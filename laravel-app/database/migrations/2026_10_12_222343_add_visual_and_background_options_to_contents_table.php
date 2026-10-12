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
            $table->string('visual_type')->nullable()->after('type');
            $table->string('paginator_style')->nullable()->after('visual_type');
            $table->string('background_style')->nullable()->after('paginator_style');
            $table->string('background_hex')->nullable()->after('background_style');
            $table->longText('prompt_compiled')->nullable()->after('hashtags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['visual_type', 'paginator_style', 'background_style', 'background_hex', 'prompt_compiled']);
        });
    }
};
