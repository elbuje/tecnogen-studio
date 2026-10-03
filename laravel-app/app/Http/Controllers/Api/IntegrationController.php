<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function getStatus(Request $request)
    {
        $user = $request->user();
        $brandId = $request->query('brand_id');

        $brand = $brandId 
            ? Brand::find($brandId) 
            : Brand::where('user_id', $user->id)->first();

        return response()->json([
            'brand_id' => $brand ? $brand->id : null,
            'brand_name' => $brand ? $brand->name : null,
            'gdrive' => [
                'connected' => (bool) ($brand && ($brand->gdrive_logos_folder_id || $brand->gdrive_subjects_folder_id || $brand->gdrive_input_folder_id || $brand->gdrive_output_folder_id)),
                'logos_folder_id' => $brand ? $brand->gdrive_logos_folder_id : null,
                'subjects_folder_id' => $brand ? $brand->gdrive_subjects_folder_id : null,
                'brand_manual_folder_id' => $brand ? $brand->gdrive_brand_manual_folder_id : null,
                'templates_folder_id' => $brand ? $brand->gdrive_templates_folder_id : null,
                'products_folder_id' => $brand ? $brand->gdrive_products_folder_id : null,
                'output_folder_id' => $brand ? $brand->gdrive_output_folder_id : null,
                'service_account_email' => env('GOOGLE_SERVICE_ACCOUNT_EMAIL', 'tecnogen-studio-sa@tecnogen-studio.iam.gserviceaccount.com'),
            ],
            'sheets' => [
                'connected' => (bool) ($brand && $brand->sheets_url),
                'sheets_url' => $brand ? $brand->sheets_url : null,
            ],
            'metricool' => [
                'connected' => (bool) ($brand && $brand->metricool_user_token),
                'blog_id' => $brand ? $brand->metricool_blog_id : null,
                'has_token' => (bool) ($brand && $brand->metricool_user_token),
            ],
            'api_keys_count' => 0,
        ]);
    }

    public function connectDrive(Request $request)
    {
        $validated = $request->validate([
            'brand_id' => 'required|string',
            'logos_folder_id' => 'nullable|string',
            'subjects_folder_id' => 'nullable|string',
            'brand_manual_folder_id' => 'nullable|string',
            'templates_folder_id' => 'nullable|string',
            'products_folder_id' => 'nullable|string',
            'output_folder_id' => 'nullable|string',
        ]);

        $brand = Brand::find($validated['brand_id']);
        if (!$brand) {
            return response()->json(['detail' => 'Marca no encontrada'], 404);
        }

        $brand->update($validated);
        return response()->json(['status' => 'connected', 'brand' => $brand]);
    }

    public function connectSheets(Request $request)
    {
        $validated = $request->validate([
            'brand_id' => 'required|string',
            'sheets_url' => 'required|string',
        ]);

        $brand = Brand::find($validated['brand_id']);
        if (!$brand) {
            return response()->json(['detail' => 'Marca no encontrada'], 404);
        }

        $brand->update(['sheets_url' => $validated['sheets_url']]);
        return response()->json(['status' => 'connected', 'sheets_url' => $brand->sheets_url]);
    }

    public function connectMetricool(Request $request)
    {
        $validated = $request->validate([
            'brand_id' => 'required|string',
            'metricool_user_token' => 'required|string',
            'metricool_blog_id' => 'required|string',
        ]);

        $brand = Brand::find($validated['brand_id']);
        if (!$brand) {
            return response()->json(['detail' => 'Marca no encontrada'], 404);
        }

        $brand->update([
            'metricool_user_token' => $validated['metricool_user_token'],
            'metricool_blog_id' => $validated['metricool_blog_id'],
        ]);

        return response()->json(['status' => 'connected']);
    }
}
