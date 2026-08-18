<?php

namespace App\Filament\Crm\Resources\Donations\RelationManagers;

use App\Support\Crm\CrmRecordDeleteActions;
use App\Support\Crm\DonationDocumentGenerator;
use App\Support\Crm\ReceiptWhatsAppLinkBuilder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Makbuzlar';

    private function canManageDocuments(): bool
    {
        return auth('crm')->user()?->canWriteDonations() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('generated_at', 'desc')
            ->emptyStateHeading('Henüz makbuz yok')
            ->emptyStateDescription('Makbuz oluştur butonu ile PDF makbuz üretebilirsiniz.')
            ->columns([
                TextColumn::make('verification_code')->label('Doğrulama Kodu')->copyable(),
                TextColumn::make('generated_at')->label('Oluşturulma')->dateTime('d.m.Y H:i'),
            ])
            ->headerActions([
                Action::make('generateReceipt')
                    ->label('Makbuz oluştur')
                    ->icon('heroicon-o-document-text')
                    ->visible(fn (): bool => $this->canManageDocuments())
                    ->action(fn () => $this->generateReceipt()),
            ])
            ->recordActions([
                Action::make('sendWhatsApp')
                    ->label('WhatsApp ile Gönder')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->visible(fn ($record): bool => app(ReceiptWhatsAppLinkBuilder::class)->donorHasPhone($record))
                    ->url(fn ($record): string => app(ReceiptWhatsAppLinkBuilder::class)->build($record) ?? '#')
                    ->openUrlInNewTab(),
                Action::make('downloadPdf')
                    ->label('PDF İndir')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        if (! Storage::disk('public')->exists($record->pdf_path)) {
                            Notification::make()->title('PDF bulunamadı')->danger()->send();

                            return;
                        }

                        return response()->download(
                            Storage::disk('public')->path($record->pdf_path),
                            'makbuz-' . $record->verification_code . '.pdf',
                        );
                    }),
                Action::make('verify')
                    ->label('QR Doğrulama')
                    ->icon('heroicon-o-qr-code')
                    ->url(fn ($record): string => $record->verification_url)
                    ->openUrlInNewTab(),
                CrmRecordDeleteActions::make(
                    authorize: fn (): bool => $this->canManageDocuments(),
                    heading: 'Makbuzu sil',
                    description: 'Bu makbuz ve ilişkili PDF dosyası kalıcı olarak silinecek.',
                    successTitle: 'Makbuz silindi',
                ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    CrmRecordDeleteActions::makeBulk(
                        authorize: fn (): bool => $this->canManageDocuments(),
                        label: 'Seçilen makbuzları sil',
                        heading: 'Seçilen makbuzları sil',
                        description: 'Seçili makbuzlar ve PDF dosyaları kalıcı olarak silinecek.',
                        successTitle: 'Makbuzlar silindi',
                    ),
                ]),
            ]);
    }

    private function generateReceipt(): void
    {
        try {
            app(DonationDocumentGenerator::class)->generate(
                $this->getOwnerRecord(),
                regenerate: true,
            );

            Notification::make()
                ->title('Makbuz oluşturuldu')
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Makbuz oluşturulamadı')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }
}
