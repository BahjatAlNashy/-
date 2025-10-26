<?php

namespace App\Http\Controllers;

use App\Models\Saying;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SayingController extends Controller
{
    /**
     * عرض جميع الأقوال مع Pagination
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
        $query = Saying::query();
        
        // منطق الخصوصية
        if (!Auth::check()) {
            $query->where('is_private', false);
        }

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $type = $request->input('type');
            if (in_array($type, [Saying::TYPE_SAYING, Saying::TYPE_SUPPLICATION])) {
                $query->where('type', $type);
            }
        }

        // تحميل العلاقات
        $query->with(['user', 'comments']);

        // Pagination
        $sayings = $query->latest()->paginate(15);

        // تنسيق البيانات
        $formattedSayings = $sayings->through(function ($saying) use ($user) {
            $isFavorited = $user ? $saying->isFavoritedBy($user) : false;

            return [
                'id' => $saying->id,
                'type' => $saying->type,
                'content' => $saying->content,
                'is_private' => $saying->is_private,
                'is_favorited' => $isFavorited,
                'comments_count' => $saying->comments->count(),
                'author_name' => $saying->user->name ?? 'غير معروف',
                'created_at' => $saying->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedSayings,
        ]);
    }

    /**
     * عرض تفاصيل قول واحد
     */
    public function show(Request $request, $id)
    {
        // دعم optional token
        if ($request->bearerToken()) {
            $userFromToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($userFromToken) {
                Auth::login($userFromToken->tokenable);
            }
        }

        $user = Auth::user();
        $saying = Saying::with(['user'])->find($id);

        if (!$saying) {
            return response()->json([
                'success' => false,
                'message' => 'القول غير موجود.'
            ], 404);
        }

        // منطق الخصوصية
        if ($saying->is_private && !$user) {
            return response()->json([
                'success' => false,
                'message' => 'هذا القول خاص، يجب تسجيل الدخول للوصول إليه.'
            ], 403);
        }

        $isFavorited = $user ? $saying->isFavoritedBy($user) : false;

        // جلب التعليقات مع Pagination
        $page = request()->input('page', 1);
        $comments = $saying->comments()
                           ->with('user:id,name')
                           ->latest()
                           ->paginate(15, ['*'], 'page', $page);

        // تنسيق التعليقات
        $formattedComments = $comments->through(function($comment) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->diffForHumans(),
                'user' => [
                    'id' => $comment->user->id ?? null,
                    'name' => $comment->user->name ?? 'مستخدم محذوف',
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $saying->id,
                'type' => $saying->type,
                'content' => $saying->content,
                'is_private' => $saying->is_private,
                'is_favorited' => $isFavorited,
                'author_name' => $saying->user->name ?? 'غير معروف',
                'created_at' => $saying->created_at->format('Y-m-d H:i:s'),
                
                // التعليقات مع Pagination
                'comments' => [
                    'data' => $formattedComments->items(),
                    'meta' => [
                        'current_page' => $comments->currentPage(),
                        'last_page' => $comments->lastPage(),
                        'per_page' => $comments->perPage(),
                        'total' => $comments->total(),
                        'from' => $comments->firstItem(),
                        'to' => $comments->lastItem(),
                    ]
                ]
            ]
        ]);
    }

    /**
     * إنشاء قول جديد (Admin فقط)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // التحقق من الصلاحيات: Admin فقط
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإضافة أقوال. هذه الصلاحية للـ Admin فقط.'
            ], 403);
        }

        $validatedData = $request->validate([
            'type' => 'required|in:saying,supplication',
            'content' => 'required|string',
            'is_private' => 'boolean',
        ]);

        $saying = Saying::create([
            'type' => $validatedData['type'],
            'content' => $validatedData['content'],
            'user_id' => $user->id,
            'is_private' => $validatedData['is_private'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم الإنشاء بنجاح',
            'data' => $saying
        ], 201);
    }

    /**
     * تحديث قول (المالك فقط)
     */
    public function update(Request $request, $id)
    {
        $saying = Saying::find($id);

        if (!$saying) {
            return response()->json([
                'success' => false,
                'message' => 'القول غير موجود.'
            ], 404);
        }

        $user = Auth::user();

        // التحقق من الصلاحيات: المالك فقط
        if ($saying->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالتحديث. يمكن للمالك فقط تحديث القول.'
            ], 403);
        }

        $validatedData = $request->validate([
            'content' => 'required|string',
            'is_private' => 'boolean',
        ]);

        $saying->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'تم التحديث بنجاح.',
            'data' => $saying
        ]);
    }

    /**
     * حذف قول (المالك أو Admin)
     */
    public function destroy($id)
    {
        $saying = Saying::find($id);

        if (!$saying) {
            return response()->json([
                'success' => false,
                'message' => 'القول غير موجود.'
            ], 404);
        }

        $user = Auth::user();

        // التحقق من الصلاحيات: المالك أو Admin
        if ($saying->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالحذف. يمكن للمالك أو Admin فقط حذف القول.'
            ], 403);
        }

        $saying->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم الحذف بنجاح.'
        ]);
    }

    /**
     * البحث في الأقوال
     */
    public function search(Request $request)
    {
        // دعم optional token
        if ($request->bearerToken()) {
            $userFromToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($userFromToken) {
                Auth::login($userFromToken->tokenable);
            }
        }

        $query = Saying::query();

        // منطق الخصوصية: الزوار يرون العامة فقط، المسجلين يرون الكل
        if (!Auth::check()) {
            $query->where('is_private', false);
        }

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $type = $request->input('type');
            if (in_array($type, [Saying::TYPE_SAYING, Saying::TYPE_SUPPLICATION])) {
                $query->where('type', $type);
            }
        }

        // البحث حسب الكلمة المفتاحية
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where('content', 'LIKE', '%' . $keyword . '%');
        }

        $sayings = $query->with(['user', 'favoritedBy'])
                         ->orderBy('created_at', 'desc')
                         ->paginate(15);

        $user = Auth::user();

        // تنسيق البيانات لإضافة is_favorited
        $formattedSayings = $sayings->through(function ($saying) use ($user) {
            $isFavorited = false;
            if ($user) {
                $isFavorited = $saying->isFavoritedBy($user);
            }

            return [
                'id' => $saying->id,
                'type' => $saying->type,
                'content' => $saying->content,
                'is_private' => $saying->is_private,
                'is_favorited' => $isFavorited,
                'user_name' => $saying->user->name ?? null,
                'created_at' => $saying->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الأقوال بنجاح',
            'meta' => [
                'current_page' => $sayings->currentPage(),
                'last_page' => $sayings->lastPage(),
                'per_page' => $sayings->perPage(),
                'total' => $sayings->total(),
                'from' => $sayings->firstItem(),
                'to' => $sayings->lastItem(),
            ],
            'data' => $formattedSayings->items(),
        ]);
    }

    /**
     * إضافة/إزالة من المفضلة (Toggle)
     */
    public function toggleFavorite($id)
    {
        $saying = Saying::find($id);

        if (!$saying) {
            return response()->json([
                'success' => false,
                'message' => 'القول غير موجود.'
            ], 404);
        }

        $user = Auth::user();

        // التحقق من المفضلة
        if ($saying->isFavoritedBy($user)) {
            $saying->favoritedBy()->detach($user->id);
            $message = 'تم الإزالة من المفضلة.';
            $isFavorited = false;
        } else {
            $saying->favoritedBy()->attach($user->id);
            $message = 'تم الإضافة للمفضلة.';
            $isFavorited = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorited' => $isFavorited
        ]);
    }

    /**
     * عرض المفضلة
     */
    public function getFavoriteSayings(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->favoriteSayings()->with('user');

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $type = $request->input('type');
            if (in_array($type, [Saying::TYPE_SAYING, Saying::TYPE_SUPPLICATION])) {
                $query->where('type', $type);
            }
        }

        $sayings = $query->get();

        return response()->json([
            'success' => true,
            'count' => $sayings->count(),
            'data' => $sayings,
        ]);
    }
}
