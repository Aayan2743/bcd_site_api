<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Creator extends Model
{
    protected $fillable = [
        'user_id',

        'display_name',
        'tagline',
        'bio',
        'location',

        'categories',
        'languages',

        'services',
        'platforms',
        'social_links',

        'profile_photo',
        'portfolio_images',
        'featured_reel_url',
        'platform_stats',

        'follower_count',
        'average_reach',

        'show_follower_count',
        'show_average_reach',
        'show_enquiry_cta',

        'is_published',
        'is_featured',
    ];

    protected $casts = [

        'categories'          => 'array',
        'location'            => 'array',
        'languages'           => 'array',
        'services'            => 'array',
        'platform_stats'      => 'array',
        'platforms'           => 'array',
        'social_links'        => 'array',
        'portfolio_images'    => 'array',

        'follower_count'      => 'integer',
        'average_reach'       => 'integer',

        'show_follower_count' => 'boolean',
        'show_average_reach'  => 'boolean',
        'show_enquiry_cta'    => 'boolean',

        'is_published'        => 'boolean',
        'is_featured'         => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
