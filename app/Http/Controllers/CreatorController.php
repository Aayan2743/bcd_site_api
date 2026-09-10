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
        $validator = Validator::make($request->all(), [

            'display_name' => 'required|string|max:60',
            'tagline' => 'nullable|string|max:80',
            'bio' => 'nullable|string|max:600',
            'location' => 'nullable|string|max:255',

            'categories' => 'nullable|array',
            'categories.*' => 'string|max:100',

            'languages' => 'nullable|array',
            'languages.*' => 'string|max:100',

            'services' => 'nullable|string',

            'platforms' => 'nullable|array',
            'platforms.*' => 'string|max:100',

            'social_links' => 'nullable|array',

            'social_links.instagram' => 'nullable|url',
            'social_links.youtube' => 'nullable|url',
            'social_links.facebook' => 'nullable|url',
            'social_links.tiktok' => 'nullable|url',
            'social_links.pinterest' => 'nullable|url',

            'instagram_url' => 'nullable|url',

            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096'
            ],

            'portfolio_images' => 'nullable|array|max:10',

            'portfolio_images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096'
            ],

            'featured_reel_url' => 'nullable|url|max:1000',

            'follower_count' => 'nullable|integer|min:0',
            'average_reach' => 'nullable|integer|min:0',

            'show_follower_count' => 'nullable|boolean',
            'show_average_reach' => 'nullable|boolean',
            'show_enquiry_cta' => 'nullable|boolean',

            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->first(),
            ], 422);
        }

        $user = $request->user();

        $existingCreator = Creator::where(
            'user_id',
            $user->id
        )->first();

        if ($existingCreator) {

            return response()->json([
                'success' => false,
                'message' => 'Creator profile already exists.',
            ], 409);
        }

        /*
        |--------------------------------------------------------------------------
        | Profile photo
        |--------------------------------------------------------------------------
        */

        $profilePhoto = null;

        if ($request->hasFile('profile_photo')) {

            $profilePhoto = $request->file('profile_photo')
                ->store('creators/profile', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Portfolio images
        |--------------------------------------------------------------------------
        */

        $portfolioImages = [];

        if ($request->hasFile('portfolio_images')) {

            foreach ($request->file('portfolio_images') as $image) {

                $portfolioImages[] = $image
                    ->store('creators/portfolio', 'public');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        $creator = Creator::create([

            'user_id' => $user->id,

            'display_name' => $request->display_name,
            'tagline' => $request->tagline,
            'bio' => $request->bio,
            'location' => $request->location,

            'categories' => $request->categories,
            'languages' => $request->languages,

            'services' => $request->services,

            'platforms' => $request->platforms,
            'social_links' => $request->social_links,

            'instagram_url' => $request->instagram_url,

            'profile_photo' => $profilePhoto,
            'portfolio_images' => $portfolioImages,

            'featured_reel_url' => $request->featured_reel_url,

            'follower_count' => $request->follower_count ?? 0,
            'average_reach' => $request->average_reach ?? 0,

            'show_follower_count' =>
                $request->boolean('show_follower_count'),

            'show_average_reach' =>
                $request->boolean('show_average_reach'),

            'show_enquiry_cta' =>
                $request->boolean('show_enquiry_cta'),

            'is_published' =>
                $request->boolean('is_published'),

            'is_featured' =>
                $request->boolean('is_featured'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Creator profile created successfully.',
            'data' => $this->formatCreator($creator),
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | MY PROFILE
    |--------------------------------------------------------------------------
    */

    public function profile(Request $request)
    {
        $creator = Creator::where(
            'user_id',
            $request->user()->id
        )->first();

        if (!$creator) {

            return response()->json([
                'success' => false,
                'message' => 'Creator profile not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatCreator($creator),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $creator = Creator::where(
            'user_id',
            $request->user()->id
        )->first();

        if (!$creator) {

            return response()->json([
                'success' => false,
                'message' => 'Creator profile not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [

            'display_name' => 'required|string|max:60',
            'tagline' => 'nullable|string|max:80',
            'bio' => 'nullable|string|max:600',
            'location' => 'nullable|string|max:255',

            'categories' => 'nullable|array',
            'languages' => 'nullable|array',

            'services' => 'nullable|string',

            'platforms' => 'nullable|array',

            'social_links' => 'nullable|array',

            'social_links.instagram' => 'nullable|url',
            'social_links.youtube' => 'nullable|url',
            'social_links.facebook' => 'nullable|url',
            'social_links.tiktok' => 'nullable|url',
            'social_links.pinterest' => 'nullable|url',

            'instagram_url' => 'nullable|url',

            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096'
            ],

            'portfolio_images' => 'nullable|array|max:10',

            'portfolio_images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096'
            ],

            'featured_reel_url' => 'nullable|url|max:1000',

            'follower_count' => 'nullable|integer|min:0',
            'average_reach' => 'nullable|integer|min:0',

            'show_follower_count' => 'nullable|boolean',
            'show_average_reach' => 'nullable|boolean',
            'show_enquiry_cta' => 'nullable|boolean',

            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->first(),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Profile photo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('profile_photo')) {

            if (
                $creator->profile_photo &&
                Storage::disk('public')
                    ->exists($creator->profile_photo)
            ) {
                Storage::disk('public')
                    ->delete($creator->profile_photo);
            }

            $creator->profile_photo =
                $request->file('profile_photo')
                    ->store('creators/profile', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Portfolio
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('portfolio_images')) {

            if ($creator->portfolio_images) {

                foreach (
                    $creator->portfolio_images as $oldImage
                ) {

                    if (
                        Storage::disk('public')
                            ->exists($oldImage)
                    ) {
                        Storage::disk('public')
                            ->delete($oldImage);
                    }
                }
            }

            $portfolioImages = [];

            foreach (
                $request->file('portfolio_images')
                as $image
            ) {

                $portfolioImages[] =
                    $image->store(
                        'creators/portfolio',
                        'public'
                    );
            }

            $creator->portfolio_images = $portfolioImages;
        }

        /*
        |--------------------------------------------------------------------------
        | Update fields
        |--------------------------------------------------------------------------
        */

        $creator->display_name = $request->display_name;
        $creator->tagline = $request->tagline;
        $creator->bio = $request->bio;
        $creator->location = $request->location;

        $creator->categories = $request->categories;
        $creator->languages = $request->languages;

        $creator->services = $request->services;

        $creator->platforms = $request->platforms;
        $creator->social_links = $request->social_links;

        $creator->instagram_url =
            $request->instagram_url;

        $creator->featured_reel_url =
            $request->featured_reel_url;

        $creator->follower_count =
            $request->follower_count ?? 0;

        $creator->average_reach =
            $request->average_reach ?? 0;

        $creator->show_follower_count =
            $request->boolean('show_follower_count');

        $creator->show_average_reach =
            $request->boolean('show_average_reach');

        $creator->show_enquiry_cta =
            $request->boolean('show_enquiry_cta');

        $creator->is_published =
            $request->boolean('is_published');

        $creator->is_featured =
            $request->boolean('is_featured');

        $creator->save();

        return response()->json([
            'success' => true,
            'message' => 'Creator profile updated successfully.',
            'data' => $this->formatCreator($creator),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request)
    {
        $creator = Creator::where(
            'user_id',
            $request->user()->id
        )->first();

        if (!$creator) {

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

    public function togglePublish(Request $request)
    {
        $creator = Creator::where(
            'user_id',
            $request->user()->id
        )->first();

        if (!$creator) {

            return response()->json([
                'success' => false,
                'message' => 'Creator profile not found.',
            ], 404);
        }

        $creator->is_published =
            !$creator->is_published;

        $creator->save();

        return response()->json([
            'success' => true,
            'message' => $creator->is_published
                ? 'Creator published successfully.'
                : 'Creator unpublished successfully.',
            'data' => [
                'id' => $creator->id,
                'is_published' => $creator->is_published,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | TOGGLE FEATURED
    |--------------------------------------------------------------------------
    */

    public function toggleFeatured(Request $request)
    {
        $creator = Creator::where(
            'user_id',
            $request->user()->id
        )->first();

        if (!$creator) {

            return response()->json([
                'success' => false,
                'message' => 'Creator profile not found.',
            ], 404);
        }

        $creator->is_featured =
            !$creator->is_featured;

        $creator->save();

        return response()->json([
            'success' => true,
            'message' => $creator->is_featured
                ? 'Creator marked as featured.'
                : 'Creator removed from featured.',
            'data' => [
                'id' => $creator->id,
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