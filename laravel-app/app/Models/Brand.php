<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasUuids;

    protected $table = 'brands';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; // SQLAlchemy created_at only or custom timestamps

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'primary_color',
        'accent_color',
        'bg_color',
        'font_style_title',
        'font_style_body',
        'layout_preset',
        'logo_position',
        'logo_width_px',
        'gdrive_logos_folder_id',
        'gdrive_subjects_folder_id',
        'gdrive_brand_manual_folder_id',
        'gdrive_templates_folder_id',
        'gdrive_products_folder_id',
        'gdrive_input_folder_id',
        'gdrive_output_folder_id',
        'sheets_url',
        'metricool_user_token',
        'metricool_blog_id',
        'brand_rules',
        'created_at',
    ];

    protected $casts = [
        'brand_rules' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assets()
    {
        return $this->hasMany(BrandAsset::class, 'brand_id');
    }

    public function contents()
    {
        return $this->hasMany(Content::class, 'brand_id');
    }

    /**
     * Extrae el ID de una carpeta o archivo de Google Drive
     */
    public static function extractDriveFolderId(?string $url): ?string
    {
        if (empty($url)) return null;
        $url = trim($url);

        // Si ya es un ID directo alfanumérico largo
        if (preg_match('/^[a-zA-Z0-9_-]{20,}$/', $url)) {
            return $url;
        }

        // Formato /folders/{ID}
        if (preg_match('/\/folders\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Formato /file/d/{ID} o /d/{ID}
        if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Formato id={ID}
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Obtiene la URL de la carpeta de logos en Google Drive
     */
    public function getLogosFolderUrlAttribute(): ?string
    {
        $rules = $this->brand_rules ?? [];
        if (!empty($rules['gdrive_logos_folder_url'])) {
            return $rules['gdrive_logos_folder_url'];
        }
        if (!empty($this->gdrive_logos_folder_id)) {
            return "https://drive.google.com/drive/folders/{$this->gdrive_logos_folder_id}";
        }
        return null;
    }

    /**
     * Obtiene la URL del Manual de Marca en Google Drive
     */
    public function getBrandManualUrlAttribute(): ?string
    {
        $rules = $this->brand_rules ?? [];
        if (!empty($rules['gdrive_brand_manual_url'])) {
            return $rules['gdrive_brand_manual_url'];
        }
        if (!empty($this->gdrive_brand_manual_folder_id)) {
            if (str_starts_with($this->gdrive_brand_manual_folder_id, 'http')) {
                return $this->gdrive_brand_manual_folder_id;
            }
            return "https://drive.google.com/drive/folders/{$this->gdrive_brand_manual_folder_id}";
        }
        return null;
    }

    /**
     * Obtiene la URL de la carpeta de Creaciones / Output en Google Drive del usuario
     */
    public function getCreationsFolderUrlAttribute(): ?string
    {
        $rules = $this->brand_rules ?? [];
        if (!empty($rules['gdrive_creations_folder_url'])) {
            return $rules['gdrive_creations_folder_url'];
        }
        if (!empty($rules['gdrive_output_folder_url'])) {
            return $rules['gdrive_output_folder_url'];
        }
        if (!empty($this->gdrive_output_folder_id)) {
            if (str_starts_with($this->gdrive_output_folder_id, 'http')) {
                return $this->gdrive_output_folder_id;
            }
            return "https://drive.google.com/drive/folders/{$this->gdrive_output_folder_id}";
        }
        return null;
    }

    /**
     * Retorna las opciones de fondos disponibles basadas dinámicamente en la identidad de la marca
     */
    public function getBackgroundOptions(): array
    {
        $primary = $this->primary_color ?: '#16345F';
        $accent = $this->accent_color ?: '#7DD3FC';
        $bg = $this->bg_color ?: '#FFFFFF';

        $options = [
            [
                'id' => 'warm_light',
                'name' => 'Cálido / Crema Editorial (Luz natural difusa)',
                'hex' => '#FDFBF7',
                'description' => 'Fondo claro/crema cálido con bokeh difuso y estética editorial luminosa. Ideal para salud, estética y odontología.',
                'style_directive' => 'Fondo claro luminoso (#FDFBF7 o crema suave) con iluminación cálida difusa de estudio clínico, luz natural suave y detalles curvos sutiles.'
            ],
            [
                'id' => 'pure_white',
                'name' => 'Blanco Puro Clínico (#FFFFFF)',
                'hex' => '#FFFFFF',
                'description' => 'Fondo blanco puro con estética médica minimalista de alto contraste y máximo aire visual.',
                'style_directive' => 'Fondo blanco clínico puro (#FFFFFF) con iluminación neutra de alta gama y espacio negativo generoso.'
            ],
            [
                'id' => 'brand_bg',
                'name' => "Fondo Oficial de Marca ({$bg})",
                'hex' => $bg,
                'description' => "Color de fondo configurado en la identidad de marca ({$bg}).",
                'style_directive' => "Fondo sólido con el color de marca oficial ({$bg}) con iluminación suave y contraste equilibrado."
            ],
            [
                'id' => 'brand_primary',
                'name' => "Fondo Primario Institucional ({$primary})",
                'hex' => $primary,
                'description' => "Tono primario de la marca ({$primary}) para composiciones profundas y elegantes.",
                'style_directive' => "Fondo profundo en color primario institucional ({$primary}) con acentos en {$accent} y tipografías claras de alto contraste."
            ],
            [
                'id' => 'brand_accent',
                'name' => "Fondo Acento Tecnológico ({$accent})",
                'hex' => $accent,
                'description' => "Tono acento luminoso ({$accent}) para piezas de alto impacto y vitalidad.",
                'style_directive' => "Fondo luminoso con el color acento ({$accent}) y tipografía en azul oscuro institucional ({$primary})."
            ],
            [
                'id' => 'brand_gradient',
                'name' => "Gradiente Dinámico de Marca ({$primary} → {$accent})",
                'hex' => $primary,
                'description' => "Transición cromática suave entre {$primary} y {$accent}.",
                'style_directive' => "Fondo con suave gradiente difuso entre el color primario ({$primary}) y el acento ({$accent}) con atmósfera cinematográfica médica."
            ],
        ];

        $rules = $this->brand_rules ?? [];
        if (!empty($rules['custom_backgrounds']) && is_array($rules['custom_backgrounds'])) {
            foreach ($rules['custom_backgrounds'] as $custom) {
                if (!empty($custom['name']) && !empty($custom['hex'])) {
                    $options[] = $custom;
                }
            }
        }

        return $options;
    }

    /**
     * Escanea y obtiene los logos desde Google Drive de forma paralela y con caché
     */
    public function getDriveLogos(): array
    {
        $folderId = $this->gdrive_logos_folder_id ?: self::extractDriveFolderId($this->logos_folder_url);
        if (!$folderId) {
            return [];
        }

        return \Illuminate\Support\Facades\Cache::remember("gdrive_brand_logos_{$folderId}", 86400, function () use ($folderId) {
            try {
                $url = "https://drive.google.com/drive/folders/{$folderId}";
                $response = \Illuminate\Support\Facades\Http::timeout(6)->get($url);
                if (!$response->successful()) {
                    return [];
                }

                $html = $response->body();
                preg_match_all('/"([a-zA-Z0-9_-]{28,35})"/', $html, $matches);
                $candidates = array_unique($matches[1] ?? []);

                if (empty($candidates)) {
                    return [];
                }

                $mh = curl_multi_init();
                $handles = [];

                foreach ($candidates as $id) {
                    if ($id === $folderId) continue;
                    if (str_starts_with($id, 'AIza') || str_starts_with($id, 'AA2Yr') || str_starts_with($id, 'ABCDEF') || str_starts_with($id, 'M120-')) {
                        continue;
                    }

                    $ch = curl_init("https://lh3.googleusercontent.com/d/{$id}=w200");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                    curl_multi_add_handle($mh, $ch);
                    $handles[$id] = $ch;
                }

                $running = null;
                do {
                    curl_multi_exec($mh, $running);
                    curl_multi_select($mh);
                } while ($running > 0);

                $logos = [];
                $idx = 1;
                foreach ($handles as $id => $ch) {
                    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                    if ($code === 200 && str_starts_with($type, 'image/')) {
                        $variant = 'Logo Variante #' . $idx;
                        if ($idx === 1) $variant = 'Logo Principal (Predeterminado)';
                        elseif ($idx === 2) $variant = 'Logo Monocromo / Blanco';
                        elseif ($idx === 3) $variant = 'Isotipo / Versión Reducida';
                        elseif ($idx === 4) $variant = 'Logo Secundario / Color';

                        $logos[] = [
                            'id' => $id,
                            'filename' => "Logo_" . sprintf('%02d', $idx) . ".png",
                            'variant_label' => $variant,
                            'relPath' => "/app/gdrive-proxy/{$id}?sz=w1200",
                            'url' => "/app/gdrive-proxy/{$id}?sz=w1200",
                            'thumb' => "/app/gdrive-proxy/{$id}?sz=w400",
                            'source' => 'gdrive'
                        ];
                        $idx++;
                    }
                    curl_multi_remove_handle($mh, $ch);
                    curl_close($ch);
                }
                curl_multi_close($mh);

                return $logos;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /**
     * Obtiene el listado de logos (Google Drive + Fallbacks locales)
     */
    public function getLogosList(): array
    {
        $driveLogos = $this->getDriveLogos();
        if (!empty($driveLogos)) {
            return $driveLogos;
        }

        // Fallback: assets locales en public/assets/logos/ o assets/
        $logos = [];
        $localPaths = [
            public_path("assets/logos/{$this->id}"),
            public_path("assets/logos"),
            public_path("assets/brand"),
        ];

        foreach ($localPaths as $absPath) {
            if (file_exists($absPath) && is_dir($absPath)) {
                $files = scandir($absPath);
                $idx = 1;
                foreach ($files as $file) {
                    if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['png', 'svg', 'webp', 'jpg', 'jpeg'])) {
                        $webPath = str_replace(public_path(), '', $absPath);
                        $webPath = rtrim($webPath, '/') . '/' . $file;
                        $logos[] = [
                            'id' => $file,
                            'filename' => $file,
                            'variant_label' => 'Logo Local #' . $idx,
                            'relPath' => $webPath,
                            'url' => $webPath,
                            'thumb' => $webPath,
                            'source' => 'local'
                        ];
                        $idx++;
                    }
                }
                if (!empty($logos)) break;
            }
        }

        return $logos;
    }
}
