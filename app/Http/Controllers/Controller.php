<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="سیستم اشتراک‌گذاری فایل",
 *     version="1.0.0",
 *     description="API برای سیستم اشتراک‌گذاری فایل با Laravel، هر کاربر 5 گیگابایت فضا دارد",
 *     @OA\Contact(
 *         email="info@example.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     description="Server محلی",
 *     url=L5_SWAGGER_CONST_HOST
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
