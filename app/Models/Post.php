<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = ['user_id', 'social_connection_id', 'title', 'content', 'content_type', 'seo_keywords', 'cta', 'image_prompt', 'image_url', 'status', 'publish_mode', 'scheduled_at', 'published_at', 'platform_post_id', 'platform_post_url', 'publish_error'];
    protected function casts(): array { return ['scheduled_at' => 'datetime', 'published_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function connection(): BelongsTo { return $this->belongsTo(SocialConnection::class, 'social_connection_id'); }
}
