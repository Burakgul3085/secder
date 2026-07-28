<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Models\Setting;
use App\Support\Mailer;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use PHPMailer\PHPMailer\Exception;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('first_name')->label('Ad')->searchable(),
                TextColumn::make('last_name')->label('Soyad')->searchable(),
                TextColumn::make('email')->label('E-posta')->searchable()->copyable(),
                TextColumn::make('message')
                    ->label('Mesaj')
                    ->limit(60)
                    ->wrap()
                    ->searchable()
                    ->action('goruntule'),
                IconColumn::make('is_replied')->label('Yanıtlandı')->boolean(),
                TextColumn::make('replied_at')->label('Yanıt Tarihi')->dateTime('d.m.Y H:i')->placeholder('-'),
            ])
            ->filters([
                TernaryFilter::make('is_replied')
                    ->label('Yanıt Durumu')
                    ->trueLabel('Yanıtlananlar')
                    ->falseLabel('Yanıtlanmayanlar')
                    ->placeholder('Tümü'),
            ])
            ->recordActions([
                Action::make('goruntule')
                    ->label('Görüntüle')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->iconButton()
                    ->tooltip('Mesajı görüntüle')
                    ->modalHeading(fn ($record) => $record->first_name . ' ' . $record->last_name . ' — Mesaj Detayı')
                    ->modalWidth('2xl')
                    ->form([
                        TextInput::make('gonderici')
                            ->label('Gönderici')
                            ->disabled(),
                        TextInput::make('email')
                            ->label('E-posta')
                            ->disabled(),
                        TextInput::make('tarih')
                            ->label('Gönderim Tarihi')
                            ->disabled(),
                        Textarea::make('message')
                            ->label('Mesaj')
                            ->rows(10)
                            ->disabled()
                            ->columnSpanFull(),
                        Textarea::make('reply_message')
                            ->label('Gönderilen Yanıt')
                            ->rows(6)
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record !== null && $record->is_replied),
                    ])
                    ->fillForm(fn ($record) => [
                        'gonderici'     => trim($record->first_name . ' ' . $record->last_name),
                        'email'         => $record->email,
                        'tarih'         => $record->created_at?->format('d.m.Y H:i'),
                        'message'       => $record->message,
                        'reply_message' => $record->reply_message,
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Kapat'),

                Action::make('yanitla')
                    ->label('Cevap Ver')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->iconButton()
                    ->tooltip('Mesaja cevap ver')
                    ->modalHeading('Mesaja Cevap Ver')
                    ->form([
                        TextInput::make('subject')
                            ->label('Konu')
                            ->required()
                            ->maxLength(180)
                            ->default('İletişim Formu Mesajınıza Yanıt'),
                        Textarea::make('body')
                            ->label('Mesaj')
                            ->rows(8)
                            ->required()
                            ->maxLength(10000),
                    ])
                    ->action(function ($record, array $data): void {
                        $settings = Setting::current();
                        $replyToEmail = $settings->mailer_notification_email ?: $settings->email;

                        try {
                            $html = view('emails.contact-reply', [
                                'contactMessage' => $record,
                                'subject' => $data['subject'],
                                'body' => $data['body'],
                                'siteTitle' => $settings->site_title ?? config('app.name'),
                            ])->render();

                            Mailer::send(
                                $record->email,
                                trim($record->first_name . ' ' . $record->last_name),
                                $data['subject'],
                                $html,
                                $replyToEmail,
                            );

                            $record->update([
                                'is_replied' => true,
                                'reply_subject' => $data['subject'],
                                'reply_message' => $data['body'],
                                'replied_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Yanıt e-postası gönderildi')
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('E-posta gönderilemedi')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                DeleteAction::make()
                    ->label('Sil')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->modalHeading('Mesajı sil')
                    ->modalDescription('Bu iletişim mesajı kalıcı olarak silinecek. Devam etmek istiyor musunuz?')
                    ->modalSubmitActionLabel('Evet, sil'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Seçilenleri sil')
                        ->modalHeading('Seçili mesajları sil'),
                ]),
            ]);
    }
}

