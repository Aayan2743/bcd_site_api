<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'category',
        'published_date',
        'excerpt',
        'content',
        'cover_image',
        'video_url',
        'seo_title',
        'meta_description',
        'slug',
        'key_phrases',
        'is_published',
        'is_featured',
        'created_by',
    ];

    protected $casts = [
        'published_date' => 'date:Y-m-d',
        'is_published'   => 'boolean',
        'is_featured'    => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
