<?php
namespace App\Http\Controllers;

use App\Models\Career as Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_title'          => 'required|string|max:255',
            'department'         => 'required|string|max:100',
            'job_type'           => 'required|string|max:100',
            'location'           => 'required|string|max:255',
            'experience'         => 'required|string|max:100',
            'work_mode'          => 'required|string|max:100',
            'description'        => 'required|string',
            'responsibilities'   => 'required|array',
            'responsibilities.*' => 'required|string|max:1000',
            'requirements'       => 'required|array',
            'requirements.*'     => 'required|string|max:1000',
            'is_published'       => 'nullable|boolean',
            'is_featured'        => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $job = Job::create([
            'user_id'          => $user->id,
            'job_title'        => $request->job_title,
            'department'       => $request->department,
            'job_type'         => $request->job_type,
            'location'         => $request->location,
            'experience'       => $request->experience,
            'work_mode'        => $request->work_mode,
            'description'      => $request->description,
            'responsibilities' => $request->responsibilities,
            'requirements'     => $request->requirements,
            'is_published'     => $request->boolean('is_published'),
            'is_featured'      => $request->boolean('is_featured'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job created successfully.',
            'data'    => $job,
        ], 201);
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $jobs = Job::latest()->paginate($perPage);

        return response()->json([
            'success'    => true,
            'message'    => 'Jobs fetched successfully.',
            'data'       => $jobs->items(),
            'pagination' => [
                'current_page'  => $jobs->currentPage(),
                'per_page'      => $jobs->perPage(),
                'total'         => $jobs->total(),
                'last_page'     => $jobs->lastPage(),
                'from'          => $jobs->firstItem(),
                'to'            => $jobs->lastItem(),
                'next_page'     => $jobs->nextPageUrl(),
                'previous_page' => $jobs->previousPageUrl(),
            ],
        ], 200);
    }

    public function show($id)
    {
        $job = Job::find($id);

        if (! $job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Job fetched successfully.',
            'data'    => $job,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $job = Job::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'job_title'          => 'required|string|max:255',
            'department'         => 'required|string|max:100',
            'job_type'           => 'required|string|max:100',
            'location'           => 'required|string|max:255',
            'experience'         => 'required|string|max:100',
            'work_mode'          => 'required|string|max:100',
            'description'        => 'required|string',
            'responsibilities'   => 'required|array',
            'responsibilities.*' => 'required|string|max:1000',
            'requirements'       => 'required|array',
            'requirements.*'     => 'required|string|max:1000',
            'is_published'       => 'nullable|boolean',
            'is_featured'        => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $job->job_title        = $request->job_title;
        $job->department       = $request->department;
        $job->job_type         = $request->job_type;
        $job->location         = $request->location;
        $job->experience       = $request->experience;
        $job->work_mode        = $request->work_mode;
        $job->description      = $request->description;
        $job->responsibilities = $request->responsibilities;
        $job->requirements     = $request->requirements;
        $job->is_published     = $request->boolean('is_published');
        $job->is_featured      = $request->boolean('is_featured');
        $job->save();

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully.',
            'data'    => $job,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $job = Job::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found.',
            ], 404);
        }

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully.',
        ], 200);
    }

    public function togglePublish(Request $request, $id)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $job = Job::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found.',
            ], 404);
        }

        $job->is_published = ! $job->is_published;
        $job->save();

        return response()->json([
            'success' => true,
            'message' => 'Job publish status updated successfully.',
            'data'    => [
                'id'           => $job->id,
                'is_published' => $job->is_published,
            ],
        ], 200);
    }

    public function toggleFeatured(Request $request, $id)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $job = Job::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found.',
            ], 404);
        }

        $job->is_featured = ! $job->is_featured;
        $job->save();

        return response()->json([
            'success' => true,
            'message' => 'Job featured status updated successfully.',
            'data'    => [
                'id'          => $job->id,
                'is_featured' => $job->is_featured,
            ],
        ], 200);
    }


    public function publicIndex(Request $request)
{
    $query = Job::query()->where('is_published', true);

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('job_title', 'like', "%{$search}%")
                ->orWhere('department', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        });
    }

    if ($request->filled('department') && $request->department !== 'all') {
        $query->where('department', $request->department);
    }

    if ($request->filled('job_type') && $request->job_type !== 'all') {
        $query->where('job_type', $request->job_type);
    }

    $perPage = $request->get('per_page', 10);

    $jobs = $query->latest()->paginate($perPage);

    return response()->json([
        'success' => true,
        'message' => 'Jobs fetched successfully.',
        'data' => $jobs->items(),
        'pagination' => [
            'current_page' => $jobs->currentPage(),
            'per_page' => $jobs->perPage(),
            'total' => $jobs->total(),
            'last_page' => $jobs->lastPage(),
            'from' => $jobs->firstItem(),
            'to' => $jobs->lastItem(),
            'next_page' => $jobs->nextPageUrl(),
            'previous_page' => $jobs->previousPageUrl(),
        ],
    ], 200);
}

public function publicShow($id)
{
    $job = Job::where('id', $id)
        ->where('is_published', true)
        ->first();

    if (!$job) {
        return response()->json([
            'success' => false,
            'message' => 'Job not found.',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Job fetched successfully.',
        'data' => $job,
    ], 200);
}

public function publicDepartments()
{
    $departments = Job::where('is_published', true)
        ->whereNotNull('department')
        ->distinct()
        ->orderBy('department')
        ->pluck('department')
        ->values();

    return response()->json([
        'success' => true,
        'message' => 'Departments fetched successfully.',
        'data' => $departments,
    ], 200);
}

public function publicJobTypes()
{
    $jobTypes = Job::where('is_published', true)
        ->whereNotNull('job_type')
        ->distinct()
        ->orderBy('job_type')
        ->pluck('job_type')
        ->values();

    return response()->json([
        'success' => true,
        'message' => 'Job types fetched successfully.',
        'data' => $jobTypes,
    ], 200);
}
}