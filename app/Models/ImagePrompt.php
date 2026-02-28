<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagePrompt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'original_image_path',
        'processed_image_path',
        'prompt',
        'status',
        'error_message',
        'file_type',
        'output_type',
        'processing_time',
    ];

    /**
     * Get the user that owns the image prompt.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL for the original image.
     */
    public function getOriginalImageUrlAttribute()
    {
        return asset('storage/' . $this->original_image_path);
    }

    /**
     * Get the full URL for the processed image.
     */
    public function getProcessedImageUrlAttribute()
    {
        return $this->processed_image_path ? asset('storage/' . $this->processed_image_path) : null;
    }

    /**
     * Check if the output is a video
     */
    public function isVideoOutput(): bool
    {
        return $this->output_type === 'video';
    }

    /**
     * Get the processed file URL (works for both image and video)
     */
    public function getProcessedFileUrlAttribute()
    {
        return $this->processed_image_path ? asset('storage/' . $this->processed_image_path) : null;
    }
}
