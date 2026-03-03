<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagePromptTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'coins_required',
        'description',
        'reference_image_path',
        'prompt',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all submissions using this template.
     */
    public function submissions()
    {
        return $this->hasMany(UserImageSubmission::class, 'template_id');
    }

    /**
     * Get the full URL for the reference image.
     */
    public function getReferenceImageUrlAttribute()
    {
        return $this->reference_image_path ? asset('storage/' . $this->reference_image_path) : null;
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }
}
