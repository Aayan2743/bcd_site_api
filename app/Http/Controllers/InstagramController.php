<?php
namespace App\Http\Controllers;

// use App\Services\InstagramScraperService;
use App\Services\InstagramScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstagramController extends Controller
{

    public function scrapeInstagram(Request $request, InstagramScraperService $instagramScraper)
    {
        $validator = Validator::make($request->all(), [
            'instagram_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $data = $instagramScraper->scrape(
                $request->instagram_url
            );

            return response()->json([
                'success' => true,
                'message' => 'Instagram profile scraped successfully.',
                'data'    => $data,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
