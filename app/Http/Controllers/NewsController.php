<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poem;
use App\Models\Lesson;
use App\Models\Saying;
use App\Models\Image;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NewsController extends Controller
{
    /**
     * عرض جميع المستجدات (الإضافات والتعديلات)
     * الزوار: يرون العام فقط
     * المسجلين: يرون العام + الخاص
     */
    public function index(Request $request)
    {
        // دعم optional token
        if ($request->bearerToken()) {
            $userFromToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($userFromToken) {
                Auth::login($userFromToken->tokenable);
            }
        }

        $user = Auth::user();
        
        // تحديد الفترة الزمنية (افتراضياً: آخر 7 أيام)
        $days = $request->input('days', 7);
        $startDate = Carbon::now()->subDays($days);

        // فلترة حسب النوع
        $type = $request->input('type'); // poems, lessons, sayings, images, activities
        $allowedTypes = ['poems', 'lessons', 'sayings', 'images', 'activities'];

        // جلب القصائد (الجديدة والمعدلة)
        $poemsQuery = Poem::where(function($q) use ($startDate) {
                            $q->where('created_at', '>=', $startDate)
                              ->orWhere('updated_at', '>=', $startDate);
                        })
                        ->with('user:id,name');
        
        // جلب الدروس (الجديدة والمعدلة)
        $lessonsQuery = Lesson::where(function($q) use ($startDate) {
                              $q->where('created_at', '>=', $startDate)
                                ->orWhere('updated_at', '>=', $startDate);
                          })
                          ->with('user:id,name');
        
        // جلب الأقوال (الجديدة والمعدلة)
        $sayingsQuery = Saying::where(function($q) use ($startDate) {
                              $q->where('created_at', '>=', $startDate)
                                ->orWhere('updated_at', '>=', $startDate);
                          })
                          ->with('user:id,name');
        
        // جلب الصور (الجديدة والمعدلة)
        $imagesQuery = Image::where(function($q) use ($startDate) {
                            $q->where('created_at', '>=', $startDate)
                              ->orWhere('updated_at', '>=', $startDate);
                        })
                        ->with('user:id,name');

        // جلب الأنشطة (الجديدة والمعدلة)
        $activitiesQuery = Activity::where(function($q) use ($startDate) {
                                       $q->where('created_at', '>=', $startDate)
                                         ->orWhere('updated_at', '>=', $startDate);
                                   })
                                   ->with('user:id,name');

        // تطبيق منطق الخصوصية
        if (!$user) {
            // الزوار: العام فقط
            $poemsQuery->where('is_private', false);
            $lessonsQuery->where('is_private', false);
            $sayingsQuery->where('is_private', false);
            $imagesQuery->where('is_private', false);
            $activitiesQuery->where('is_private', false);
        }
        // المسجلين: يرون كل شيء (العام + الخاص)

        // جلب البيانات حسب الفلتر
        $poems = (!$type || $type === 'poems') ? $poemsQuery->latest('updated_at')->get() : collect();
        $lessons = (!$type || $type === 'lessons') ? $lessonsQuery->latest('updated_at')->get() : collect();
        $sayings = (!$type || $type === 'sayings') ? $sayingsQuery->latest('updated_at')->get() : collect();
        $images = (!$type || $type === 'images') ? $imagesQuery->latest('updated_at')->get() : collect();
        $activities = (!$type || $type === 'activities') ? $activitiesQuery->latest('updated_at')->get() : collect();

        // دمج جميع المستجدات في مصفوفة واحدة
        $allUpdates = [];

        // إضافة القصائد
        foreach ($poems as $poem) {
            $isNew = $poem->created_at->gte($startDate);
            $isUpdated = $poem->updated_at->gte($startDate) && $poem->created_at->lt($startDate);
            
            $allUpdates[] = [
                'id' => $poem->id,
                'type' => 'poem',
                'type_ar' => 'قصيدة',
                'title' => $poem->title,
                'description' => $poem->description,
                'is_private' => $poem->is_private,
                'author_name' => $poem->user->name ?? 'غير معروف',
                'status' => $isNew ? 'new' : ($isUpdated ? 'updated' : 'new'),
                'status_ar' => $isNew ? 'جديد' : ($isUpdated ? 'محدث' : 'جديد'),
                'created_at' => $poem->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $poem->updated_at->format('Y-m-d H:i:s'),
                'created_at_human' => $poem->created_at->diffForHumans(),
                'updated_at_human' => $poem->updated_at->diffForHumans(),
            ];
        }

        // إضافة الدروس
        foreach ($lessons as $lesson) {
            $isNew = $lesson->created_at->gte($startDate);
            $isUpdated = $lesson->updated_at->gte($startDate) && $lesson->created_at->lt($startDate);
            
            $allUpdates[] = [
                'id' => $lesson->id,
                'type' => 'lesson',
                'type_ar' => 'درس',
                'title' => $lesson->title,
                'description' => $lesson->description,
                'is_private' => $lesson->is_private,
                'author_name' => $lesson->user->name ?? 'غير معروف',
                'status' => $isNew ? 'new' : ($isUpdated ? 'updated' : 'new'),
                'status_ar' => $isNew ? 'جديد' : ($isUpdated ? 'محدث' : 'جديد'),
                'created_at' => $lesson->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $lesson->updated_at->format('Y-m-d H:i:s'),
                'created_at_human' => $lesson->created_at->diffForHumans(),
                'updated_at_human' => $lesson->updated_at->diffForHumans(),
            ];
        }

        // إضافة الأقوال
        foreach ($sayings as $saying) {
            $isNew = $saying->created_at->gte($startDate);
            $isUpdated = $saying->updated_at->gte($startDate) && $saying->created_at->lt($startDate);
            
            $allUpdates[] = [
                'id' => $saying->id,
                'type' => 'saying',
                'type_ar' => $saying->type === 'saying' ? 'قول' : 'دعاء',
                'title' => mb_substr($saying->content, 0, 50) . '...',
                'content' => $saying->content,
                'is_private' => $saying->is_private,
                'author_name' => $saying->user->name ?? 'غير معروف',
                'status' => $isNew ? 'new' : ($isUpdated ? 'updated' : 'new'),
                'status_ar' => $isNew ? 'جديد' : ($isUpdated ? 'محدث' : 'جديد'),
                'created_at' => $saying->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $saying->updated_at->format('Y-m-d H:i:s'),
                'created_at_human' => $saying->created_at->diffForHumans(),
                'updated_at_human' => $saying->updated_at->diffForHumans(),
            ];
        }

        // إضافة الصور
        foreach ($images as $image) {
            $isNew = $image->created_at->gte($startDate);
            $isUpdated = $image->updated_at->gte($startDate) && $image->created_at->lt($startDate);
            
            $allUpdates[] = [
                'id' => $image->id,
                'type' => 'image',
                'type_ar' => 'صورة',
                'title' => $image->title ?? 'صورة',
                'description' => $image->description,
                'image_url' => $image->image_url,
                'is_private' => $image->is_private,
                'author_name' => $image->user->name ?? 'غير معروف',
                'status' => $isNew ? 'new' : ($isUpdated ? 'updated' : 'new'),
                'status_ar' => $isNew ? 'جديد' : ($isUpdated ? 'محدث' : 'جديد'),
                'created_at' => $image->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $image->updated_at->format('Y-m-d H:i:s'),
                'created_at_human' => $image->created_at->diffForHumans(),
                'updated_at_human' => $image->updated_at->diffForHumans(),
            ];
        }

        // إضافة الأنشطة
        foreach ($activities as $activity) {
            $isNew = $activity->created_at->gte($startDate);
            $isUpdated = $activity->updated_at->gte($startDate) && $activity->created_at->lt($startDate);
            
            $allUpdates[] = [
                'id' => $activity->id,
                'type' => 'activity',
                'type_ar' => 'نشاط',
                'title' => $activity->title,
                'description' => $activity->description,
                'is_private' => $activity->is_private ?? false,
                'author_name' => $activity->user->name ?? 'غير معروف',
                'status' => $isNew ? 'new' : ($isUpdated ? 'updated' : 'new'),
                'status_ar' => $isNew ? 'جديد' : ($isUpdated ? 'محدث' : 'جديد'),
                'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $activity->updated_at->format('Y-m-d H:i:s'),
                'created_at_human' => $activity->created_at->diffForHumans(),
                'updated_at_human' => $activity->updated_at->diffForHumans(),
            ];
        }

        // ترتيب حسب التاريخ (الأحدث أولاً - حسب updated_at)
        usort($allUpdates, function($a, $b) {
            return strtotime($b['updated_at']) - strtotime($a['updated_at']);
        });

        // Pagination يدوي
        $page = $request->input('page', 1);
        $perPage = 20;
        $total = count($allUpdates);
        $lastPage = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $paginatedUpdates = array_slice($allUpdates, $offset, $perPage);

        // حساب الإحصائيات
        $newCount = count(array_filter($allUpdates, fn($item) => $item['status'] === 'new'));
        $updatedCount = count(array_filter($allUpdates, fn($item) => $item['status'] === 'updated'));

        return response()->json([
            'success' => true,
            'message' => 'تم جلب المستجدات بنجاح',
            'meta' => [
                'current_page' => (int)$page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
                'period_days' => $days,
                'start_date' => $startDate->format('Y-m-d'),
            ],
            'statistics' => [
                'poems_count' => $poems->count(),
                'lessons_count' => $lessons->count(),
                'sayings_count' => $sayings->count(),
                'images_count' => $images->count(),
                'activities_count' => $activities->count(),
                'total_count' => $total,
                'new_count' => $newCount,
                'updated_count' => $updatedCount,
            ],
            'filter' => [
                'type' => $type,
                'available_types' => $allowedTypes,
            ],
            'data' => $paginatedUpdates
        ]);
    }

    /**
     * عرض إحصائيات المستجدات
     */
    public function statistics(Request $request)
    {
        // دعم optional token
        if ($request->bearerToken()) {
            $userFromToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($userFromToken) {
                Auth::login($userFromToken->tokenable);
            }
        }

        $user = Auth::user();
        $days = $request->input('days', 7);
        $startDate = Carbon::now()->subDays($days);

        // إحصائيات حسب النوع
        $poemsCount = Poem::where(function($q) use ($startDate) {
                            $q->where('created_at', '>=', $startDate)
                              ->orWhere('updated_at', '>=', $startDate);
                        })
                        ->when(!$user, fn($q) => $q->where('is_private', false))
                        ->count();

        $lessonsCount = Lesson::where(function($q) use ($startDate) {
                              $q->where('created_at', '>=', $startDate)
                                ->orWhere('updated_at', '>=', $startDate);
                          })
                          ->when(!$user, fn($q) => $q->where('is_private', false))
                          ->count();

        $sayingsCount = Saying::where(function($q) use ($startDate) {
                              $q->where('created_at', '>=', $startDate)
                                ->orWhere('updated_at', '>=', $startDate);
                          })
                          ->when(!$user, fn($q) => $q->where('is_private', false))
                          ->count();

        $imagesCount = Image::where(function($q) use ($startDate) {
                            $q->where('created_at', '>=', $startDate)
                              ->orWhere('updated_at', '>=', $startDate);
                        })
                        ->when(!$user, fn($q) => $q->where('is_private', false))
                        ->count();

        $activitiesCount = Activity::where(function($q) use ($startDate) {
                                       $q->where('created_at', '>=', $startDate)
                                         ->orWhere('updated_at', '>=', $startDate);
                                   })
                                   ->when(!$user, fn($q) => $q->where('is_private', false))
                                   ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'period_days' => $days,
                'start_date' => $startDate->format('Y-m-d'),
                'poems_count' => $poemsCount,
                'lessons_count' => $lessonsCount,
                'sayings_count' => $sayingsCount,
                'images_count' => $imagesCount,
                'activities_count' => $activitiesCount,
                'total_count' => $poemsCount + $lessonsCount + $sayingsCount + $imagesCount + $activitiesCount,
            ]
        ]);
    }
}
