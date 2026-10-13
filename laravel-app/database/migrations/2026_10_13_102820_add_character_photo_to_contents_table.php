<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            if (!Schema::hasColumn('contents', 'character_photo_id')) {
                $table->string('character_photo_id')->nullable()->after('character_id');
            }
            if (!Schema::hasColumn('contents', 'character_photo_url')) {
                $table->text('character_photo_url')->nullable()->after('character_photo_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            if (Schema::hasColumn('contents', 'character_photo_url')) {
                $table->dropColumn('character_photo_url');
            }
            if (Schema::hasColumn('contents', 'character_photo_id')) {
                $table->dropColumn('character_photo_id');
            }
        });
    }
};
