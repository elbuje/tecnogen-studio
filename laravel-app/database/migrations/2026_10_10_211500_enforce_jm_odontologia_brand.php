<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $brandRules = [
            'paginator_id' => 'profundidad-3d-layered',
            'estilo_paginador' => '06. Barra de progreso (━━━━━━────────)',
            'default_paginator' => '06. Barra de progreso (━━━━━━────────)',
            'tipo_visual' => 'Problema → Solución',
            'default_visual_type' => 'Problema → Solución',
            'gdrive_logos_folder_url' => 'https://drive.google.com/drive/folders/1Nb7c7NW_vd8zXN1V1jjb1zNDO0wn0jxJ?usp=sharing',
            'gdrive_brand_manual_url' => 'https://drive.google.com/file/d/1RASbKrUu7LoKA79mXQ45KIjZvzOzSbCL/view?usp=drive_link',
            'gdrive_output_folder_url' => 'https://drive.google.com/drive/folders/1c-3tgZcJsfgaDmUDEdvh8HFSGb9KgI_K?usp=sharing',
            'gdrive_creations_folder_url' => 'https://drive.google.com/drive/folders/1c-3tgZcJsfgaDmUDEdvh8HFSGb9KgI_K?usp=sharing',
        ];

        // 1. Eliminar cualquier marca residual TecnoGen o genérica
        DB::table('brands')->where('name', 'like', '%TecnoGen%')->delete();

        // 2. Actualizar cualquier marca que contenga 'Marcelo' o 'Marca' a 'JM Odontologia Integral'
        DB::table('brands')->where('name', 'like', '%Marcelo%')
            ->orWhere('name', 'like', '%Mi Marca%')
            ->update([
                'name' => 'JM Odontologia Integral',
                'primary_color' => '#16345F',
                'accent_color' => '#7DD3FC',
                'bg_color' => '#1D1D1B',
                'font_style_title' => 'serif-editorial',
                'font_style_body' => 'sans-modern',
                'gdrive_logos_folder_id' => '1Nb7c7NW_vd8zXN1V1jjb1zNDO0wn0jxJ',
                'gdrive_brand_manual_folder_id' => '1RASbKrUu7LoKA79mXQ45KIjZvzOzSbCL',
                'gdrive_output_folder_id' => '1c-3tgZcJsfgaDmUDEdvh8HFSGb9KgI_K',
                'brand_rules' => json_encode($brandRules),
            ]);

        // 3. Asegurar que la marca JM Odontologia Integral tenga todas las carpetas y colores oficiales
        DB::table('brands')->where('name', 'like', '%JM%')->update([
            'name' => 'JM Odontologia Integral',
            'primary_color' => '#16345F',
            'accent_color' => '#7DD3FC',
            'bg_color' => '#1D1D1B',
            'font_style_title' => 'serif-editorial',
            'font_style_body' => 'sans-modern',
            'gdrive_logos_folder_id' => '1Nb7c7NW_vd8zXN1V1jjb1zNDO0wn0jxJ',
            'gdrive_brand_manual_folder_id' => '1RASbKrUu7LoKA79mXQ45KIjZvzOzSbCL',
            'gdrive_output_folder_id' => '1c-3tgZcJsfgaDmUDEdvh8HFSGb9KgI_K',
            'brand_rules' => json_encode($brandRules),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
