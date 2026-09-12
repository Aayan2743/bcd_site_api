<?php
namespace App\Http\Controllers;

use App\Models\Creator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CreatorController extends Controller
{
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make($request->all(), [

            'display_name'             => 'required|string|max:60',

            'tagline'                  => 'required|string|max:80',

            'bio'                      => 'required|string|max:600',

            'location'                 => 'required|array',
            'location.*'               => 'string|max:100',

            'categories'               => 'required|array',
            'categories.*'             => 'string|max:100',

            'languages'                => 'required|array',
            'languages.*'              => 'string|max:100',

            'services'                 => 'required|array',
            'services.*'               => 'string|max:100',

            'platforms'                => 'required|array',
            'platforms.*'              => 'string|max:100',

            'social_links'             => 'required|array',

            'social_links.*.instagram' => 'required|string|max:1000',
            'social_links.*.youtube'   => 'nullable|string|max:1000',
            'social_links.*.facebook'  => 'nullable|string|max:1000',
            'social_links.*.tiktok'    => 'nullable|string|max:1000',
            'social_links.*.pinterest' => 'nullable|string|max:1000',

            'instagram_url'            => 'required|url',

            'profile_photo'            => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'portfolio_images'         => 'required|array|max:10',

            'portfolio_images.*'       => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'featured_reel_url'        => 'nullable|url|max:1000',

            'average_reach'            => 'nullable|integer|min:0',

            'show_follower_count'      => 'nullable|boolean',

            'show_average_reach'       => 'nullable|boolean',

            'show_enquiry_cta'         => 'nullable|boolean',

            'is_published'             => 'nullable|boolean',

            'is_featured'              => 'nullable|boolean',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validation Error
        |--------------------------------------------------------------------------
        */

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->first(),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Authenticated User
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        if (! $user) {

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Check Existing Creator
        |--------------------------------------------------------------------------
        */

        // $existingCreator = Creator::where(
        //     'user_id',
        //     $user->id
        // )->first();

        // if ($existingCreator) {

        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Creator profile already exists.',
        //     ], 409);
        // }

        /*
        |--------------------------------------------------------------------------
        | Profile Photo
        |--------------------------------------------------------------------------
        */

        $profilePhoto = null;

        if ($request->hasFile('profile_photo')) {

            $profilePhoto = $request
                ->file('profile_photo')
                ->store(
                    'creators/profile',
                    'public'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Portfolio Images
        |--------------------------------------------------------------------------
        */

        $portfolioImages = [];

        if ($request->hasFile('portfolio_images')) {

            foreach (
                $request->file('portfolio_images') as $image
            ) {

                $portfolioImages[] = $image
                    ->store(
                        'creators/portfolio',
                        'public'
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Dummy Follower Count
        |--------------------------------------------------------------------------
        |
        | Instagram count is not being fetched.
        | This is a dummy value for testing.
        |
        */

        $followerCount = 42;

        /*
        |--------------------------------------------------------------------------
        | Create Creator
        |--------------------------------------------------------------------------
        */

        $creator = Creator::create([

            'user_id'             => $user->id,

            'display_name'        =>
            $request->display_name,

            'tagline'             =>
            $request->tagline,

            'bio'                 =>
            $request->bio,

            'location'            =>
            $request->location,

            'categories'          =>
            $request->categories,

            'languages'           =>
            $request->languages,

            'services'            =>
            $request->services,

            'platforms'           =>
            $request->platforms,

            'social_links'        =>
            $request->social_links,

            'instagram_url'       =>
            $request->instagram_url,

            'profile_photo'       =>
            $profilePhoto,

            'portfolio_images'    =>
            $portfolioImages,

            'featured_reel_url'   =>
            $request->featured_reel_url,

            /*
            |--------------------------------------------------------------------------
            | Dummy Instagram Followers
            |--------------------------------------------------------------------------
            */

            'follower_count'      =>
            $followerCount,

            /*
            |--------------------------------------------------------------------------
            | Average Reach
            |--------------------------------------------------------------------------
            */

            'average_reach'       =>
            $request->average_reach ?? 0,

            /*
            |--------------------------------------------------------------------------
            | Visibility Settings
            |--------------------------------------------------------------------------
            */

            'show_follower_count' =>
            $request->boolean(
                'show_follower_count'
            ),

            'show_average_reach'  =>
            $request->boolean(
                'show_average_reach'
            ),

            'show_enquiry_cta'    =>
            $request->boolean(
                'show_enquiry_cta'
            ),

            /*
            |--------------------------------------------------------------------------
            | Publish Settings
            |--------------------------------------------------------------------------
            */

            'is_published'        =>
            $request->boolean(
                'is_published'
            ),

            'is_featured'         =>
            $request->boolean(
                'is_featured'
            ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'message' =>
            'Creator profile created successfully.',

            'data'    =>
            $this->formatCreator($creator),

        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | MY PROFILE
    |--------------------------------------------------------------------------
    */

    /**
     * index
     */

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $creators = Creator::with('user')
            ->latest()
            ->paginate($perPage);

        $creators->getCollection()->transform(function ($creator) {
            return $this->formatCreator($creator);
        });

        return response()->json([
            'success'    => true,
            'message'    => 'Creators fetched successfully.',
            'data'       => $creators->items(),
            'pagination' => [
                'current_page'  => $creators->currentPage(),
                'per_page'      => $creators->perPage(),
                'total'         => $creators->total(),
                'last_page'     => $creators->lastPage(),
                'from'          => $creators->firstItem(),
                'to'            => $creators->lastItem(),
                'next_page'     => $creators->nextPageUrl(),
                'previous_page' => $creators->previousPageUrl(),
            ],
        ], 200);
    }

    /**
     *
     *
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $creator = Creator::with('user')->find($id);

        if (! $creator) {
            return response()->json([
                'success' => false,
                'message' => 'Creator not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Creator fetched successfully.',
            'data'    => $this->formatCreator($creator),
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

        $validator = Validator::make($request->all(), [

            'display_name'           => 'required|string|max:60',

            'tagline'                => 'required|string|max:80',

            'bio'                    => 'required|string|max:600',

            'locations'              => 'required|array',
            'locations.*'            => 'string|max:100',

            'categories'             => 'required|array',
            'categories.*'           => 'string|max:100',

            'languages'              => 'required|array',
            'languages.*'            => 'string|max:100',

            'services'               => 'required|array',
            'services.*'             => 'string|max:100',

            'platforms'              => 'required|array',
            'platforms.*'            => 'string|max:100',

            'social_links'           => 'required|array',

            'social_links.instagram' => 'required|string|max:1000',
            'social_links.youtube'   => 'nullable|string|max:1000',
            'social_links.facebook'  => 'nullable|string|max:1000',
            'social_links.tiktok'    => 'nullable|string|max:1000',
            'social_links.pinterest' => 'nullable|string|max:1000',

            'instagram_url'          => 'required|url',

            'profile_photo'          => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'portfolio_images'       => 'nullable|array|max:10',

            'portfolio_images.*'     => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'featured_reel_url'      => 'nullable|url|max:1000',

            'average_reach'          => 'nullable|integer|min:0',

            'show_follower_count'    => 'nullable|boolean',

            'show_average_reach'     => 'nullable|boolean',

            'show_enquiry_cta'       => 'nullable|boolean',

            'is_published'           => 'nullable|boolean',

            'is_featured'            => 'nullable|boolean',
        ]);

        /*
    |--------------------------------------------------------------------------
    | Validation Error
    |--------------------------------------------------------------------------
    */

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->first(),
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Authenticated User
    |--------------------------------------------------------------------------
    */

        $user = $request->user();

        if (! $user) {

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        /*
    |--------------------------------------------------------------------------
    | Find Creator
    |--------------------------------------------------------------------------
    */

        $creator = Creator::where(
            'user_id',
            $user->id
        )->first();

        if (! $creator) {

            return response()->json([
                'success' => false,
                'message' => 'Creator profile not found.',
            ], 404);
        }

        /*
    |--------------------------------------------------------------------------
    | Profile Photo
    |--------------------------------------------------------------------------
    */

        if ($request->hasFile('profile_photo')) {

            // Delete old profile photo
            if (
                $creator->profile_photo &&
                \Storage::disk('public')->exists(
                    $creator->profile_photo
                )
            ) {
                \Storage::disk('public')->delete(
                    $creator->profile_photo
                );
            }

            $creator->profile_photo = $request
                ->file('profile_photo')
                ->store(
                    'creators/profile',
                    'public'
                );
        }

        /*
    |--------------------------------------------------------------------------
    | Portfolio Images
    |--------------------------------------------------------------------------
    */

        if ($request->hasFile('portfolio_images')) {

            // Delete old portfolio images
            if (is_array($creator->portfolio_images)) {

                foreach ($creator->portfolio_images as $oldImage) {

                    if (
                        \Storage::disk('public')->exists(
                            $oldImage
                        )
                    ) {
                        \Storage::disk('public')->delete(
                            $oldImage
                        );
                    }
                }
            }

            $portfolioImages = [];

            foreach (
                $request->file('portfolio_images') as $image
            ) {

                $portfolioImages[] = $image
                    ->store(
                        'creators/portfolio',
                        'public'
                    );
            }

            $creator->portfolio_images = $portfolioImages;
        }

        /*
    |--------------------------------------------------------------------------
    | Update Creator Details
    |--------------------------------------------------------------------------
    */

        $creator->display_name =
        $request->display_name;

        $creator->tagline =
        $request->tagline;

        $creator->bio =
        $request->bio;

        $creator->locations =
        $request->locations;

        $creator->categories =
        $request->categories;

        $creator->languages =
        $request->languages;

        $creator->services =
        $request->services;

        $creator->platforms =
        $request->platforms;

        $creator->social_links =
        $request->social_links;

        /*
    |--------------------------------------------------------------------------
    | Instagram
    |--------------------------------------------------------------------------
    */

        $creator->instagram_url =
        $request->instagram_url;

        /*
    |--------------------------------------------------------------------------
    | Other Details
    |--------------------------------------------------------------------------
    */

        $creator->featured_reel_url =
        $request->featured_reel_url;

        $creator->average_reach =
        $request->average_reach ?? 0;

        /*
    |--------------------------------------------------------------------------
    | Visibility Settings
    |--------------------------------------------------------------------------
    */

        $creator->show_follower_count =
        $request->boolean(
            'show_follower_count'
        );

        $creator->show_average_reach =
        $request->boolean(
            'show_average_reach'
        );

        $creator->show_enquiry_cta =
        $request->boolean(
            'show_enquiry_cta'
        );

        /*
    |--------------------------------------------------------------------------
    | Publish Settings
    |--------------------------------------------------------------------------
    */

        $creator->is_published =
        $request->boolean(
            'is_published'
        );

        $creator->is_featured =
        $request->boolean(
            'is_featured'
        );

        /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

        $creator->save();

        /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

        return response()->json([

            'success' => true,

            'message' =>
            'Creator profile updated successfully.',

            'data'    =>
            $this->formatCreator($creator),

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request, $id)
    {
        $creator = Creator::where(
            'id',
            $id
        )->first();

        if (! $creator) {

            return response()->json([
                'success' => false,
                'message' => 'Creator profile not found.',
            ], 404);
        }

        /*
        | Delete profile photo
        */

        if (
            $creator->profile_photo &&
            Storage::disk('public')
            ->exists($creator->profile_photo)
        ) {

            Storage::disk('public')
                ->delete($creator->profile_photo);
        }

        /*
        | Delete portfolio
        */

        if ($creator->portfolio_images) {

            foreach (
                $creator->portfolio_images as $image
            ) {

                if (
                    Storage::disk('public')
                    ->exists($image)
                ) {

                    Storage::disk('public')
                        ->delete($image);
                }
            }
        }

        $creator->delete();

        return response()->json([
            'success' => true,
            'message' => 'Creator profile deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE PUBLISH
    |--------------------------------------------------------------------------
    */

    public function togglePublish(Request $request, $id)
    {
        $creator = Creator::where(
            'id',
            $id
        )->first();

        if (! $creator) {

            return response()->json([
                'success' => false,
                'message' => 'Creator profile not found.',
            ], 404);
        }

        $creator->is_published =
        ! $creator->is_published;

        $creator->save();

        return response()->json([
            'success' => true,
            'message' => $creator->is_published
                ? 'Creator published successfully.'
                : 'Creator unpublished successfully.',
            'data'    => [
                'id'           => $creator->id,
                'is_published' => $creator->is_published,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE FEATURED
    |--------------------------------------------------------------------------
    */

    public function toggleFeatured(Request $request, $id)
    {
        $creator = Creator::where(
            'id',
            $id
        )->first();

        if (! $creator) {

            return response()->json([
                'success' => false,
                'message' => 'Creator profile not found.',
            ], 404);
        }

        $creator->is_featured =
        ! $creator->is_featured;

        $creator->save();

        return response()->json([
            'success' => true,
            'message' => $creator->is_featured
                ? 'Creator marked as featured.'
                : 'Creator removed from featured.',
            'data'    => [
                'id'          => $creator->id,
                'is_featured' => $creator->is_featured,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FORMAT RESPONSE
    |--------------------------------------------------------------------------
    */

    private function formatCreator($creator)
    {
        if ($creator->profile_photo) {

            $creator->profile_photo =
                asset(
                'storage/' .
                $creator->profile_photo
            );
        }

        if ($creator->instagram_profile_image) {

            $creator->instagram_profile_image =
                asset(
                'storage/' .
                $creator->instagram_profile_image
            );
        }

        if ($creator->portfolio_images) {

            $creator->portfolio_images =
            collect($creator->portfolio_images)
                ->map(function ($image) {

                    return asset(
                        'storage/' . $image
                    );

                })
                ->values()
                ->toArray();
        }

        return $creator;
    }
}
