<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::create('posts', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('social_connection_id')->nullable()->constrained()->nullOnDelete(); $table->string('title'); $table->longText('content'); $table->text('image_prompt')->nullable(); $table->string('image_url')->nullable(); $table->string('status')->default('draft'); $table->string('publish_mode')->default('review'); $table->timestamp('scheduled_at')->nullable(); $table->timestamp('published_at')->nullable(); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('posts'); }
};
