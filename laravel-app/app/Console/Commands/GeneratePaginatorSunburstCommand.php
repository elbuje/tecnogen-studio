<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\PaginatorImageService;

class GeneratePaginatorSunburstCommand extends Command
{
    protected $signature = 'paginators:sunburst {--batch=} {--all} {--force}';
    protected $description = 'Genera imágenes de muestra de todos los paginadores en español exclusivamente con OpenAI gpt-image-2.5-sunburst';

    public function handle()
    {
        $apiKey = env('OPENAI_API_KEY');
        $model = env('OPENAI_IMAGE_MODEL', 'gpt-image-2.5-sunburst');
        $force = $this->option('force');

        $this->info("🎨 Iniciando generación con modelo exclusivo: {$model} (100% en Español)");

        $allPaginators = PaginatorImageService::getPaginatorsList();
        
        if ($this->option('all')) {
            $numbers = range(1, 49);
        } elseif ($this->option('batch')) {
            $numbers = array_map('intval', explode(',', $this->option('batch')));
        } else {
            $numbers = range(1, 49);
        }

        $outputDirs = [
            public_path('assets/paginators'),
            base_path('../public/assets/paginators'),
        ];

        foreach ($outputDirs as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }
        }

        $total = count($numbers);
        $current = 0;

        foreach ($numbers as $num) {
            $current++;
            if ($num <= 0) continue;

            $numStr = sprintf('%02d', $num);
            $filename = "paginator_{$numStr}.png";
            $targetPath = "{$outputDirs[0]}/{$filename}";

            // Si ya existe y no es forzado, saltar
            if (!$force && file_exists($targetPath) && filesize($targetPath) > 500000) {
                $this->line("⏩ [#{$numStr} ({$current}/{$total})] Ya existe ({$filename}). Saltando...");
                continue;
            }

            // Buscar datos del paginador
            $pData = null;
            foreach ($allPaginators as $p) {
                if ($p['number'] === $num) {
                    $pData = $p;
                    break;
                }
            }

            if (!$pData) {
                $this->warn("⚠️ [#{$numStr}] No se encontró definición para el paginador {$num}.");
                continue;
            }

            $this->info("\n🚀 [#{$numStr} ({$current}/{$total})] Generando: {$pData['name']}...");

            $prompt = "Tarjeta de diseño UI profesional para una lámina de carrusel odontológico y médico para redes sociales. "
                . "Estética: SaaS moderna, ultra limpia, fondo blanco (#FFFFFF) con bordes suaves (#E2E8F0) y contraste azul marino (#16345F). "
                . "TODO EL TEXTO VISIBLE DEBE ESTAR ESTRICTAMENTE EN ESPAÑOL (ej: 'Salud Dental', 'Odontología Integral', 'Plan Clínico'). "
                . "Elemento principal destacado: Estilo de paginación '{$pData['name']}'. "
                . "Especificación del componente visual: {$pData['description']}. "
                . "El indicador de paginación debe ser el foco visual, perfectamente renderizado, nítido y en primer plano. "
                . "Diseño clínico de alta gama, luz de día, sin fondos oscuros ni saturación.";

            try {
                $response = Http::withToken($apiKey)
                    ->timeout(120)
                    ->post('https://api.openai.com/v1/images/generations', [
                        'model' => $model,
                        'prompt' => $prompt,
                        'n' => 1,
                        'size' => '1024x1024'
                    ]);

                if (!$response->successful()) {
                    $err = $response->json()['error']['message'] ?? $response->body();
                    $this->error("❌ Error de OpenAI para #{$numStr}: {$err}");
                    continue;
                }

                $data = $response->json();
                $imageBytes = null;

                $b64 = $data['data'][0]['b64_json'] ?? null;
                if ($b64) {
                    $imageBytes = base64_decode($b64);
                } elseif (!empty($data['data'][0]['url'])) {
                    $imageBytes = Http::timeout(60)->get($data['data'][0]['url'])->body();
                }

                if (!$imageBytes) {
                    $this->error("❌ No se recibieron datos binarios para #{$numStr}");
                    continue;
                }

                foreach ($outputDirs as $dir) {
                    file_put_contents("{$dir}/{$filename}", $imageBytes);
                }

                $this->info("✅ [#{$numStr}] Guardado exitosamente: {$filename} (" . strlen($imageBytes) . " bytes)");

            } catch (\Throwable $e) {
                $this->error("❌ Excepción en #{$numStr}: " . $e->getMessage());
            }

            // Pausa de 1 segundo para no saturar la API
            sleep(1);
        }

        $this->info("\n🎉 Proceso de generación completa finalizado exitosamente.");
        return 0;
    }
}
