<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::table('users', function (Blueprint $table) { $table->string('brand_name')->nullable()->after('email'); $table->string('business_phone')->nullable()->after('brand_name'); $table->string('business_email')->nullable()->after('business_phone'); $table->string('business_address')->nullable()->after('business_email'); $table->string('business_website')->nullable()->after('business_address'); $table->string('default_cta')->nullable()->after('business_website'); }); }
    public function down(): void { Schema::table('users', function (Blueprint $table) { $table->dropColumn(['brand_name','business_phone','business_email','business_address','business_website','default_cta']); }); }
};
