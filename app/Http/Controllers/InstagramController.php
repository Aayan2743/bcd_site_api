<?php
namespace App\Http\Controllers;

use App\Services\InstagramScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstagramController extends Controller
{
    public function profile(
        Request $request,
        InstagramScraperService $instagram
    ) {
        $validator = Validator::make($request->all(), [
            'instagram_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {

            $data = $instagram->scrape(
                $request->instagram_url
            );

            return response()->json([
                'success' => true,
                'message' => 'Instagram profile fetched successfully.',
                'data'    => $data,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch Instagram profile.',
                'error'   => $e->getMessage(),
            ], 422);
        }
    }
}