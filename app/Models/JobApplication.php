<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_id',
        'full_name',
        'email',
        'phone',
        'experience',
        'resume',
    ];

    public function job()
    {
        return $this->belongsTo(Career::class);
    }

    public function applications()
{
    return $this->hasMany(JobApplication::class);
}
}