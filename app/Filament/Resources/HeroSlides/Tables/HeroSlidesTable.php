<?php

namespace App\Filament\Resources\HeroSlides\Tables;

use App\Models\HeroSlide;
use App\Support\HeroImageRenderer;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class HeroSlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('rendered_desktop_path')
                    ->label('Masaüstü bandı')
                    ->disk('public')
                    ->height(44)
                    ->width(176)
                    ->defaultImageUrl(fn (HeroSlide $record): ?string => $record->imageSet()['image']),
                TextColumn::make('sort_order')->label('Sıra'),
                IconColumn::make('is_active')->boolean()->label('Aktif'),
                TextColumn::make('render_meta.rendered_at')
                    ->label('Görseller üretildi')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('Henüz üretilmedi')
                    ->toggleable(),
            ])->recordActions([
                Action::make('renderImages')
                    ->label('Görselleri yeniden üret')
                    ->icon('heroicon-m-arrow-path')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Görseller yeniden üretilsin mi?')
                    ->modalDescription('Yüklediğiniz masaüstü, tablet ve telefon görsellerinden cihaz bandları tekrar oluşturulur.')
                    ->action(function (HeroSlide $record): void {
                        try {
                            app(HeroImageRenderer::class)->render($record, force: true);
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->title('Üretim başarısız')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Görseller yeniden üretildi')
                            ->body(implode(' ', $record->renderWarnings()) ?: 'Tüm bandlar güncellendi.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('renderImagesBulk')
                        ->label('Görselleri yeniden üret')
                        ->icon('heroicon-m-arrow-path')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $renderer = app(HeroImageRenderer::class);
                            $failed = 0;

                            foreach ($records as $record) {
                                try {
                                    $renderer->render($record, force: true);
                                } catch (Throwable) {
                                    $failed++;
                                }
                            }

                            Notification::make()
                                ->title($failed === 0 ? 'Görseller yeniden üretildi' : "{$failed} slaytta hata oluştu")
                                ->status($failed === 0 ? 'success' : 'warning')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
