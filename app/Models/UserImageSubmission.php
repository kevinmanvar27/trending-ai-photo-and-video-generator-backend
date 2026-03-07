<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserImageSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'original_image_path',
        'processed_image_path',
        'output_type',
        'status',
        'error_message',
        'processing_time',
        'coins_used',
        'coins_from_referral',
        'coins_from_subscription',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that submitted the image.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the template used for this submission.
     */
    public function template()
    {
        return $this->belongsTo(ImagePromptTemplate::class, 'template_id');
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
}
