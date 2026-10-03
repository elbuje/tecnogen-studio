<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Modificar / asegurar columnas en users
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('email')->unique();
                $table->string('password');
                $table->string('full_name')->nullable();
                $table->string('role', 20)->default('client');
                $table->string('plan_tier', 20)->default('growth');
                $table->string('commercial_status', 30)->default('active');
                $table->string('plan_name', 50)->nullable()->default('Plan Growth Pro');
                $table->integer('plan_price_monthly')->nullable()->default(150);
                $table->integer('monthly_video_limit')->default(30);
                $table->integer('videos_generated_this_month')->default(0);
                $table->integer('avatar_minutes_quota')->default(60);
                $table->integer('avatar_minutes_used')->default(0);
                $table->boolean('auto_mode_enabled')->default(false);
                $table->text('sheet_url')->nullable();
                $table->string('sheet_auto_mode', 30)->default('copilot');
                $table->dateTime('sheet_last_sync_at')->nullable();
                $table->text('notes')->nullable();
                $table->integer('credits_balance')->default(220);
                $table->text('google_oauth_token')->nullable();
                $table->text('google_refresh_token')->nullable();
                $table->string('stripe_customer_id', 100)->nullable();
                $table->string('mercadopago_payer_id', 100)->nullable();
                $table->dateTime('plan_renewal_date')->nullable();
                $table->timestamps();
            });
        }

        // 2. brands
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('user_id')->index();
                $table->string('name', 100);
                $table->string('primary_color', 7)->default('#16345F');
                $table->string('accent_color', 7)->default('#7DD3FC');
                $table->string('bg_color', 7)->default('#0B1E38');
                $table->string('font_style_title', 50)->default('serif-editorial');
                $table->string('font_style_body', 50)->default('sans-modern');
                $table->string('layout_preset', 50)->default('editorial-top');
                $table->string('logo_position', 30)->default('top-left');
                $table->integer('logo_width_px')->default(180);
                $table->text('gdrive_logos_folder_id')->nullable();
                $table->text('gdrive_subjects_folder_id')->nullable();
                $table->text('gdrive_brand_manual_folder_id')->nullable();
                $table->text('gdrive_templates_folder_id')->nullable();
                $table->text('gdrive_products_folder_id')->nullable();
                $table->text('gdrive_input_folder_id')->nullable();
                $table->text('gdrive_output_folder_id')->nullable();
                $table->text('sheets_url')->nullable();
                $table->text('metricool_user_token')->nullable();
                $table->string('metricool_blog_id', 100)->nullable();
                $table->json('brand_rules')->nullable();
                $table->dateTime('created_at')->useCurrent();
            });
        }

        // 3. brand_assets
        if (!Schema::hasTable('brand_assets')) {
            Schema::create('brand_assets', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('brand_id')->index();
                $table->string('asset_type', 30);
                $table->string('label', 100)->nullable();
                $table->text('file_url')->nullable();
                $table->text('gdrive_file_id')->nullable();
                $table->string('mime_type', 50)->nullable();
                $table->dateTime('created_at')->useCurrent();
            });
        }

        // 4. contents
        if (!Schema::hasTable('contents')) {
            Schema::create('contents', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('brand_id')->index();
                $table->string('type', 20)->default('carousel');
                $table->string('title', 255);
                $table->text('hook_text')->nullable();
                $table->text('caption_copy')->nullable();
                $table->text('hashtags')->nullable();
                $table->string('status', 30)->default('draft')->index();
                $table->string('source', 30)->default('web_form');
                $table->string('sheet_row_ref', 50)->nullable();
                $table->integer('total_slides')->default(6);
                $table->string('metricool_post_id', 100)->nullable();
                $table->dateTime('scheduled_for')->nullable();
                $table->integer('version')->default(1);
                $table->timestamps();
            });
        }

        // 5. slides
        if (!Schema::hasTable('slides')) {
            Schema::create('slides', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('content_id')->index();
                $table->integer('slide_number');
                $table->string('slide_type', 30)->default('content');
                $table->text('image_url')->nullable();
                $table->text('gdrive_file_id')->nullable();
                $table->text('headline')->nullable();
                $table->text('body_text')->nullable();
                $table->string('badge', 50)->nullable();
                $table->text('prompt_used')->nullable();
                $table->text('feedback')->nullable();
                $table->string('status', 20)->default('pending');
                $table->integer('version')->default(1);
                $table->dateTime('created_at')->useCurrent();
            });
        }

        // 6. ai_settings
        if (!Schema::hasTable('ai_settings')) {
            Schema::create('ai_settings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('provider', 50)->default('openai');
                $table->string('category', 30)->default('image');
                $table->string('model_name', 100)->default('gpt-image-2.5-sunburst');
                $table->text('api_key_override')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('parameters')->nullable();
                $table->timestamps();
            });
        }

        // 7. credit_ledger
        if (!Schema::hasTable('credit_ledger')) {
            Schema::create('credit_ledger', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('user_id')->index();
                $table->integer('amount');
                $table->string('action_type', 50);
                $table->uuid('reference_id')->nullable();
                $table->text('description')->nullable();
                $table->dateTime('created_at')->useCurrent()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_ledger');
        Schema::dropIfExists('ai_settings');
        Schema::dropIfExists('slides');
        Schema::dropIfExists('contents');
        Schema::dropIfExists('brand_assets');
        Schema::dropIfExists('brands');
    }
};
