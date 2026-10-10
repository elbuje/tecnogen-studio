<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Character extends Model
{
    protected $table = 'characters';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'brand_id',
        'name',
        'role',
        'gdrive_folder_url',
        'gdrive_folder_id',
        'folder_path',
        'cover_photo',
        'notes',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (!empty($model->gdrive_folder_url) && empty($model->gdrive_folder_id)) {
                $model->gdrive_folder_id = self::extractDriveFolderId($model->gdrive_folder_url);
            }
        });

        static::updating(function ($model) {
            if (!empty($model->gdrive_folder_url)) {
                $model->gdrive_folder_id = self::extractDriveFolderId($model->gdrive_folder_url);
            }
        });
    }

    /**
     * Extrae el ID de una URL de carpeta de Google Drive
     */
    public static function extractDriveFolderId(?string $url): ?string
    {
        if (empty($url)) return null;

        // Si ya es un ID directo
        if (preg_match('/^[a-zA-Z0-9_-]{20,}$/', $url)) {
            return $url;
        }

        // Formato /folders/{ID}
        if (preg_match('/\/folders\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Formato id={ID}
        if (preg_match('/id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Escanea y obtiene las fotos de Google Drive de forma paralela y ultra rápida
     */
    public function getDrivePhotos(): array
    {
        $folderId = $this->gdrive_folder_id ?: self::extractDriveFolderId($this->gdrive_folder_url);
        if (!$folderId) {
            return [];
        }

        return Cache::remember("gdrive_photos_{$folderId}", 86400, function () use ($folderId) {
            try {
                $url = "https://drive.google.com/drive/folders/{$folderId}";
                $response = Http::timeout(6)->get($url);
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

                $photos = [];
                $idx = 1;
                foreach ($handles as $id => $ch) {
                    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                    if ($code === 200 && str_starts_with($type, 'image/')) {
                        $photos[] = [
                            'id' => $id,
                            'filename' => "Foto_" . sprintf('%02d', $idx) . ".jpg",
                            'relPath' => "/app/gdrive-proxy/{$id}?sz=w1200",
                            'url' => "/app/gdrive-proxy/{$id}?sz=w1200",
                            'thumb' => "/app/gdrive-proxy/{$id}?sz=w300",
                            'source' => 'gdrive'
                        ];
                        $idx++;
                    }
                    curl_multi_remove_handle($mh, $ch);
                    curl_close($ch);
                }
                curl_multi_close($mh);

                return $photos;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /**
     * Obtiene el listado de archivos de fotos disponibles (Google Drive prioritario)
     */
    public function getPhotosList(): array
    {
        // 1. Prioridad: Fotos de Google Drive
        $drivePhotos = $this->getDrivePhotos();
        if (!empty($drivePhotos)) {
            return $drivePhotos;
        }

        // 2. Fallback: Fotos locales
        $photos = [];
        if (!empty($this->folder_path) && !str_starts_with($this->folder_path, 'http')) {
            $baseFolder = $this->folder_path;
            $absPath = str_starts_with($baseFolder, '/') ? $baseFolder : public_path($baseFolder);

            if (!file_exists($absPath) || !is_dir($absPath)) {
                $fallback = public_path("assets/characters/{$this->folder_path}");
                if (file_exists($fallback) && is_dir($fallback)) {
                    $absPath = $fallback;
                }
            }

            if (file_exists($absPath) && is_dir($absPath)) {
                $files = scandir($absPath);
                foreach ($files as $file) {
                    if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'hif', 'heic'])) {
                        $webPath = str_replace(public_path(), '', $absPath);
                        $webPath = rtrim($webPath, '/') . '/' . $file;
                        $photos[] = [
                            'id' => $file,
                            'filename' => $file,
                            'relPath' => $webPath,
                            'url' => $webPath,
                            'thumb' => $webPath,
                            'source' => 'local'
                        ];
                    }
                }
            }
        }

        return $photos;
    }
}
