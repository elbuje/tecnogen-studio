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
            if (!Schema::hasColumn('contents', 'character_id')) {
                $table->string('character_id')->nullable()->after('brand_id');
            }
            if (!Schema::hasColumn('contents', 'logo_id')) {
                $table->string('logo_id')->nullable()->after('character_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            if (Schema::hasColumn('contents', 'character_id')) {
                $table->dropColumn('character_id');
            }
            if (Schema::hasColumn('contents', 'logo_id')) {
                $table->dropColumn('logo_id');
            }
        });
    }

};
