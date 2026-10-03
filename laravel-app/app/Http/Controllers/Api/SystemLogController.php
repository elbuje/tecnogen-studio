<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemLogController extends Controller
{
    public function getLogs(Request $request)
    {
        $limit = (int) $request->query('limit', 100);
        $level = $request->query('level');
        $service = $request->query('service');

        $logs = [];

        $recentContents = Content::with('slides')->orderBy('created_at', 'desc')->limit(15)->get();
        foreach ($recentContents as $c) {
            $failedSlides = $c->slides->where('status', 'failed');
            if ($c->status === 'failed' || $failedSlides->count() > 0) {
                $errDetails = $failedSlides->map(fn($s) => "Slide {$s->slide_number}: {$s->feedback}")->join("\n");
                $logs[] = [
                    'id' => "content_err_{$c->id}",
                    'timestamp' => $c->updated_at ? $c->updated_at->toIso8601String() : $c->created_at->toIso8601String(),
                    'level' => 'ERROR',
                    'service' => 'Generación Carrusel',
                    'message' => "Fallo en carrusel '{$c->title}' (Fuente: {$c->source}) - Estado: {$c->status}",
                    'details' => $errDetails ?: 'Fallo en ejecución de tareas de generación.',
                ];
            } else {
                $logs[] = [
                    'id' => "content_ok_{$c->id}",
                    'timestamp' => $c->created_at->toIso8601String(),
                    'level' => 'SUCCESS',
                    'service' => 'Generación Carrusel',
                    'message' => "Carrusel '{$c->title}' generado con éxito ({$c->slides->count()} láminas listas)",
                    'details' => "ID: {$c->id} | Fuente: {$c->source}",
                ];
            }
        }

        if ($level && $level !== 'ALL') {
            $logs = array_values(array_filter($logs, fn($l) => $l['level'] === strtoupper($level)));
        }
        if ($service && $service !== 'ALL') {
            $logs = array_values(array_filter($logs, fn($l) => str_contains(strtolower($l['service']), strtolower($service))));
        }

        $dbConnection = config('database.default');
        $dbName = config("database.connections.{$dbConnection}.database", 'tecnogen_studio');

        return response()->json([
            'total' => count($logs),
            'logs' => array_slice($logs, 0, $limit),
            'server_time' => now()->toIso8601String(),
            'database_status' => "ONLINE (Laravel: {$dbConnection} / {$dbName})",
        ]);
    }
}
