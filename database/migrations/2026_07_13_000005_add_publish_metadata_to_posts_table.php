<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::table('posts', function (Blueprint $table) { $table->string('platform_post_id')->nullable()->after('published_at'); $table->text('publish_error')->nullable()->after('platform_post_id'); }); }
    public function down(): void { Schema::table('posts', function (Blueprint $table) { $table->dropColumn(['platform_post_id','publish_error']); }); }
};
