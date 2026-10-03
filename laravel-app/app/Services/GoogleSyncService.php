<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Content;
use App\Models\Slide;
use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Google\Service\Drive;
use Illuminate\Support\Facades\Log;

class GoogleSyncService
{
    private string $serviceAccountPath;

    public function __construct()
    {
        $this->serviceAccountPath = base_path('../backend/service_account.json');
        if (!file_exists($this->serviceAccountPath)) {
            $this->serviceAccountPath = base_path('service_account.json');
        }
    }

    public function getGoogleClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setAuthConfig($this->serviceAccountPath);
        $client->setScopes([
            Sheets::SPREADSHEETS,
            Drive::DRIVE,
        ]);
        return $client;
    }

    public function syncSheetForBrand(Brand $brand): array
    {
        if (empty($brand->sheets_url)) {
            return ['status' => 'skipped', 'message' => 'Sin URL de Google Sheets configurada'];
        }

        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $brand->sheets_url, $matches);
        $spreadsheetId = $matches[1] ?? null;

        if (!$spreadsheetId) {
            return ['status' => 'error', 'message' => 'ID de planilla inválido'];
        }

        $client = $this->getGoogleClient();
        $sheetsService = new Sheets($client);

        $range = 'Carruseles!A1:Z500';
        $response = $sheetsService->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        if (empty($values)) {
            return ['status' => 'empty', 'message' => 'Planilla vacía'];
        }

        $headers = array_map('strtoupper', array_map('trim', $values[0]));
        $rows = array_slice($values, 1);

        $imported = 0;
        foreach ($rows as $rowIndex => $row) {
            $rowNum = $rowIndex + 2;
            $data = [];
            foreach ($headers as $colIndex => $colName) {
                $data[$colName] = $row[$colIndex] ?? '';
            }

            $title = $data['TITULO'] ?? $data['TÍTULO'] ?? $data['TITULO DEL TEMA'] ?? "Carrusel Fila {$rowNum}";
            if (empty(trim($title))) {
                continue;
            }

            $content = Content::updateOrCreate(
                [
                    'brand_id' => $brand->id,
                    'sheet_row_ref' => "Carruseles!A{$rowNum}",
                ],
                [
                    'title' => $title,
                    'hook_text' => $data['HOOK / GANCHO'] ?? $data['HOOK'] ?? null,
                    'caption_copy' => $data['COPY / CAPTION'] ?? $data['COPY'] ?? null,
                    'hashtags' => $data['HASHTAGS'] ?? null,
                    'status' => 'draft',
                    'source' => 'google_sheet',
                    'type' => 'carousel',
                ]
            );

            $imported++;
        }

        return ['status' => 'success', 'imported_count' => $imported];
    }
}
