<?php

namespace App\Filament\Crm\Resources\Donations\RelationManagers;

use App\Models\Donation;
use App\Models\PosterTemplate;
use App\Support\Crm\CrmRecordDeleteActions;
use App\Support\Crm\DonationDocumentGenerator;
use App\Support\Crm\PosterDataResolver;
use App\Support\Crm\PosterWhatsAppLinkBuilder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Attributes\On;

class PostersRelationManager extends RelationManager
{
    protected static string $relationship = 'posters';

    protected static ?string $title = 'Afişler';

    private function canManage(): bool
    {
        return auth('crm')->user()?->canWriteDonations() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('generated_at', 'desc')
            ->emptyStateHeading('Henüz afiş yok')
            ->emptyStateDescription('Yukarıdaki butonla teşekkür afişi oluşturabilirsiniz. Önce "Afiş Şablonları" bölümünden teşekkür afişi şablonunu tasarlamış olmalısınız.')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Önizleme')
                    ->disk('public')
                    ->imageHeight(80),
                TextColumn::make('type')
                    ->label('Tür')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PosterTemplate::TYPES[$state] ?? $state),
                TextColumn::make('generated_at')->label('Oluşturulma')->dateTime('d.m.Y H:i'),
            ])
            ->headerActions([
                Action::make('generateThanksPoster')
                    ->label('Teşekkür Afişi Oluştur')
                    ->icon(Heroicon::OutlinedHeart)
                    ->color('warning')
                    ->visible(fn (): bool => $this->canManage())
                    ->action(fn () => $this->dispatchPosters([PosterTemplate::TYPE_THANKS])),
                Action::make('generateAll')
                    ->label('Hepsini Oluştur')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->color('success')
                    ->visible(fn (): bool => $this->canManage())
                    ->action(fn () => $this->generateAll()),
            ])
            ->recordActions([
                Action::make('sendWhatsApp')
                    ->label('WhatsApp ile Gönder')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->color('success')
                    ->visible(fn ($record): bool => app(PosterWhatsAppLinkBuilder::class)->donorHasPhone($record))
                    ->url(fn ($record): string => app(PosterWhatsAppLinkBuilder::class)->build($record) ?? '#')
                    ->openUrlInNewTab(),
                Action::make('preview')
                    ->label('Görüntüle')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn ($record): ?string => $record->image_url)
                    ->openUrlInNewTab(),
                Action::make('editStudio')
                    ->label('Düzenle')
                    ->icon(Heroicon::OutlinedPaintBrush)
                    ->color('primary')
                    ->visible(fn (): bool => $this->canManage())
                    ->url(fn ($record): string => route('crm.posters.edit', $record))
                    ->openUrlInNewTab(),
                Action::make('downloadPng')
                    ->label('PNG İndir')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->url(fn ($record): string => route('crm.posters.download.png', $record))
                    ->openUrlInNewTab(),
                Action::make('downloadPdf')
                    ->label('PDF İndir')
                    ->icon(Heroicon::OutlinedDocument)
                    ->url(fn ($record): string => route('crm.posters.download.pdf', $record))
                    ->openUrlInNewTab(),
                CrmRecordDeleteActions::make(
                    authorize: fn (): bool => $this->canManage(),
                    heading: 'Afişi sil',
                    description: 'Bu afiş ve ilişkili PNG/PDF dosyaları kalıcı olarak silinecek.',
                    successTitle: 'Afiş silindi',
                ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    CrmRecordDeleteActions::makeBulk(
                        authorize: fn (): bool => $this->canManage(),
                        heading: 'Seçilen afişleri sil',
                        description: 'Seçili afişler ve dosyaları kalıcı olarak silinecek.',
                        successTitle: 'Afişler silindi',
                    ),
                ]),
            ]);
    }

    private function generateAll(): void
    {
        try {
            app(DonationDocumentGenerator::class)->generate($this->getOwnerRecord(), regenerate: true);
            Notification::make()->title('Makbuz oluşturuldu')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Makbuz oluşturulamadı')->body($e->getMessage())->danger()->send();
        }

        $this->dispatchPosters([PosterTemplate::TYPE_THANKS]);
    }

    /**
     * @param  array<int, string>  $types
     */
    private function dispatchPosters(array $types): void
    {
        if (! $this->canManage()) {
            return;
        }

        $jobs = [];
        $missing = [];

        foreach ($types as $type) {
            $job = $this->buildJob($type);
            if ($job) {
                $jobs[] = $job;
            } else {
                $missing[] = PosterTemplate::TYPES[$type] ?? $type;
            }
        }

        if ($missing) {
            Notification::make()
                ->title('Şablon hazır değil')
                ->body('Şu afiş türleri için önce "Afiş Şablonları" bölümünden arka plan ve yazı katmanlarını tasarlayın: ' . implode(', ', $missing) . '.')
                ->warning()
                ->send();
        }

        if ($jobs) {
            $this->dispatch('bkd-generate-poster', jobs: $jobs);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildJob(string $type): ?array
    {
        /** @var Donation $donation */
        $donation = $this->getOwnerRecord();

        $template = PosterTemplate::resolveActive($type);

        if (! $template || ! $template->background_path || empty($template->layout)) {
            return null;
        }

        $data = app(PosterDataResolver::class)->resolve($donation, $template);

        return [
            'donationId' => $donation->id,
            'type' => $type,
            'templateId' => $template->id,
            'backgroundUrl' => $template->background_url,
            'canvasWidth' => $template->canvas_width ?: null,
            'canvasHeight' => $template->canvas_height ?: null,
            'layout' => $template->layout ?? [],
            'data' => $data,
            'uploadUrl' => route('crm.posters.store'),
            'csrf' => csrf_token(),
        ];
    }

    #[On('bkd-poster-saved')]
    public function onPosterSaved(int $ok = 0): void
    {
        if ($ok > 0) {
            Notification::make()
                ->title($ok . ' afiş oluşturuldu')
                ->success()
                ->send();
        }
    }
}
