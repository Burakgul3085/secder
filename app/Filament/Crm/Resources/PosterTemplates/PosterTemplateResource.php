<?php

namespace App\Filament\Crm\Resources\PosterTemplates;

use App\Filament\Crm\Resources\PosterTemplates\Pages\CreatePosterTemplate;
use App\Filament\Crm\Resources\PosterTemplates\Pages\DesignPosterTemplate;
use App\Filament\Crm\Resources\PosterTemplates\Pages\EditPosterTemplate;
use App\Filament\Crm\Resources\PosterTemplates\Pages\ListPosterTemplates;
use App\Models\CrmUser;
use App\Models\PosterTemplate;
use App\Support\Crm\PosterDataResolver;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PosterTemplateResource extends Resource
{
    protected static ?string $model = PosterTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Afiş Şablonları';

    protected static string|\UnitEnum|null $navigationGroup = 'Afiş Yönetimi';

    protected static ?string $modelLabel = 'Afiş Şablonu';

    protected static ?string $pluralModelLabel = 'Afiş Şablonları';

    protected static ?int $navigationSort = 1;

    protected static function crmUser(): ?CrmUser
    {
        return auth('crm')->user();
    }

    public static function canViewAny(): bool
    {
        return self::crmUser()?->canWriteDonations() ?? false;
    }

    public static function canCreate(): bool
    {
        return self::crmUser()?->canWriteDonations() ?? false;
    }

    public static function canEdit($record): bool
    {
        return self::crmUser()?->canWriteDonations() ?? false;
    }

    public static function canDelete($record): bool
    {
        return self::crmUser()?->canDeleteRecords() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Şablon adı')
                    ->required()
                    ->maxLength(150),
                Select::make('type')
                    ->label('Afiş türü')
                    ->options(PosterTemplate::CREATABLE_TYPES)
                    ->default(PosterTemplate::TYPE_THANKS)
                    ->required()
                    ->native(false)
                    ->live(),
                FileUpload::make('background_path')
                    ->label('Arka plan görseli (boş afiş)')
                    ->image()
                    ->disk('public')
                    ->directory('crm/poster-templates')
                    ->imageEditor()
                    ->helperText('Afişin boş halini (logolu, çerçeveli arka plan) yükleyin. Tasarım ekranında üzerine yazı katmanları ekleyeceksiniz.')
                    ->columnSpanFull(),
                Textarea::make('thanks_text_template')
                    ->label('Teşekkür metni kalıbı')
                    ->rows(8)
                    ->helperText(new \Illuminate\Support\HtmlString(PosterDataResolver::templateHelperHtml()))
                    ->visible(fn (callable $get): bool => $get('type') === PosterTemplate::TYPE_THANKS)
                    ->placeholder((new PosterDataResolver())->defaultThanksTemplate())
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Toggle::make('is_default')
                    ->label('Varsayılan (bu türde öncelikli kullanılsın)')
                    ->default(false),
                TextInput::make('sort_order')
                    ->label('Sıralama')
                    ->numeric()
                    ->default(0),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('background_path')
                    ->label('Önizleme')
                    ->disk('public')
                    ->height(64),
                TextColumn::make('name')->label('Şablon adı')->searchable()->sortable(),
                TextColumn::make('type')
                    ->label('Tür')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PosterTemplate::TYPES[$state] ?? $state),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                IconColumn::make('is_default')->label('Varsayılan')->boolean(),
                TextColumn::make('updated_at')->label('Güncelleme')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('design')
                    ->label('Tasarla')
                    ->icon(Heroicon::OutlinedPaintBrush)
                    ->color('primary')
                    ->url(fn (PosterTemplate $record): string => DesignPosterTemplate::getUrl(['record' => $record])),
                \Filament\Actions\EditAction::make()->label('Düzenle'),
                \Filament\Actions\DeleteAction::make()->label('Sil')->requiresConfirmation(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()->label('Seçilenleri sil')->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosterTemplates::route('/'),
            'create' => CreatePosterTemplate::route('/create'),
            'edit' => EditPosterTemplate::route('/{record}/edit'),
            'design' => DesignPosterTemplate::route('/{record}/design'),
        ];
    }
}
