<?php
namespace App\Http\Controllers;

use App\Mail\JobApplicationConfirmationMail;
use App\Mail\NewJobApplicationMail;
use App\Models\Career as Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class JobApplicationController extends Controller
{

    public function store(Request $request, $jobId)
    {
        $validator = Validator::make($request->all(), [
            'full_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'required|string|max:30',
            'experience' => 'required|string|max:100',
            'resume'     => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $job = Job::where('id', $jobId)
            ->where('is_published', true)
            ->first();

        if (! $job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found.',
            ], 404);
        }

        $resume = $request->file('resume')->store(
            'job-applications/resumes',
            'public'
        );

        $application = JobApplication::create([
            'job_id'     => $job->id,
            'full_name'  => $request->full_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'experience' => $request->experience,
            'resume'     => $resume,
        ]);

        Mail::to($application->email)->send(
            new JobApplicationConfirmationMail($application)
        );

        Mail::to(env('CONTACT_EMAIL'))->send(
            new NewJobApplicationMail($application)
        );

        return response()->json([
            'success' => true,
            'message' => 'Job application submitted successfully.',
            'data'    => [
                'id'         => $application->id,
                'job_id'     => $application->job_id,
                'full_name'  => $application->full_name,
                'email'      => $application->email,
                'phone'      => $application->phone,
                'experience' => $application->experience,
                'resume'     => asset('storage/' . $application->resume),
            ],
        ], 201);
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $applications = JobApplication::with('job')
            ->latest()
            ->paginate($perPage);

        $applications->getCollection()->transform(function ($application) {
            return [
                'id'         => $application->id,
                'job_id'     => $application->job_id,
                'job_title'  => $application->job?->job_title,
                'full_name'  => $application->full_name,
                'email'      => $application->email,
                'phone'      => $application->phone,
                'experience' => $application->experience,
                'resume'     => $application->resume
                    ? asset('storage/' . $application->resume)
                    : null,
                'created_at' => $application->created_at,
            ];
        });

        return response()->json([
            'success'    => true,
            'message'    => 'Job applications fetched successfully.',
            'data'       => $applications->items(),
            'pagination' => [
                'current_page'  => $applications->currentPage(),
                'per_page'      => $applications->perPage(),
                'total'         => $applications->total(),
                'last_page'     => $applications->lastPage(),
                'from'          => $applications->firstItem(),
                'to'            => $applications->lastItem(),
                'next_page'     => $applications->nextPageUrl(),
                'previous_page' => $applications->previousPageUrl(),
            ],
        ], 200);
    }
}