<?php

namespace App\Filament\Resources\MailerSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MailerSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Mailer Ayarları')
                ->description('Admin panelden e-posta gönderimini yönetmek için SMTP ve gönderici bilgileri.')
                ->schema([
                    TextInput::make('mailer_notification_email')
                        ->label('Bildirim Alıcı E-posta')
                        ->email()
                        ->helperText('Ziyaretçi iletişim/gönüllü başvuruları bu adrese bildirilir.')
                        ->required(),
                    TextInput::make('mailer_from_name')
                        ->label('Gönderici Adı')
                        ->helperText('E-postalarda görünen gönderen isim.')
                        ->required(),
                    TextInput::make('mailer_from_address')
                        ->label('Gönderici E-posta')
                        ->email()
                        ->helperText('E-postalar bu adresten gönderilir.')
                        ->required(),
                    TextInput::make('mailer_host')
                        ->label('SMTP Host (Sabit)')
                        ->default('smtp.gmail.com')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Bu alan Gmail için sabittir ve değiştirilemez.'),
                    TextInput::make('mailer_port')
                        ->label('SMTP Port (Sabit)')
                        ->default('587')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Bu alan Gmail için sabittir ve değiştirilemez.'),
                    TextInput::make('mailer_encryption')
                        ->label('Şifreleme (Sabit)')
                        ->default('tls')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Bu alan Gmail için sabittir ve değiştirilemez.'),
                    TextInput::make('mailer_username')
                        ->label('SMTP Kullanıcı (E-posta)')
                        ->helperText('Genellikle gönderen e-posta ile aynıdır.')
                        ->required()
                        ->columnSpanFull(),
                    TextInput::make('mailer_password')
                        ->label('SMTP Şifre / Uygulama Şifresi')
                        ->password()
                        ->revealable()
                        ->minLength(16)
                        ->helperText('Gmail için 16 haneli uygulama şifresi girin.')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }
}
