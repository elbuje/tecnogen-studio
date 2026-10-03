<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Content;
use App\Models\Slide;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $brandIds = Brand::where('user_id', $user->id)->pluck('id');
        
        $brandId = $request->query('brand_id');
        $query = Content::with('slides')->orderBy('created_at', 'desc');

        if ($brandId) {
            $query->where('brand_id', $brandId);
        } else {
            $query->whereIn('brand_id', $brandIds);
        }

        return response()->json($query->get());
    }

    public function show(Request $request, string $id)
    {
        $content = Content::with('slides')->find($id);
        if (!$content) {
            return response()->json(['detail' => 'Contenido no encontrado'], 404);
        }

        return response()->json($content);
    }

    public function publicShow(string $id)
    {
        $content = Content::with('slides')->find($id);
        if (!$content) {
            return response()->json(['detail' => 'Contenido no encontrado'], 404);
        }

        return response()->json($content);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand_id' => 'required|string',
            'title' => 'required|string',
            'hook_text' => 'nullable|string',
            'caption_copy' => 'nullable|string',
            'hashtags' => 'nullable|string',
            'total_slides' => 'nullable|integer',
            'type' => 'nullable|string',
        ]);

        $content = Content::create([
            'brand_id' => $validated['brand_id'],
            'title' => $validated['title'],
            'hook_text' => $validated['hook_text'] ?? null,
            'caption_copy' => $validated['caption_copy'] ?? null,
            'hashtags' => $validated['hashtags'] ?? null,
            'total_slides' => $validated['total_slides'] ?? 6,
            'type' => $validated['type'] ?? 'carousel',
            'status' => 'draft',
            'source' => 'web_form',
            'version' => 1,
        ]);

        return response()->json($content, 201);
    }

    public function updateStatus(Request $request, string $id)
    {
        $content = Content::find($id);
        if (!$content) {
            return response()->json(['detail' => 'Contenido no encontrado'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $content->update(['status' => $validated['status']]);
        return response()->json($content);
    }

    public function updateSlide(Request $request, string $contentId, string $slideId)
    {
        $slide = Slide::where('content_id', $contentId)->where('id', $slideId)->first();
        if (!$slide) {
            return response()->json(['detail' => 'Slide no encontrado'], 404);
        }

        $slide->update($request->all());
        return response()->json($slide);
    }
}
