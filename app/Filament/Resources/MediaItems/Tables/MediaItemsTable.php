<?php

namespace App\Filament\Resources\MediaItems\Tables;

use App\Models\MediaItem;
use App\Support\MediaGallerySync;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class MediaItemsTable
{
    public static function configure(Table $table): Table
    {
        $sync = app(MediaGallerySync::class);

        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('preview')
                    ->label('Önizleme')
                    ->html()
                    ->state(fn (MediaItem $record): HtmlString => self::previewHtml($record))
                    ->width('120px')
                    ->extraCellAttributes(['class' => 'fi-ta-preview-cell']),
                TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === MediaItem::TYPE_VIDEO ? 'Video' : 'Fotoğraf')
                    ->color(fn (string $state): string => $state === MediaItem::TYPE_VIDEO ? 'warning' : 'info'),
                TextColumn::make('owner')
                    ->label('Başlık')
                    ->state(fn (MediaItem $record): string => $record->ownerLabel())
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($q) use ($search): void {
                            $q->whereHas('project', fn ($p) => $p->where('title', 'like', "%{$search}%"))
                                ->orWhereHas('album', fn ($a) => $a->where('title', 'like', "%{$search}%"));
                        });
                    })
                    ->wrap(),
                TextColumn::make('path')
                    ->label('Dosya')
                    ->limit(36)
                    ->tooltip(fn (MediaItem $record): string => (string) $record->path)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Güncelleme')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tip')
                    ->options([
                        MediaItem::TYPE_IMAGE => 'Fotoğraf',
                        MediaItem::TYPE_VIDEO => 'Video',
                    ]),
                SelectFilter::make('project_id')
                    ->label('Faaliyet')
                    ->relationship('project', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('media_album_id')
                    ->label('Albüm')
                    ->relationship('album', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('move')
                    ->label('Taşı')
                    ->icon('heroicon-o-arrows-right-left')
                    ->modalHeading(fn (MediaItem $record): string => 'Medya taşı: ' . $record->ownerLabel())
                    ->modalDescription(fn (MediaItem $record): HtmlString => self::previewHtml($record, large: true))
                    ->form([
                        Select::make('destination')
                            ->label('Hedef başlık')
                            ->options(fn (): array => $sync->destinationOptions())
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (MediaItem $record, array $data) use ($sync): void {
                        if (($data['destination'] ?? '') === $record->destinationKey()) {
                            Notification::make()->title('Öğe zaten bu başlıkta.')->warning()->send();

                            return;
                        }

                        $sync->moveToDestination($record, (string) $data['destination']);
                        Notification::make()->title('Medya taşındı.')->success()->send();
                    }),
                DeleteAction::make()
                    ->before(function (MediaItem $record): void {
                        $record->deleteFileIfOrphan();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('moveSelected')
                        ->label('Seçilenleri taşı')
                        ->icon('heroicon-o-arrows-right-left')
                        ->form([
                            Select::make('destination')
                                ->label('Hedef başlık')
                                ->options(fn (): array => $sync->destinationOptions())
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (Collection $records, array $data) use ($sync): void {
                            foreach ($records as $record) {
                                if (! $record instanceof MediaItem) {
                                    continue;
                                }
                                if (($data['destination'] ?? '') === $record->destinationKey()) {
                                    continue;
                                }
                                $sync->moveToDestination($record, (string) $data['destination']);
                            }

                            Notification::make()->title('Seçilen medya öğeleri taşındı.')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function previewHtml(MediaItem $record, bool $large = false): HtmlString
    {
        $url = e($record->url());
        $w = $large ? '220px' : '88px';
        $h = $large ? '140px' : '64px';
        $open = '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" '
            . 'title="' . ($record->isVideo() ? 'Videoyu aç' : 'Fotoğrafı aç') . '" '
            . 'onclick="event.stopPropagation();" '
            . 'style="display:inline-block;cursor:pointer;line-height:0;text-decoration:none;">';
        $close = '</a>';

        if ($record->isImage()) {
            return new HtmlString(
                $open
                . '<img src="' . $url . '" alt="Önizleme" loading="lazy" decoding="async" '
                . 'style="width:' . $w . ';height:' . $h . ';object-fit:cover;border-radius:10px;'
                . 'display:block;background:#e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,.08);" />'
                . $close
            );
        }

        return new HtmlString(
            $open
            . '<div style="position:relative;width:' . $w . ';height:' . $h . ';border-radius:10px;overflow:hidden;'
            . 'background:#0f172a;box-shadow:0 1px 4px rgba(0,0,0,.12);">'
            . '<video src="' . $url . '#t=0.8" muted preload="metadata" playsinline '
            . 'style="width:100%;height:100%;object-fit:cover;display:block;pointer-events:none;"></video>'
            . '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;'
            . 'background:rgba(0,0,0,.28);pointer-events:none;">'
            . '<span style="width:28px;height:28px;border-radius:999px;background:rgba(255,255,255,.92);'
            . 'display:inline-flex;align-items:center;justify-content:center;color:#334155;font-size:12px;'
            . 'font-weight:700;padding-left:2px;">▶</span>'
            . '</div></div>'
            . $close
        );
    }
}
