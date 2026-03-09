<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Handle image upload without access token.
     *
     * @param UploadRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(UploadRequest $request)
    {
        try {
            // Validate file type and size
            $validator = Validator::make($request->all(), [
                'file' => 'image|mimes:jpeg,png,jpg|max:2048' // 2MB Max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Picha inatakiwa kuwa aina ya jpeg, png, jpg na size isizidi 2mb',
                    'errors' => $validator->errors()
                ], 400);
            }

            $image = $request->file('file');
            $new_name = Str::random(10) . '_' . time() . '.' . $image->getClientOriginalExtension();
            $disk = config('filesystems.default'); // FILESYSTEM_DISK from .env

            if ($disk === 's3') {
                /**
                 * Upload to S3
                 */
                $path = Storage::disk('s3')->putFileAs('uploads/mikopo/images', $image, $new_name);

                // Safe URL generation
                if (env('AWS_URL')) {
                    $url = rtrim(env('AWS_URL'), '/') . '/' . ltrim($path, '/');
                } else {
                    $url = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . $path;
                }
            } else {
                /**
                 * Fallback to local storage
                 */
                $imagePath = public_path('uploads/mikopo/images/');
                if (!is_dir($imagePath)) {
                    mkdir($imagePath, 0755, true);
                }
                $image->move($imagePath, $new_name);
                $url = url('uploads/mikopo/images/' . $new_name);
            }

            return response()->json([
                'message' => $url,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
