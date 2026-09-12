<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrowthAuditRequest extends Model
{
    protected $fillable = [
        'business_type',
        'primary_goal',
        'main_channel',
        'biggest_challenge',
        'monthly_budget',
        'timeline',

        'client_name',
        'phone',
        'email',
        'company_name',

        'recommended_package',
    ];
}
