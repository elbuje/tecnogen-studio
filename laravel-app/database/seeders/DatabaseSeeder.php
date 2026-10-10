<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        $adminId = 'aeb74ebf-7d36-4728-bad6-2f836865ea14';
        $adminUser = DB::table('users')->where('email', 'mfmujic@gmail.com')->first();
        if (!$adminUser) {
            DB::table('users')->insert([
                'id' => $adminId,
                'email' => 'mfmujic@gmail.com',
                'password' => Hash::make('AdminTecnoGen2026!'),
                'full_name' => 'Marcelo Mujica (Admin)',
                'role' => 'superadmin',
                'plan_tier' => 'agency',
                'commercial_status' => 'active',
                'plan_name' => 'Plan Agency Elite',
                'plan_price_monthly' => 250,
                'monthly_video_limit' => 999,
                'videos_generated_this_month' => 0,
                'avatar_minutes_quota' => 999,
                'avatar_minutes_used' => 0,
                'auto_mode_enabled' => 1,
                'credits_balance' => 9999,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('email', 'mfmujic@gmail.com')->update([
                'password' => Hash::make('AdminTecnoGen2026!'),
                'role' => 'superadmin',
                'credits_balance' => 9999,
            ]);
            $adminId = $adminUser->id;
        }

        // 2. Client User (JM Odontología)
        $clientId = 'f0b61134-7eb6-447c-bd06-7a9e2302e21a';
        $clientUser = DB::table('users')->where('email', 'mmujica@tecnobrain.com.ar')->first();
        if (!$clientUser) {
            DB::table('users')->insert([
                'id' => $clientId,
                'email' => 'mmujica@tecnobrain.com.ar',
                'password' => Hash::make('JMOdonto2026!'),
                'full_name' => 'JM Odontologia Integral',
                'role' => 'client',
                'plan_tier' => 'agency',
                'commercial_status' => 'active',
                'plan_name' => 'Plan Growth Pro',
                'plan_price_monthly' => 150,
                'monthly_video_limit' => 50,
                'videos_generated_this_month' => 0,
                'avatar_minutes_quota' => 120,
                'avatar_minutes_used' => 0,
                'auto_mode_enabled' => 1,
                'credits_balance' => 1000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('email', 'mmujica@tecnobrain.com.ar')->update([
                'password' => Hash::make('JMOdonto2026!'),
                'role' => 'client',
                'credits_balance' => 1000,
            ]);
            $clientId = $clientUser->id;
        }

        // 3. Marca JM Odontología Integral
        $jmBrand = DB::table('brands')->where('user_id', $clientId)->orWhere('name', 'like', '%JM%')->first();
        $brandRules = [
            'paginator_id' => 'profundidad-3d-layered',
            'estilo_paginador' => '06. Barra de progreso (━━━━━━────────)',
            'default_paginator' => '06. Barra de progreso (━━━━━━────────)',
            'tipo_visual' => 'Problema → Solución',
            'default_visual_type' => 'Problema → Solución',
            'gdrive_logos_folder_url' => 'https://drive.google.com/drive/folders/1Nb7c7NW_vd8zXN1V1jjb1zNDO0wn0jxJ?usp=sharing',
            'gdrive_brand_manual_url' => 'https://drive.google.com/file/d/1RASbKrUu7LoKA79mXQ45KIjZvzOzSbCL/view?usp=drive_link',
        ];

        if (!$jmBrand) {
            DB::table('brands')->insert([
                'id' => 'ef9ec23e-822f-4308-a027-7436371bc87c',
                'user_id' => $clientId,
                'name' => 'JM Odontologia Integral',
                'primary_color' => '#16345F',
                'accent_color' => '#7DD3FC',
                'bg_color' => '#1D1D1B',
                'font_style_title' => 'serif-editorial',
                'font_style_body' => 'sans-modern',
                'layout_preset' => 'editorial-top',
                'logo_position' => 'top-left',
                'logo_width_px' => 180,
                'gdrive_logos_folder_id' => '1Nb7c7NW_vd8zXN1V1jjb1zNDO0wn0jxJ',
                'gdrive_brand_manual_folder_id' => '1RASbKrUu7LoKA79mXQ45KIjZvzOzSbCL',
                'brand_rules' => json_encode($brandRules),
                'created_at' => now(),
            ]);
        } else {
            DB::table('brands')->where('id', $jmBrand->id)->update([
                'primary_color' => '#16345F',
                'accent_color' => '#7DD3FC',
                'bg_color' => '#1D1D1B',
                'font_style_title' => 'serif-editorial',
                'font_style_body' => 'sans-modern',
                'logo_position' => 'top-left',
                'logo_width_px' => 180,
                'gdrive_logos_folder_id' => '1Nb7c7NW_vd8zXN1V1jjb1zNDO0wn0jxJ',
                'gdrive_brand_manual_folder_id' => '1RASbKrUu7LoKA79mXQ45KIjZvzOzSbCL',
                'brand_rules' => json_encode($brandRules),
            ]);
        }

        // 4. Marca Admin
        $adminBrand = DB::table('brands')->where('user_id', $adminId)->first();
        if (!$adminBrand) {
            DB::table('brands')->insert([
                'id' => '3f265e72-8680-42dc-9727-a2510cf78867',
                'user_id' => $adminId,
                'name' => 'TecnoGen Admin Brand',
                'primary_color' => '#7C3AED',
                'accent_color' => '#A78BFA',
                'bg_color' => '#030712',
                'font_style_title' => 'sans-modern',
                'font_style_body' => 'sans-modern',
                'layout_preset' => 'editorial-top',
                'logo_position' => 'top-left',
                'logo_width_px' => 180,
                'gdrive_logos_folder_id' => '1Nb7c7NW_vd8zXN1V1jjb1zNDO0wn0jxJ',
                'gdrive_brand_manual_folder_id' => '1RASbKrUu7LoKA79mXQ45KIjZvzOzSbCL',
                'brand_rules' => json_encode($brandRules),
                'created_at' => now(),
            ]);
        }

        // 5. Personajes de la Marca
        $charactersData = [
            [
                'id' => '132f9d70-7add-4da7-b2a8-c901f2b724a8',
                'user_id' => $clientId,
                'brand_id' => 'ef9ec23e-822f-4308-a027-7436371bc87c',
                'name' => 'Dra. Luciana Galbiati',
                'role' => 'Odontología General',
                'gdrive_folder_url' => 'https://drive.google.com/drive/folders/1Qc8t4ZAQEao4spaQjdL07WjppBu1ThZo?usp=sharing',
                'gdrive_folder_id' => '1Qc8t4ZAQEao4spaQjdL07WjppBu1ThZo',
                'folder_path' => 'https://drive.google.com/drive/folders/1Qc8t4ZAQEao4spaQjdL07WjppBu1ThZo?usp=sharing',
                'cover_photo' => '/app/gdrive-proxy/1jiP4uMjQYthZ1LcvDLye0cyv2O_O2RCD?sz=w1200',
                'notes' => 'Especialista en odontología integral y estética dental.',
            ],
            [
                'id' => '14c9d30a-4172-4226-9ca8-b9334b197ce5',
                'user_id' => $clientId,
                'brand_id' => 'ef9ec23e-822f-4308-a027-7436371bc87c',
                'name' => 'Dra. Karina',
                'role' => 'Odontóloga Especialista',
                'gdrive_folder_url' => 'https://drive.google.com/drive/folders/1Nb7c7NW_vd8zXN1V1jjb1zNDO0wn0jxJ',
                'gdrive_folder_id' => '1Nb7c7NW_vd8zXN1V1jjb1zNDO0wn0jxJ',
                'folder_path' => 'https://drive.google.com/drive/folders/1Nb7c7NW_vd8zXN1V1jjb1zNDO0wn0jxJ',
                'cover_photo' => null,
                'notes' => 'Profesional principal para carruseles y posts clínicos.',
            ],
            [
                'id' => 'b2862fc2-9447-4cb5-a371-9e0ca2eec202',
                'user_id' => $clientId,
                'brand_id' => 'ef9ec23e-822f-4308-a027-7436371bc87c',
                'name' => 'Dra. Jessica',
                'role' => 'Odontología General',
                'gdrive_folder_url' => 'https://drive.google.com/drive/folders/1PNC5gKeTv6OgHyXlgbZmHuAeNw8IH4ps',
                'gdrive_folder_id' => '1PNC5gKeTv6OgHyXlgbZmHuAeNw8IH4ps',
                'folder_path' => 'https://drive.google.com/drive/folders/1PNC5gKeTv6OgHyXlgbZmHuAeNw8IH4ps',
                'cover_photo' => '/app/gdrive-proxy/10bbN0T8WpUdXeOhoKFz1lUw9fNGcThai?sz=w1200',
                'notes' => 'Fotos oficiales de sesión clínica con ambo azul marino institucional JM.',
            ],
        ];

        foreach ($charactersData as $c) {
            $exists = DB::table('characters')->where('id', $c['id'])->orWhere('name', $c['name'])->first();
            if (!$exists) {
                $c['created_at'] = now();
                $c['updated_at'] = now();
                DB::table('characters')->insert($c);
            } else {
                DB::table('characters')->where('id', $exists->id)->update([
                    'gdrive_folder_url' => $c['gdrive_folder_url'],
                    'gdrive_folder_id' => $c['gdrive_folder_id'],
                    'folder_path' => $c['folder_path'],
                    'role' => $c['role'],
                    'notes' => $c['notes'],
                    'cover_photo' => $c['cover_photo'] ?? $exists->cover_photo,
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
