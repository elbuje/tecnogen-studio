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
        Schema::table('characters', function (Blueprint $table) {
            if (!Schema::hasColumn('characters', 'gdrive_folder_url')) {
                $table->text('gdrive_folder_url')->nullable()->after('role');
            }
            if (!Schema::hasColumn('characters', 'gdrive_folder_id')) {
                $table->string('gdrive_folder_id', 100)->nullable()->after('gdrive_folder_url');
            }
            if (!Schema::hasColumn('characters', 'folder_path')) {
                $table->text('folder_path')->nullable()->after('gdrive_folder_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            if (Schema::hasColumn('characters', 'gdrive_folder_url')) {
                $table->dropColumn('gdrive_folder_url');
            }
            if (Schema::hasColumn('characters', 'gdrive_folder_id')) {
                $table->dropColumn('gdrive_folder_id');
            }
        });
    }
};
