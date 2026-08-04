<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivitySectionSetting extends Model
{
    protected $fillable = [
        'badge_text',
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->where('is_active', true)->latest('id')->first() ?? new self([
            'badge_text' => 'SECDER',
            'title' => 'Faaliyetlerimiz',
            'description' => 'Gaziantep\'te cami merkezli ilmi eğitim, sosyal projeler ve dayanışma çalışmalarımızla nesillerin yanında yer alıyoruz.',
            'is_active' => true,
        ]);
    }
}
