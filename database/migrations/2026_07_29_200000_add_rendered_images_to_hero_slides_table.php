<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            // Yüklenen görselden otomatik üretilen cihaz varyantları.
            $table->string('rendered_desktop_path')->nullable()->after('image_path_mobile');
            $table->string('rendered_desktop_2x_path')->nullable()->after('rendered_desktop_path');
            $table->string('rendered_desktop_fallback_path')->nullable()->after('rendered_desktop_2x_path');
            $table->string('rendered_tablet_path')->nullable()->after('rendered_desktop_fallback_path');
            $table->string('rendered_mobile_path')->nullable()->after('rendered_tablet_path');

            // Kaynak dosya + ayar imzası; değişmediyse yeniden üretim yapılmaz.
            $table->string('render_signature', 64)->nullable()->after('rendered_mobile_path');
            $table->json('render_meta')->nullable()->after('render_signature');

            // auto | blur | gradient | mirror
            $table->string('fill_mode', 16)->default('auto')->after('render_meta');
        });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropColumn([
                'rendered_desktop_path',
                'rendered_desktop_2x_path',
                'rendered_desktop_fallback_path',
                'rendered_tablet_path',
                'rendered_mobile_path',
                'render_signature',
                'render_meta',
                'fill_mode',
            ]);
        });
    }
};
