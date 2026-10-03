<?php

namespace App\Jobs;

use App\Models\Brand;
use App\Models\Content;
use App\Models\Slide;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateCarouselJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        public string $contentId,
        public ?string $globalFeedback = null
    ) {}

    public function handle(): void
    {
        $content = Content::with(['brand', 'slides'])->find($this->contentId);
        if (!$content) {
            return;
        }

        $content->update(['status' => 'generating']);

        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            $content->update(['status' => 'failed']);
            Log::error("GenerateCarouselJob: Sin OPENAI_API_KEY");
            return;
        }

        try {
            $slides = $content->slides()->orderBy('slide_number')->get();
            $brand = $content->brand;

            foreach ($slides as $slide) {
                $slide->update(['status' => 'generating']);

                $prompt = "Professional dental carousel slide #{$slide->slide_number}. Headline: '{$slide->headline}'. Body: '{$slide->body_text}'. Brand color: {$brand->primary_color}. Clean, clinical editorial aesthetic, high resolution.";

                // Si se usa DALL-E-3 o Sunburst
                $res = Http::withToken($apiKey)->timeout(120)->post('https://api.openai.com/v1/images/generations', [
                    'model' => env('OPENAI_IMAGE_MODEL', 'dall-e-3'),
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => '1024x1024',
                ]);

                if ($res->successful()) {
                    $imgUrl = $res->json('data.0.url');
                    $slide->update([
                        'image_url' => $imgUrl,
                        'prompt_used' => $prompt,
                        'status' => 'generated',
                    ]);
                } else {
                    $slide->update([
                        'status' => 'failed',
                        'feedback' => $res->body(),
                    ]);
                }
            }

            $hasFailed = $content->slides()->where('status', 'failed')->exists();
            $content->update([
                'status' => $hasFailed ? 'ready_for_review' : 'ready_for_review',
            ]);
        } catch (\Throwable $e) {
            Log::error("Error en GenerateCarouselJob: " . $e->getMessage());
            $content->update(['status' => 'failed']);
        }
    }
}
