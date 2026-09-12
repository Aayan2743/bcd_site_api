<?php
namespace App\Http\Controllers;

use App\Mail\GrowthAuditAdminMail;
use App\Mail\GrowthAuditClientMail;
use App\Models\GrowthAuditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class GrowthAuditController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_type'       => 'required|string|max:100',
            'primary_goal'        => 'required|string|max:150',
            'main_channel'        => 'nullable|string|max:100',
            'biggest_challenge'   => 'required|string|max:150',
            'monthly_budget'      => 'nullable|string|max:100',
            'timeline'            => 'nullable|string|max:100',

            'client_name'         => 'required|string|max:150',
            'phone'               => 'required|string|max:30',
            'email'               => 'required|email|max:255',
            'company_name'        => 'required|string|max:200',

            'recommended_package' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $audit = GrowthAuditRequest::create([
            'business_type'       => $request->business_type,
            'primary_goal'        => $request->primary_goal,
            'main_channel'        => $request->main_channel,
            'biggest_challenge'   => $request->biggest_challenge,
            'monthly_budget'      => $request->monthly_budget,
            'timeline'            => $request->timeline,

            'client_name'         => $request->client_name,
            'phone'               => $request->phone,
            'email'               => $request->email,
            'company_name'        => $request->company_name,

            'recommended_package' => $request->recommended_package,
        ]);

        Mail::to(env('ADMIN_MAIL'))
            ->send(new GrowthAuditAdminMail($audit));

// Send confirmation mail to client
        Mail::to($audit->email)
            ->send(new GrowthAuditClientMail($audit));

        return response()->json([
            'success' => true,
            'message' => 'Consultation request submitted successfully.',
            'data'    => $audit,
        ], 201);
    }


    public function index(Request $request)
{
    $perPage = $request->get('per_page', 10);
    $search = $request->get('search');

    $query = GrowthAuditRequest::query();

    // Search
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('client_name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere('phone', 'LIKE', "%{$search}%")
                ->orWhere('company_name', 'LIKE', "%{$search}%")
                ->orWhere('business_type', 'LIKE', "%{$search}%")
                ->orWhere('primary_goal', 'LIKE', "%{$search}%");
        });
    }

    $audits = $query
        ->latest()
        ->paginate($perPage);

    return response()->json([
        'success' => true,
        'message' => 'Growth audit requests fetched successfully.',
        'data' => $audits->items(),
        'pagination' => [
            'current_page' => $audits->currentPage(),
            'last_page' => $audits->lastPage(),
            'per_page' => $audits->perPage(),
            'total' => $audits->total(),
            'from' => $audits->firstItem(),
            'to' => $audits->lastItem(),
        ],
    ], 200);
}
}