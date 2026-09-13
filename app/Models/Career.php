<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    protected $fillable = [

        'user_id',

        'job_title',
        'slug',
        'department',
        'job_type',
        'location',
        'experience',
        'work_mode',

        'description',

        'responsibilities',
        'requirements',

        'is_published',
        'is_featured',
    ];

    protected $casts = [

        'responsibilities' => 'array',

        'requirements'     => 'array',

        'is_published'     => 'boolean',

        'is_featured'      => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
