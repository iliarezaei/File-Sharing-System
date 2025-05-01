<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * ثبت نام کاربر و دریافت توکن
     * 
     * @OA\Post(
     *     path="/api/register",
     *     summary="ثبت نام کاربر جدید",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="محمد حسین"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="کاربر با موفقیت ایجاد شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="محمد حسین"),
     *                 @OA\Property(property="email", type="string", example="user@example.com")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|laravel_sanctum_gUcULfpdQgHr8NPnS8uNJ7v8u3XO5fUJEssT4tCR")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="اطلاعات ارسالی نامعتبر است"
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * ورود کاربر و دریافت توکن
     * 
     * @OA\Post(
     *     path="/api/login",
     *     summary="ورود کاربر و دریافت توکن",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ورود موفق",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="محمد حسین"),
     *                 @OA\Property(property="email", type="string", example="user@example.com")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|laravel_sanctum_gUcULfpdQgHr8NPnS8uNJ7v8u3XO5fUJEssT4tCR")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="اطلاعات ورود نامعتبر است",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'ایمیل یا رمز عبور اشتباه است'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * خروج کاربر و حذف توکن
     * 
     * @OA\Post(
     *     path="/api/logout",
     *     summary="خروج کاربر و حذف توکن",
     *     tags={"Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="خروج موفق",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'با موفقیت خارج شدید'
        ]);
    }

    /**
     * دریافت اطلاعات کاربر فعلی
     * 
     * @OA\Get(
     *     path="/api/user",
     *     summary="دریافت اطلاعات کاربر فعلی",
     *     tags={"Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="اطلاعات کاربر",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="محمد حسین"),
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="total_storage_used", type="integer", example=104857600),
     *             @OA\Property(property="storage_limit", type="integer", example=5368709120),
     *             @OA\Property(property="storage_percentage", type="number", format="float", example=1.95)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function user(Request $request)
    {
        $user = $request->user();
        $totalStorageLimit = 5 * 1024 * 1024 * 1024; // 5GB in bytes
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'total_storage_used' => $user->total_storage_used,
            'storage_limit' => $totalStorageLimit,
            'storage_percentage' => ($user->total_storage_used / $totalStorageLimit) * 100
        ]);
    }
} 