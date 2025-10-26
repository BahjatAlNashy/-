<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * عرض جميع المشاركات الموافق عليها مع Pagination
     */
    public function index()
    {
        // عرض المشاركات الموافق عليها فقط
        $posts = Post::with('user')
            ->where('is_approved', true)
            ->latest()
            ->paginate(15);

        // تنسيق البيانات
        $formattedPosts = $posts->through(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'author_name' => $post->user->name ?? 'غير معروف',
                'author_id' => $post->user_id,
                'created_at' => $post->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedPosts
        ]);
    }

    /**
     * عرض مشاركة واحدة (موافق عليها فقط أو مشاركة المستخدم نفسه)
     */
    public function show($id)
    {
        $post = Post::with('user')->find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'المشاركة غير موجودة.'
            ], 404);
        }

        $user = Auth::user();
        
        // التحقق: إما موافق عليها أو المالك
        if (!$post->is_approved && (!$user || $post->user_id !== $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'هذه المشاركة في انتظار موافقة الإدارة.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $post->id,
                'content' => $post->content,
                'author_name' => $post->user->name ?? 'غير معروف',
                'author_id' => $post->user_id,
                'created_at' => $post->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * إنشاء مشاركة جديدة (مستخدمين مسجلين فقط)
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'content' => 'required|string|min:10|max:2000',
        ]);

        $user = Auth::user();

        $post = Post::create([
            'content' => $validatedData['content'],
            'user_id' => $user->id,
            'is_approved' => false, // في انتظار موافقة Admin
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المشاركة بنجاح. في انتظار موافقة الإدارة.',
            'data' => [
                'id' => $post->id,
                'content' => $post->content,
                'author_name' => $user->name,
                'author_id' => $user->id,
                'created_at' => $post->created_at->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }

    /**
     * تحديث مشاركة (المالك فقط)
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'المشاركة غير موجودة.'
            ], 404);
        }

        $user = Auth::user();

        // التحقق من الصلاحيات: المالك فقط
        if ($post->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالتحديث. يمكن للمالك فقط تحديث المشاركة.'
            ], 403);
        }

        $validatedData = $request->validate([
            'content' => 'required|string|min:10|max:2000',
        ]);

        $post->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'تم التحديث بنجاح.',
            'data' => [
                'id' => $post->id,
                'content' => $post->content,
                'author_name' => $user->name,
                'author_id' => $user->id,
                'created_at' => $post->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * حذف مشاركة (المالك أو Admin)
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'المشاركة غير موجودة.'
            ], 404);
        }

        $user = Auth::user();

        // التحقق من الصلاحيات: المالك أو Admin
        if ($post->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالحذف. يمكن للمالك أو Admin فقط حذف المشاركة.'
            ], 403);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم الحذف بنجاح.'
        ]);
    }

    /**
     * البحث في المشاركات الموافق عليها
     */
    public function search(Request $request)
    {
        $query = Post::query();

        // عرض المشاركات الموافق عليها فقط
        $query->where('is_approved', true);

        // البحث حسب الكلمة المفتاحية
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where('content', 'LIKE', '%' . $keyword . '%');
        }

        $posts = $query->with('user')->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب المشاركات بنجاح',
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ],
            'data' => $posts->items(),
        ]);
    }

    /**
     * عرض مشاركات المستخدم الخاصة به (موافق عليها وغير موافق)
     */
    public function myPosts()
    {
        $user = Auth::user();

        $posts = Post::where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        // تنسيق البيانات
        $formattedPosts = $posts->through(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'is_approved' => $post->is_approved,
                'status' => $post->is_approved ? 'موافق عليها' : 'في انتظار الموافقة',
                'created_at' => $post->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedPosts
        ]);
    }

    /**
     * عرض المشاركات في انتظار الموافقة (Admin فقط)
     */
    public function pendingPosts()
    {
        $user = Auth::user();

        // التحقق من الصلاحيات: Admin فقط
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بهذه العملية. Admin فقط.'
            ], 403);
        }

        $posts = Post::with('user')
            ->where('is_approved', false)
            ->latest()
            ->paginate(15);

        // تنسيق البيانات
        $formattedPosts = $posts->through(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'author_name' => $post->user->name ?? 'غير معروف',
                'author_id' => $post->user_id,
                'created_at' => $post->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $posts->total(),
            'data' => $formattedPosts
        ]);
    }

    /**
     * الموافقة على مشاركة أو رفضها (Admin فقط)
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();

        // التحقق من الصلاحيات: Admin فقط
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بهذه العملية. Admin فقط.'
            ], 403);
        }

        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'المشاركة غير موجودة.'
            ], 404);
        }

        $validatedData = $request->validate([
            'is_approved' => 'required|boolean',
        ]);

        $post->update([
            'is_approved' => $validatedData['is_approved']
        ]);

        $message = $validatedData['is_approved'] 
            ? 'تم الموافقة على المشاركة بنجاح.' 
            : 'تم رفض المشاركة.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'id' => $post->id,
                'is_approved' => $post->is_approved,
            ]
        ]);
    }
}
