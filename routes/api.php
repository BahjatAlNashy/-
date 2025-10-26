<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PoemController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SayingController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\NewsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ============================================================================
// المستخدمين (Authentication & Profile)
// ============================================================================

// التسجيل وتسجيل الدخول (بدون middleware)
Route::post('/register', [UserController::class, 'register']); // إنشاء حساب جديد
Route::post('/login', [UserController::class, 'login']); // تسجيل الدخول

// العمليات المحمية (تحتاج token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']); // تسجيل الخروج
    Route::get('/profile', [UserController::class, 'profile']); // عرض معلومات المستخدم
    Route::put('/profile', [UserController::class, 'updateProfile']); // تحديث معلومات المستخدم
});

// ============================================================================
// القصائد (Poems)
// ============================================================================

// --- CRUD Operations ---
Route::post('/AddPoem', [PoemController::class, 'store'])->middleware('auth:sanctum');
Route::get('/poems/getall', [PoemController::class, 'index']);
Route::get('/poems/search', [PoemController::class, 'search']); // ⚠️ يجب أن يكون قبل {poem_id}
Route::get('/poems/favorites', [PoemController::class, 'getFavoritePoems'])->middleware('auth:sanctum'); // ⚠️ يجب أن يكون قبل {poem_id}
Route::get('/poems/{poem_id}', [PoemController::class, 'show']);
Route::post('/poems/{poem_id}/update', [PoemController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/deletePoem/{id}', [PoemController::class, 'destroy'])->middleware('auth:sanctum');

// --- Sources Management ---
Route::post('/AddSourcePoem/{poem_id}', [PoemController::class, 'AddSourcePoem'])->middleware('auth:sanctum');
Route::delete('/deleteSource/{source_id}', [PoemController::class, 'deleteSource'])->middleware('auth:sanctum');

// --- Favorites ---
Route::post('/FavoritePoem/{poem_id}', [PoemController::class, 'toggleFavorite'])->middleware('auth:sanctum');

// --- Comments ---
Route::get('/poems/{poem_id}/comments', [CommentController::class, 'index']);
Route::post('/poems/{poem_id}/comments', [CommentController::class, 'store'])->middleware('auth:sanctum');
Route::put('/poems/comments/{comment_id}', [CommentController::class, 'updatePoemComment'])->middleware('auth:sanctum');
Route::delete('/poems/comments/{comment_id}', [CommentController::class, 'destroyPoemComment'])->middleware('auth:sanctum');

// ============================================================================
// الدروس (Lessons)
// ============================================================================

// --- CRUD Operations ---
Route::post('/AddLesson', [LessonController::class, 'store'])->middleware('auth:sanctum');
Route::get('/lessons/getall', [LessonController::class, 'index']);
Route::get('/lessons/search', [LessonController::class, 'search']); // ⚠️ يجب أن يكون قبل {lesson_id}
Route::get('/lessons/favorites', [LessonController::class, 'getFavoriteLessons'])->middleware('auth:sanctum'); // ⚠️ يجب أن يكون قبل {lesson_id}
Route::get('/lessons/{lesson_id}', [LessonController::class, 'show']);
Route::post('/lessons/{lesson_id}/update', [LessonController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/deleteLesson/{id}', [LessonController::class, 'destroy'])->middleware('auth:sanctum');

// --- Sources Management ---
Route::post('/AddSourceLesson/{lesson_id}', [LessonController::class, 'AddSourceLesson'])->middleware('auth:sanctum');
Route::delete('/deleteLessonSource/{source_id}', [LessonController::class, 'deleteSource'])->middleware('auth:sanctum');

// --- Favorites ---
Route::post('/FavoriteLesson/{lesson_id}', [LessonController::class, 'toggleFavorite'])->middleware('auth:sanctum');

// --- Comments ---
Route::get('/lessons/{lesson_id}/comments', [CommentController::class, 'indexLessonComments']);
Route::post('/lessons/{lesson_id}/comments', [CommentController::class, 'storeLessonComment'])->middleware('auth:sanctum');
Route::put('/lessons/comments/{comment_id}', [CommentController::class, 'updateLessonComment'])->middleware('auth:sanctum');
Route::delete('/lessons/comments/{comment_id}', [CommentController::class, 'destroyLessonComment'])->middleware('auth:sanctum');

// ============================================================================
// الأقوال (Sayings - أقوال مأثورة وأوراد/أذكار)
// ============================================================================

// --- CRUD Operations ---
Route::post('/AddSaying', [SayingController::class, 'store'])->middleware('auth:sanctum');
Route::get('/sayings/getall', [SayingController::class, 'index']); // ?type=saying أو ?type=supplication

// --- Search & Favorites (يجب أن تكون قبل {saying_id}) ---
Route::get('/sayings/search', [SayingController::class, 'search']); // يدعم type, keyword
Route::get('/sayings/favorites', [SayingController::class, 'getFavoriteSayings'])->middleware('auth:sanctum');

// --- Single Item Operations (بعد search و favorites) ---
Route::get('/sayings/{saying_id}', [SayingController::class, 'show']);
Route::post('/sayings/{saying_id}/update', [SayingController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/deleteSaying/{id}', [SayingController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/FavoriteSaying/{saying_id}', [SayingController::class, 'toggleFavorite'])->middleware('auth:sanctum');

// --- Comments ---
Route::get('/sayings/{saying_id}/comments', [CommentController::class, 'indexSayingComments']);
Route::post('/sayings/{saying_id}/comments', [CommentController::class, 'storeSayingComment'])->middleware('auth:sanctum');
Route::put('/sayings/comments/{comment_id}', [CommentController::class, 'updateSayingComment'])->middleware('auth:sanctum');
Route::delete('/sayings/comments/{comment_id}', [CommentController::class, 'destroySayingComment'])->middleware('auth:sanctum');

// ============================================================================
// الأنشطة (Activities - فيديوهات الأنشطة)
// ============================================================================

// --- CRUD Operations (Admin Only) ---
Route::post('/AddActivity', [ActivityController::class, 'store'])->middleware('auth:sanctum'); // إضافة نشاط (Admin)
Route::get('/activities/getall', [ActivityController::class, 'index']); // عرض جميع الأنشطة
Route::get('/activities/search', [ActivityController::class, 'search']); // بحث (قبل {id})
Route::get('/activities/favorites', [ActivityController::class, 'getFavoriteActivities'])->middleware('auth:sanctum'); // المفضلة (قبل {id})
Route::get('/activities/{activity_id}', [ActivityController::class, 'show']); // عرض تفاصيل نشاط
Route::post('/activities/{activity_id}/update', [ActivityController::class, 'update'])->middleware('auth:sanctum'); // تحديث (Admin)
Route::delete('/deleteActivity/{activity_id}', [ActivityController::class, 'destroy'])->middleware('auth:sanctum'); // حذف (Admin)

// --- Favorites ---
Route::post('/FavoriteActivity/{activity_id}', [ActivityController::class, 'toggleFavorite'])->middleware('auth:sanctum'); // إضافة/إزالة مفضلة

// ============================================================================
// مشاركات الزوار (Posts - مشاركات المستخدمين)
// ============================================================================

// --- CRUD Operations ---
Route::get('/posts', [PostController::class, 'index']); // عرض الموافق عليها
Route::get('/posts/search', [PostController::class, 'search']); // بحث (قبل {id})
Route::get('/posts/my-posts', [PostController::class, 'myPosts'])->middleware('auth:sanctum'); // مشاركاتي
Route::get('/posts/pending', [PostController::class, 'pendingPosts'])->middleware('auth:sanctum'); // في انتظار الموافقة (Admin)
Route::get('/posts/{id}', [PostController::class, 'show']); // عرض واحد
Route::post('/posts', [PostController::class, 'store'])->middleware('auth:sanctum'); // إنشاء
Route::post('/posts/{id}/update', [PostController::class, 'update'])->middleware('auth:sanctum'); // تحديث
Route::post('/posts/{id}/approve', [PostController::class, 'approve'])->middleware('auth:sanctum'); // موافقة Admin
Route::delete('/posts/{id}', [PostController::class, 'destroy'])->middleware('auth:sanctum'); // حذف

// ============================================================================
// الصور (Images)
// ============================================================================

use App\Http\Controllers\ImageController;

// --- CRUD Operations (Admin Only) ---
Route::post('/images', [ImageController::class, 'store'])->middleware('auth:sanctum'); // رفع صورة (Admin)
Route::post('/images/{id}/update', [ImageController::class, 'update'])->middleware('auth:sanctum'); // تحديث (Admin)
Route::delete('/images/{id}', [ImageController::class, 'destroy'])->middleware('auth:sanctum'); // حذف (Admin)

// --- Public & Search ---
Route::get('/images/search', [ImageController::class, 'search']); // البحث وعرض الكل (قبل {id})
Route::get('/images/{id}', [ImageController::class, 'show']); // عرض صورة واحدة

// --- Favorites ---
Route::post('/images/{id}/favorite', [ImageController::class, 'toggleFavorite'])->middleware('auth:sanctum'); // إضافة/إزالة مفضلة
Route::get('/images/favorites/my', [ImageController::class, 'getFavorites'])->middleware('auth:sanctum'); // عرض المفضلة

// ============================================================================
// المستجدات (News/Updates) - الإضافات والتعديلات
// ============================================================================

// عرض جميع المستجدات (الإضافات الجديدة + التعديلات)
// Parameters:
// - days: عدد الأيام (افتراضي 7)
// - type: فلترة حسب النوع (poems, lessons, sayings, images, activities)
// - page: رقم الصفحة
// الزوار: يرون العام فقط | المسجلين: يرون العام + الخاص
Route::get('/news', [NewsController::class, 'index']);

// إحصائيات المستجدات
Route::get('/news/statistics', [NewsController::class, 'statistics']);


