<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AISetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AISettingController extends Controller
{
    public function index()
    {
        return response()->json(AISetting::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string',
            'category' => 'required|string',
            'model_name' => 'required|string',
            'api_key_override' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'parameters' => 'nullable|array',
        ]);

        $setting = AISetting::updateOrCreate(
            ['provider' => $validated['provider'], 'category' => $validated['category']],
            $validated
        );

        return response()->json($setting);
    }

    public function fetchModels(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string',
            'api_key' => 'nullable|string',
        ]);

        $apiKey = $validated['api_key'] ?? env('OPENAI_API_KEY');

        if ($validated['provider'] === 'openai') {
            if (empty($apiKey)) {
                return response()->json(['detail' => 'Por favor ingresá tu API Key de OpenAI.'], 400);
            }

            $response = Http::withToken($apiKey)->get('https://api.openai.com/v1/models');
            if (!$response->successful()) {
                return response()->json(['detail' => 'Error al consultar OpenAI: ' . $response->body()], $response->status());
            }

            $data = $response->json('data', []);
            $models = [];
            foreach ($data as $m) {
                $id = $m['id'] ?? '';
                $type = 'other';
                if (str_contains($id, 'dall-e') || str_contains($id, 'image') || str_contains($id, 'sunburst')) {
                    $type = 'image';
                } elseif (str_contains($id, 'gpt') || str_contains($id, 'o1') || str_contains($id, 'o3')) {
                    $type = 'chat';
                }

                $models[] = [
                    'id' => $id,
                    'created' => $m['created'] ?? time(),
                    'owned_by' => $m['owned_by'] ?? 'openai',
                    'type' => $type,
                ];
            }

            return response()->json([
                'provider' => 'openai',
                'models' => $models,
                'total' => count($models),
            ]);
        }

        return response()->json(['provider' => $validated['provider'], 'models' => [], 'total' => 0]);
    }
}
