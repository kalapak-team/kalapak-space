<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\SupabaseStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Media::with('uploader')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('original_name', 'ilike', "%{$search}%");
        }

        if ($request->filled('type')) {
            $type = $request->input('type');
            if ($type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($type === 'document') {
                $query->where('mime_type', 'not like', 'image/%');
            }
        }

        $media = $query->paginate($request->input('per_page', 24));

        $items = collect($media->items())->map(function ($item) {
            $data = $item->toArray();
            $data['url'] = app(SupabaseStorage::class)->url($item->path);
            if ($item->uploader) {
                $data['uploader'] = [
                    'id' => $item->uploader->id,
                    'name' => $item->uploader->name,
                ];
            }
            return $data;
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
            ],
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200', 'mimes:jpg,jpeg,png,webp,gif,pdf,mp4,mov,avi,webm'],
        ]);

        try {
            $file = $request->file('file');
            $storage = app(SupabaseStorage::class);
            $path = $storage->upload($file, 'media');

            $media = Media::create([
                'filename' => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => 'supabase',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);

            $responseData = $media->toArray();
            $responseData['url'] = $storage->url($path);

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'File uploaded successfully.',
            ], 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Media upload failed', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Storage error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $media = Media::findOrFail($id);
        app(SupabaseStorage::class)->delete($media->path);
        $media->delete();

        return response()->json([
            'success' => true,
            'message' => 'Media deleted successfully.',
        ]);
    }
}
