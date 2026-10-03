<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\BrandAsset;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    private const PLAN_MAX_BRANDS = [
        'starter' => 1,
        'growth' => 3,
        'agency' => 9999,
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        if (in_array($user->role, ['admin', 'superadmin', 'support'])) {
            return response()->json(Brand::with('assets')->get());
        }

        return response()->json(Brand::where('user_id', $user->id)->with('assets')->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $maxBrands = self::PLAN_MAX_BRANDS[$user->plan_tier] ?? 1;
        $currentCount = Brand::where('user_id', $user->id)->count();

        if ($currentCount >= $maxBrands && !in_array($user->role, ['admin', 'superadmin'])) {
            return response()->json([
                'detail' => "Tu plan " . ucfirst($user->plan_tier) . " permite hasta {$maxBrands} marca(s).",
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'primary_color' => 'nullable|string',
            'accent_color' => 'nullable|string',
            'bg_color' => 'nullable|string',
            'font_style_title' => 'nullable|string',
            'font_style_body' => 'nullable|string',
            'logo_position' => 'nullable|string',
            'logo_width_px' => 'nullable|integer',
            'gdrive_input_folder_id' => 'nullable|string',
            'gdrive_output_folder_id' => 'nullable|string',
            'sheets_url' => 'nullable|string',
            'metricool_user_token' => 'nullable|string',
            'metricool_blog_id' => 'nullable|string',
            'brand_rules' => 'nullable|array',
        ]);

        $brand = Brand::create(array_merge($validated, [
            'user_id' => $user->id,
            'created_at' => now(),
        ]));

        return response()->json($brand, 201);
    }

    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $brand = Brand::with('assets')->find($id);

        if (!$brand) {
            return response()->json(['detail' => 'Marca no encontrada'], 404);
        }

        if ($brand->user_id !== $user->id && !in_array($user->role, ['admin', 'superadmin', 'support'])) {
            return response()->json(['detail' => 'No tienes acceso a esta marca'], 403);
        }

        return response()->json($brand);
    }

    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['detail' => 'Marca no encontrada'], 404);
        }

        if ($brand->user_id !== $user->id && !in_array($user->role, ['admin', 'superadmin', 'support'])) {
            return response()->json(['detail' => 'No tienes acceso a esta marca'], 403);
        }

        $brand->update($request->all());

        return response()->json($brand);
    }

    public function addAsset(Request $request, string $id)
    {
        $user = $request->user();
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['detail' => 'Marca no encontrada'], 404);
        }

        if ($brand->user_id !== $user->id && !in_array($user->role, ['admin', 'superadmin', 'support'])) {
            return response()->json(['detail' => 'No tienes acceso a esta marca'], 403);
        }

        $validated = $request->validate([
            'asset_type' => 'required|string',
            'label' => 'nullable|string',
            'file_url' => 'nullable|string',
            'gdrive_file_id' => 'nullable|string',
            'mime_type' => 'nullable|string',
        ]);

        $asset = BrandAsset::create(array_merge($validated, [
            'brand_id' => $brand->id,
            'created_at' => now(),
        ]));

        return response()->json($asset, 201);
    }

    public function deleteAsset(Request $request, string $id, string $assetId)
    {
        $user = $request->user();
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['detail' => 'Marca no encontrada'], 404);
        }

        if ($brand->user_id !== $user->id && !in_array($user->role, ['admin', 'superadmin', 'support'])) {
            return response()->json(['detail' => 'No tienes acceso a esta marca'], 403);
        }

        $asset = BrandAsset::where('brand_id', $brand->id)->where('id', $assetId)->first();
        if ($asset) {
            $asset->delete();
        }

        return response()->json(['status' => 'deleted']);
    }
}
