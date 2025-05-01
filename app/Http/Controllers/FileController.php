<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
    /**
     * Display the user's dashboard with files and storage info.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $files = $user->files()->latest()->get();
        $sharedFiles = $user->sharedFiles()->latest()->get();

        $totalSpace = 5 * 1024 * 1024 * 1024;
        $usedSpace = $user->total_storage_used;
        $freeSpace = $totalSpace - $usedSpace;

        $percentUsed = ($usedSpace / $totalSpace) * 100;

        return view('dashboard', compact('files', 'sharedFiles', 'totalSpace', 'usedSpace', 'freeSpace', 'percentUsed'));
    }

    /**
     * Upload a file.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:1048576',
        ]);

        $user = Auth::user();
        $uploadedFile = $request->file('file');
        $fileSize = $uploadedFile->getSize();

        if (!$user->hasEnoughStorage($fileSize)) {
            throw ValidationException::withMessages([
                'file' => 'You do not have enough storage space left. Please free up some space by deleting other files.'
            ]);
        }

        $filename = $uploadedFile->getClientOriginalName();
        $path = $uploadedFile->store('files/' . $user->id);

        $file = new File([
            'user_id' => $user->id,
            'name' => $filename,
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $fileSize,
        ]);

        $file->save();

        return redirect()->route('dashboard')->with('success', 'File uploaded successfully.');
    }

    /**
     * Download a file.
     */
    public function download(File $file)
    {
        $user = Auth::user();

        if ($file->user_id !== $user->id && !$file->sharedWithUsers()->where('user_id', $user->id)->exists()) {
            abort(403, 'Unauthorized');
        }

        if (!Storage::exists($file->path)) {
            abort(404, 'File not found');
        }

        return Storage::download($file->path, $file->name);
    }

    /**
     * Delete a file.
     */
    public function delete(File $file)
    {
        $user = Auth::user();

        if ($file->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        $file->delete();

        return redirect()->route('dashboard')->with('success', 'File deleted successfully.');
    }

    /**
     * Show share file form.
     */
    public function showShareForm(File $file)
    {
        $user = Auth::user();

        if ($file->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $sharedWithUsers = $file->sharedWithUsers;

        return view('files.share', compact('file', 'sharedWithUsers'));
    }

    /**
     * Share a file with another user.
     */
    public function share(Request $request, File $file)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = Auth::user();

        // Check if the user owns the file
        if ($file->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $shareWithUser = User::where('email', $request->email)->first();

        // Don't share with yourself
        if ($shareWithUser->id === $user->id) {
            throw ValidationException::withMessages([
                'email' => 'You cannot share a file with yourself.'
            ]);
        }

        // Check if the file is already shared with this user
        if ($file->sharedWithUsers()->where('user_id', $shareWithUser->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This file is already shared with this user.'
            ]);
        }

        // Create the file share
        FileShare::create([
            'file_id' => $file->id,
            'user_id' => $shareWithUser->id,
        ]);

        return redirect()->route('files.share', $file)->with('success', 'File shared successfully.');
    }

    /**
     * Remove file share.
     */
    public function removeShare(File $file, User $user)
    {
        $currentUser = Auth::user();

        // Check if the current user owns the file
        if ($file->user_id !== $currentUser->id) {
            abort(403, 'Unauthorized');
        }

        // Delete the file share
        FileShare::where('file_id', $file->id)
                 ->where('user_id', $user->id)
                 ->delete();

        return redirect()->route('files.share', $file)->with('success', 'File access removed.');
    }

    /**
     * API: Get list of user files.
     *
     * @OA\Get(
     *     path="/api/files",
     *     summary="دریافت لیست فایل‌های کاربر",
     *     tags={"Files"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="لیست فایل‌های کاربر",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="files",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="document.pdf"),
     *                     @OA\Property(property="mime_type", type="string", example="application/pdf"),
     *                     @OA\Property(property="size", type="string", example="2.5 MB"),
     *                     @OA\Property(property="uploaded_at", type="string", example="2 hours ago"),
     *                     @OA\Property(property="shared_with_count", type="integer", example=3)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function apiGetFiles()
    {
        $user = Auth::user();
        $files = $user->files()->latest()->get();

        return response()->json([
            'success' => true,
            'files' => $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'mime_type' => $file->mime_type,
                    'size' => $file->formatted_size,
                    'uploaded_at' => $file->created_at->diffForHumans(),
                    'shared_with_count' => $file->sharedWithUsers()->count(),
                ];
            }),
        ]);
    }

    /**
     * API: Get list of files shared with the user.
     *
     * @OA\Get(
     *     path="/api/files/shared",
     *     summary="دریافت لیست فایل‌های به اشتراک گذاشته شده با کاربر",
     *     tags={"Files"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="لیست فایل‌های به اشتراک گذاشته شده با کاربر",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="files",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="document.pdf"),
     *                     @OA\Property(property="mime_type", type="string", example="application/pdf"),
     *                     @OA\Property(property="size", type="string", example="2.5 MB"),
     *                     @OA\Property(property="owner", type="string", example="John Doe"),
     *                     @OA\Property(property="shared_at", type="string", example="5 days ago")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function apiGetSharedFiles()
    {
        $user = Auth::user();
        $sharedFiles = $user->sharedFiles()->with('user')->latest()->get();

        return response()->json([
            'success' => true,
            'files' => $sharedFiles->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'mime_type' => $file->mime_type,
                    'size' => $file->formatted_size,
                    'owner' => $file->user->name,
                    'shared_at' => $file->pivot->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    /**
     * API: Get list of users a file is shared with.
     *
     * @OA\Get(
     *     path="/api/files/{file}/shares",
     *     summary="دریافت لیست کاربرانی که فایل با آنها به اشتراک گذاشته شده",
     *     tags={"Files"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         required=true,
     *         description="آیدی فایل",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="لیست کاربران",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="shares",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="shared_at", type="string", example="2 days ago")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function apiGetFileShares(File $file)
    {
        $user = Auth::user();

        // Check if the user owns the file
        if ($file->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $shares = $file->sharedWithUsers()->get();

        return response()->json([
            'success' => true,
            'shares' => $shares->map(function ($user) use ($file) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'shared_at' => $user->pivot->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    /**
     * API: Upload a file
     * 
     * @OA\Post(
     *     path="/api/files/upload",
     *     summary="آپلود فایل جدید",
     *     tags={"Files"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="فایل برای آپلود (حداکثر 1GB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="فایل با موفقیت آپلود شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="file", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="document.pdf"),
     *                 @OA\Property(property="mime_type", type="string", example="application/pdf"),
     *                 @OA\Property(property="size", type="string", example="2.5 MB")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="اطلاعات نامعتبر یا فضای ناکافی",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The file field is required."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="file", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function apiUploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:1048576', // 1GB max file size for upload
        ]);
        
        $user = Auth::user();
        $uploadedFile = $request->file('file');
        $fileSize = $uploadedFile->getSize();
        
        // Check if user has enough storage space
        if (!$user->hasEnoughStorage($fileSize)) {
            return response()->json([
                'success' => false,
                'message' => 'فضای ذخیره‌سازی ناکافی است. لطفاً برخی از فایل‌های قدیمی را حذف کنید.'
            ], 422);
        }
        
        $filename = $uploadedFile->getClientOriginalName();
        $path = $uploadedFile->store('files/' . $user->id);
        
        $file = new File([
            'user_id' => $user->id,
            'name' => $filename,
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $fileSize,
        ]);
        
        $file->save();
        
        return response()->json([
            'success' => true,
            'file' => [
                'id' => $file->id,
                'name' => $file->name,
                'mime_type' => $file->mime_type,
                'size' => $file->formatted_size
            ]
        ]);
    }
    
    /**
     * API: Delete a file
     * 
     * @OA\Delete(
     *     path="/api/files/{file}",
     *     summary="حذف فایل",
     *     tags={"Files"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         required=true,
     *         description="آیدی فایل",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="فایل با موفقیت حذف شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="فایل یافت نشد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="File not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function apiDeleteFile(File $file)
    {
        $user = Auth::user();
        
        // Check if the user owns the file
        if ($file->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Delete the file from storage
        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }
        
        // Delete the file record
        $file->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'فایل با موفقیت حذف شد'
        ]);
    }
    
    /**
     * API: Share a file with another user
     * 
     * @OA\Post(
     *     path="/api/files/{file}/share",
     *     summary="اشتراک‌گذاری فایل با کاربر دیگر",
     *     tags={"Files"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         required=true,
     *         description="آیدی فایل",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="فایل با موفقیت به اشتراک گذاشته شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File shared successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="اطلاعات نامعتبر یا خطای اشتراک‌گذاری",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Email not found or file already shared")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function apiShareFile(Request $request, File $file)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        
        $user = Auth::user();
        
        // Check if the user owns the file
        if ($file->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $shareWithUser = User::where('email', $request->email)->first();
        
        // Don't share with yourself
        if ($shareWithUser->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'نمی‌توانید فایل را با خودتان به اشتراک بگذارید'
            ], 422);
        }
        
        // Check if the file is already shared with this user
        if ($file->sharedWithUsers()->where('user_id', $shareWithUser->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'این فایل قبلاً با این کاربر به اشتراک گذاشته شده است'
            ], 422);
        }
        
        // Create the file share
        FileShare::create([
            'file_id' => $file->id,
            'user_id' => $shareWithUser->id,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'فایل با موفقیت به اشتراک گذاشته شد'
        ]);
    }
    
    /**
     * API: Remove file share
     * 
     * @OA\Delete(
     *     path="/api/files/{file}/share/{user}",
     *     summary="حذف دسترسی کاربر به فایل",
     *     tags={"Files"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         required=true,
     *         description="آیدی فایل",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="آیدی کاربر",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="دسترسی با موفقیت حذف شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File access removed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="فایل یا کاربر یافت نشد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="File or user not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function apiRemoveShare(File $file, User $user)
    {
        $currentUser = Auth::user();
        
        // Check if the current user owns the file
        if ($file->user_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Check if the file is shared with the specified user
        if (!$file->sharedWithUsers()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'این فایل با این کاربر به اشتراک گذاشته نشده است'
            ], 404);
        }
        
        // Delete the file share
        FileShare::where('file_id', $file->id)
                ->where('user_id', $user->id)
                ->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'دسترسی با موفقیت حذف شد'
        ]);
    }
}
