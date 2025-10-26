<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class ActivityController extends Controller
{
    // ============================================================================
    // عرض جميع الأنشطة (مع Pagination)
    // ============================================================================
    
    public function index(Request $request)
    {
        // المصادقة الاختيارية
        if (!Auth::check() && $request->bearerToken()) {
            $userFromToken = Auth::guard('sanctum')->user();
            if ($userFromToken) {
                Auth::login($userFromToken);
            }
        }
        
        $user = Auth::user();
        
        // جلب الأنشطة مع العلاقات والتصفح
        $query = Activity::with(['user:id,name', 'favoritedBy']);
        
        // تطبيق منطق الخصوصية
        if (!$user) {
            // الزوار: العام فقط
            $query->where('is_private', false);
        }
        // المسجلين: يرون كل شيء (العام + الخاص)
        
        $activities = $query->orderBy('activity_date', 'desc')->paginate(15);
        
        // تنسيق البيانات
        $formattedActivities = $activities->through(function($activity) use ($user) {
            $isFavorited = false;
            if ($user) {
                $isFavorited = $activity->isFavoritedBy($user);
            }
            
            return [
                'id' => $activity->id,
                'title' => $activity->title,
                'description' => $activity->description,
                'date' => $activity->activity_date,
                'video_url' => $activity->video_url,
                'is_private' => $activity->is_private,
                'is_favorited' => $isFavorited,
                'author_name' => $activity->user->name ?? 'غير معروف',
            ];
        })->items();
        
        return response()->json([
            'success' => true,
            'message' => 'تم جلب الأنشطة بنجاح',
            'meta' => [
                'page_number' => $activities->currentPage(),
                'total_pages' => $activities->lastPage(),
                'has_previous' => $activities->currentPage() > 1,
                'has_next' => $activities->hasMorePages(),
                'total_items' => $activities->total(),
            ],
            'data' => $formattedActivities
        ]);
    }

    // ============================================================================
    // البحث في الأنشطة (مع Pagination)
    // ============================================================================
    
    public function search(Request $request)
    {
        // المصادقة الاختيارية
        if (!Auth::check() && $request->bearerToken()) {
            $userFromToken = Auth::guard('sanctum')->user();
            if ($userFromToken) {
                Auth::login($userFromToken);
            }
        }
        
        $user = Auth::user();
        $query = Activity::query();
        
        // البحث بالكلمة المفتاحية
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('description', 'LIKE', '%' . $keyword . '%');
            });
        }
        
        // البحث حسب السنة
        if ($request->filled('year')) {
            $query->whereYear('activity_date', $request->input('year'));
        }
        
        // البحث حسب الشهر
        if ($request->filled('month')) {
            $query->whereMonth('activity_date', $request->input('month'));
        }
        
        // البحث حسب التاريخ
        if ($request->filled('date')) {
            $date = $request->input('date');
            $comparison = $request->input('date_comparison', '=');
            
            if (in_array($comparison, ['=', '>', '<', '>=', '<='])) {
                $query->whereDate('activity_date', $comparison, $date);
            } else {
                $query->whereDate('activity_date', '=', $date);
            }
        }
        
        // تطبيق منطق الخصوصية
        if (!$user) {
            // الزوار: العام فقط
            $query->where('is_private', false);
        }
        // المسجلين: يرون كل شيء (العام + الخاص)
        
        // التحميل المبكر والتصفح
        $query->with(['user:id,name', 'favoritedBy']);
        $activities = $query->orderBy('activity_date', 'desc')->paginate(15);
        
        // تنسيق البيانات
        $formattedActivities = $activities->through(function($activity) use ($user) {
            $isFavorited = false;
            if ($user) {
                $isFavorited = $activity->isFavoritedBy($user);
            }
            
            return [
                'id' => $activity->id,
                'title' => $activity->title,
                'description' => $activity->description,
                'date' => $activity->activity_date,
                'video_url' => $activity->video_url,
                'is_private' => $activity->is_private,
                'is_favorited' => $isFavorited,
                'author_name' => $activity->user->name ?? 'غير معروف',
            ];
        })->items();
        
        return response()->json([
            'success' => true,
            'message' => 'نتائج البحث عن الأنشطة',
            'meta' => [
                'page_number' => $activities->currentPage(),
                'total_pages' => $activities->lastPage(),
                'has_previous' => $activities->currentPage() > 1,
                'has_next' => $activities->hasMorePages(),
                'total_items' => $activities->total(),
            ],
            'data' => $formattedActivities
        ]);
    }

    // ============================================================================
    // عرض تفاصيل نشاط
    // ============================================================================
    
    public function show(Request $request, $activity_id)
    {
        // المصادقة الاختيارية
        if (!Auth::check() && $request->bearerToken()) {
            $userFromToken = Auth::guard('sanctum')->user();
            if ($userFromToken) {
                Auth::login($userFromToken);
            }
        }
        
        $user = Auth::user();
        
        // جلب النشاط
        $activity = Activity::with(['user:id,name', 'favoritedBy'])->find($activity_id);
        
        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'النشاط المطلوب غير موجود.'
            ], 404);
        }
        
        // فحص الخصوصية
        if ($activity->is_private && !$user) {
            return response()->json([
                'success' => false,
                'message' => 'هذا النشاط خاص، يجب تسجيل الدخول للوصول إليه.'
            ], 403);
        }
        
        // حساب حالة المفضلة
        $isFavorited = false;
        if ($user) {
            $isFavorited = $activity->isFavoritedBy($user);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'تم جلب تفاصيل النشاط بنجاح',
            'data' => [
                'id' => $activity->id,
                'title' => $activity->title,
                'description' => $activity->description,
                'date' => $activity->activity_date,
                'video_url' => $activity->video_url,
                'is_private' => $activity->is_private,
                'is_favorited' => $isFavorited,
                'author_name' => $activity->user->name ?? 'غير معروف',
            ]
        ]);
    }

    // ============================================================================
    // إضافة نشاط جديد (Admin فقط)
    // ============================================================================
    
    public function store(Request $request)
    {
        // التحقق من الصلاحيات
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action!'
            ], 403);
        }
        
        // التحقق من صحة المدخلات
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'nullable|date',
            'video' => 'required|file|mimes:mp4,mov,avi,wmv|max:102400', // max 100MB
            'is_private' => 'boolean',
        ]);
        
        // رفع الفيديو
        $videoPath = null;
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('activities/videos', 'public');
        }
        
        // إنشاء النشاط
        $activity = Activity::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'] ?? null,
            'activity_date' => $validatedData['activity_date'] ?? null,
            'video_path' => $videoPath,
            'user_id' => Auth::id(),
            'is_private' => $validatedData['is_private'] ?? false,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء النشاط بنجاح',
            'data' => [
                'activity' => [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'date' => $activity->activity_date,
                    'video_url' => $activity->video_url,
                ]
            ]
        ], 201);
    }

    // ============================================================================
    // تحديث نشاط (Admin فقط)
    // ============================================================================
    
    public function update(Request $request, $activity_id)
    {
        // التحقق من الصلاحيات
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action!'
            ], 403);
        }
        
        $activity = Activity::find($activity_id);
        
        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'النشاط المطلوب غير موجود.'
            ], 404);
        }
        
        // التحقق من صحة المدخلات
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'nullable|date',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:102400',
            'is_private' => 'boolean',
        ]);
        
        // تحديث الفيديو إذا تم رفع فيديو جديد
        if ($request->hasFile('video')) {
            // حذف الفيديو القديم
            if ($activity->video_path) {
                Storage::disk('public')->delete($activity->video_path);
            }
            
            // رفع الفيديو الجديد
            $activity->video_path = $request->file('video')->store('activities/videos', 'public');
        }
        
        // تحديث البيانات الأخرى
        if (isset($validatedData['title'])) {
            $activity->title = $validatedData['title'];
        }
        if (isset($validatedData['description'])) {
            $activity->description = $validatedData['description'];
        }
        if (isset($validatedData['activity_date'])) {
            $activity->activity_date = $validatedData['activity_date'];
        }
        if (isset($validatedData['is_private'])) {
            $activity->is_private = $validatedData['is_private'];
        }
        
        $activity->save();
        
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث النشاط بنجاح',
            'data' => [
                'activity' => [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'date' => $activity->activity_date,
                    'video_url' => $activity->video_url,
                ]
            ]
        ]);
    }

    // ============================================================================
    // حذف نشاط (Admin فقط)
    // ============================================================================
    
    public function destroy($activity_id)
    {
        // التحقق من الصلاحيات
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action!'
            ], 403);
        }
        
        $activity = Activity::find($activity_id);
        
        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'النشاط المطلوب غير موجود.'
            ], 404);
        }
        
        // حذف الفيديو من التخزين
        if ($activity->video_path) {
            Storage::disk('public')->delete($activity->video_path);
        }
        
        // حذف النشاط (سيحذف المفضلة تلقائياً بسبب cascade)
        $activity->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف النشاط بنجاح'
        ]);
    }

    // ============================================================================
    // إضافة/إزالة من المفضلة (Toggle)
    // ============================================================================
    
    public function toggleFavorite($activity_id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً.'
            ], 401);
        }
        
        $activity = Activity::find($activity_id);
        
        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'النشاط المطلوب غير موجود.'
            ], 404);
        }
        
        // التحقق من وجود المفضلة
        $isFavorited = $activity->isFavoritedBy($user);
        
        if ($isFavorited) {
            // إزالة من المفضلة
            $user->favoriteActivities()->detach($activity_id);
            
            return response()->json([
                'success' => true,
                'message' => 'تمت إزالة النشاط من المفضلة.',
                'is_favorited' => false
            ]);
        } else {
            // إضافة للمفضلة
            $user->favoriteActivities()->attach($activity_id);
            
            return response()->json([
                'success' => true,
                'message' => 'تمت إضافة النشاط إلى المفضلة.',
                'is_favorited' => true
            ]);
        }
    }

    // ============================================================================
    // عرض الأنشطة المفضلة
    // ============================================================================
    
    public function getFavoriteActivities()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً.'
            ], 401);
        }
        
        // جلب الأنشطة المفضلة مع التصفح
        $favoriteActivities = $user->favoriteActivities()
            ->with(['user:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // تنسيق البيانات
        $formattedActivities = $favoriteActivities->through(function($activity) {
            return [
                'id' => $activity->id,
                'title' => $activity->title,
                'date' => $activity->activity_date,
                'video_url' => $activity->video_url,
                'is_favorited' => true, // دائماً true لأنها في المفضلة
            ];
        })->items();
        
        return response()->json([
            'success' => true,
            'message' => 'تم جلب الأنشطة المفضلة بنجاح',
            'meta' => [
                'page_number' => $favoriteActivities->currentPage(),
                'total_pages' => $favoriteActivities->lastPage(),
                'has_previous' => $favoriteActivities->currentPage() > 1,
                'has_next' => $favoriteActivities->hasMorePages(),
                'total_items' => $favoriteActivities->total(),
            ],
            'data' => $formattedActivities
        ]);
    }
}
