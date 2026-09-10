<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Create Post
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'            => 'required|string|max:255',
            'category'         => 'required|string|max:255',
            'published_date'   => 'required|date',

            'excerpt'          => 'nullable|string',
            'content'          => 'required|string',

            'cover_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'video_url'        => 'nullable|url|max:1000',

            'seo_title'        => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'slug'             => 'required|string|max:255',
            'key_phrases'      => 'nullable|string',

            'is_published'     => 'nullable|boolean',
            'is_featured'      => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->first(),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Slug
        |--------------------------------------------------------------------------
        */

        if (Post::where('slug', $request->slug)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Slug already exists.',
                'errors'  => [
                    'slug' => ['This slug is already in use.'],
                ],
            ], 422);
        }
        /*
        |--------------------------------------------------------------------------
        | Cover Image
        |--------------------------------------------------------------------------
        */

        $coverImage = null;

        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image')
                ->store('posts', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Create Post
        |--------------------------------------------------------------------------
        */

        $post = Post::create([
            'title'            => $request->title,
            'category'         => $request->category,
            'published_date'   => $request->published_date,

            'excerpt'          => $request->excerpt,
            'content'          => $request->content,

            'cover_image'      => $coverImage,
            'video_url'        => $request->video_url,

            'seo_title'        => $request->seo_title,
            'meta_description' => $request->meta_description,
            'slug'             => $request->slug,
            'key_phrases'      => $request->key_phrases,

            'is_published'     => $request->boolean('is_published'),
            'is_featured'      => $request->boolean('is_featured'),

            'created_by'       => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully.',
            'data'    => $post,
        ], 201);
    }

    /**
     * Get All Posts
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $posts = Post::with('creator')
            ->latest()
            ->paginate($perPage);

        $posts->getCollection()->transform(function ($post) {

            $post->cover_image = $post->cover_image
                ? asset('storage/' . $post->cover_image)
                : null;

            return $post;
        });

        return response()->json([
            'success' => true,
            'message' => 'Posts fetched successfully.',
            'data'    => $posts,
        ]);
    }

    /**
     * Get Single Post
     */
    public function show($id)
    {
        $post = Post::with('creator')->find($id);

        if (! $post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        $post->cover_image_url = $post->cover_image
            ? asset('storage/' . $post->cover_image)
            : null;

        return response()->json([
            'success' => true,
            'data'    => $post,
        ]);
    }

    /**
     * Update Post
     */
    public function update(Request $request, $id)
    {
        /*
    |--------------------------------------------------------------------------
    | Find Post
    |--------------------------------------------------------------------------
    */

        $post = Post::find($id);

        if (! $post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

        $validator = Validator::make($request->all(), [
            'title'            => 'required|string|max:255',
            'category'         => 'required|string|max:255',
            'published_date'   => 'required|date',

            'excerpt'          => 'nullable|string',
            'content'          => 'required|string',

            'cover_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'video_url'        => 'nullable|url|max:1000',

            'seo_title'        => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',

            'slug'             => 'required|string|max:255',

            'key_phrases'      => 'nullable|string',

            'is_published'     => 'nullable|boolean',
            'is_featured'      => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->first(),
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Check Slug
    |--------------------------------------------------------------------------
    | Allow current post's slug.
    | Don't allow another post to use it.
    */

        if (
            Post::where('slug', $request->slug)
            ->where('id', '!=', $post->id)
            ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Slug already exists.',
                'errors'  => [
                    'slug' => ['This slug is already in use.'],
                ],
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Cover Image
    |--------------------------------------------------------------------------
    */

        if ($request->hasFile('cover_image')) {

            // Delete old image
            if (
                $post->cover_image &&
                Storage::disk('public')->exists($post->cover_image)
            ) {
                Storage::disk('public')->delete($post->cover_image);
            }

            // Store new image
            $post->cover_image = $request->file('cover_image')
                ->store('posts', 'public');
        }

        /*
    |--------------------------------------------------------------------------
    | Update Post
    |--------------------------------------------------------------------------
    */

        $post->title          = $request->title;
        $post->category       = $request->category;
        $post->published_date = $request->published_date;

        $post->excerpt = $request->excerpt;
        $post->content = $request->content;

        $post->video_url = $request->video_url;

        $post->seo_title        = $request->seo_title;
        $post->meta_description = $request->meta_description;

        // Use slug exactly from frontend
        $post->slug = $request->slug;

        $post->key_phrases = $request->key_phrases;

        $post->is_published = $request->boolean('is_published');
        $post->is_featured  = $request->boolean('is_featured');

        $post->save();

        /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully.',
            'data'    => $post,
        ], 200);
    }

    /**
     * Delete Post
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (! $post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        if (
            $post->cover_image &&
            Storage::disk('public')->exists($post->cover_image)
        ) {
            Storage::disk('public')->delete($post->cover_image);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully.',
        ]);
    }

    /**
     * Toggle Publish Status
     */

    public function togglePublish($id)
    {
        $post = Post::find($id);

        if (! $post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        $post->is_published = ! $post->is_published;
        $post->save();

        return response()->json([
            'success' => true,
            'message' => $post->is_published
                ? 'Post published successfully.'
                : 'Post unpublished successfully.',
            'data'    => [
                'id'           => $post->id,
                'is_published' => $post->is_published,
            ],
        ], 200);
    }

    /**
     * Toggle Featured Status
     */

    public function toggleFeatured($id)
    {
        $post = Post::find($id);

        if (! $post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        $post->is_featured = ! $post->is_featured;
        $post->save();

        return response()->json([
            'success' => true,
            'message' => $post->is_featured
                ? 'Post marked as featured successfully.'
                : 'Post removed from featured successfully.',
            'data'    => [
                'id'          => $post->id,
                'is_featured' => $post->is_featured,
            ],
        ], 200);
    }
}
