<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialConnection extends Model
{
    protected $fillable = ['user_id', 'provider', 'label', 'credentials', 'is_active'];
    protected function casts(): array { return ['credentials' => 'encrypted:array', 'is_active' => 'boolean']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
