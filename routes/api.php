<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PoemController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\CommentController;
use App\Models\Source;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

 Route::post('/register',[UserController::class,'register']);
 Route::post('/login',[UserController::class,'login']);

#القصائد.................................................................................القصائد

 Route::post('/AddPoem',[PoemController::class,'store'])->middleware('auth:sanctum');
//   Route::post('/poems/{poem_id}', [PoemController::class, 'update'])->middleware('auth:sanctum');
 Route::post('/AddSourcePoem/{poem_id}',[PoemController::class,'AddSourcePoem'])->middleware('auth:sanctum');
 Route::post('/FavoritePoem/{poem_id}', [PoemController::class, 'toggleFavorite'])->middleware('auth:sanctum');
 Route::get('/poems/getall', [PoemController::class, 'index'])->middleware('auth:sanctum');
 Route::delete('/deletePoem/{id}',[PoemController::class,'destroy'])->middleware('auth:sanctum');
 Route::post('AddComment{poem_id}',[CommentController::class,'store'])->middleware('auth:sanctum');

Route::get('/poems/{poem_id}', [App\Http\Controllers\PoemController::class, 'show']); #عرض تفاصيل قصيدة
 #comments

 // عرض التعليقات على قصيدة معينة (يمكن أن يكون مفتوحًا للجميع)
Route::get('poems/{poem_id}/comments', [CommentController::class, 'index']);

// إنشاء تعليق (يجب أن يكون محميًا بـ auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('poems/{poem_id}/comments', [CommentController::class, 'store']);
    
    // يمكنك إضافة مسار حذف التعليق هنا (delete)


});
