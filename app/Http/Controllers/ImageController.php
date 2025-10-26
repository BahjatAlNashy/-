<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * إضافة صورة جديدة (Admin فقط)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // التحقق من أن المستخدم Admin
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك. Admin فقط.'
            ], 403);
        }

        // Validation
        $validatedData = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            'is_private' => 'boolean',
        ]);

        // رفع الصورة
        $imagePath = $request->file('image')->store('images', 'public');
        $imageUrl = Storage::url($imagePath);

        // إنشاء السجل
        $image = Image::create([
            'title' => $validatedData['title'] ?? null,
            'description' => $validatedData['description'] ?? null,
            'image_path' => $imagePath,
            'image_url' => $imageUrl,
            'user_id' => $user->id,
            'is_private' => $validatedData['is_private'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم رفع الصورة بنجاح',
            'data' => $image
        ], 201);
    }

    /**
     * عرض جميع الصور أو البحث (مع Pagination)
     */
    public function search(Request $request)
    {
        // تحميل المستخدم من التوكن إذا كان موجوداً
        if (!Auth::check() && $request->bearerToken()) {
            $userFromToken = Auth::guard('sanctum')->user();
            if ($userFromToken) {
                Auth::login($userFromToken);
            }
        }

        $user = Auth::user();
        $query = Image::query();

        // منطق الخصوصية
        if (!Auth::check()) {
            $query->where('is_private', false);
        }

        // البحث حسب العنوان أو الوصف
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('description', 'LIKE', '%' . $keyword . '%');
            });
        }

        // Pagination
        $images = $query->with(['user:id,name', 'favoritedBy'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        // تنسيق البيانات
        $formattedImages = $images->through(function ($image) use ($user) {
            $isFavorited = false;
            if ($user) {
                $isFavorited = $image->isFavoritedBy($user);
            }

            return [
                'id' => $image->id,
                'title' => $image->title,
                'description' => $image->description,
                'image_url' => url($image->image_url),
                'is_private' => $image->is_private,
                'is_favorited' => $isFavorited,
                'uploaded_by' => $image->user->name ?? null,
                'created_at' => $image->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الصور بنجاح',
            'meta' => [
                'current_page' => $images->currentPage(),
                'last_page' => $images->lastPage(),
                'per_page' => $images->perPage(),
                'total' => $images->total(),
                'from' => $images->firstItem(),
                'to' => $images->lastItem(),
            ],
            'data' => $formattedImages->items(),
        ]);
    }

    /**
     * عرض صورة واحدة
     */
    public function show($id)
    {
        // تحميل المستخدم من التوكن
        if (!Auth::check() && request()->bearerToken()) {
            $userFromToken = Auth::guard('sanctum')->user();
            if ($userFromToken) {
                Auth::login($userFromToken);
            }
        }

        $user = Auth::user();
        $image = Image::with(['user:id,name', 'favoritedBy'])->find($id);

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'الصورة غير موجودة'
            ], 404);
        }

        // التحقق من الخصوصية
        if ($image->is_private && !Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الصورة خاصة. يجب تسجيل الدخول.'
            ], 403);
        }

        $isFavorited = false;
        if ($user) {
            $isFavorited = $image->isFavoritedBy($user);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $image->id,
                'title' => $image->title,
                'description' => $image->description,
                'image_url' => url($image->image_url),
                'is_private' => $image->is_private,
                'is_favorited' => $isFavorited,
                'uploaded_by' => $image->user->name ?? null,
                'created_at' => $image->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * تحديث صورة (Admin فقط)
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك. Admin فقط.'
            ], 403);
        }

        $image = Image::find($id);

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'الصورة غير موجودة'
            ], 404);
        }

        $validatedData = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'is_private' => 'boolean',
        ]);

        // تحديث البيانات النصية
        $image->title = $validatedData['title'] ?? $image->title;
        $image->description = $validatedData['description'] ?? $image->description;
        $image->is_private = $validatedData['is_private'] ?? $image->is_private;

        // إذا تم رفع صورة جديدة
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة
            Storage::disk('public')->delete($image->image_path);

            // رفع الصورة الجديدة
            $imagePath = $request->file('image')->store('images', 'public');
            $imageUrl = Storage::url($imagePath);

            $image->image_path = $imagePath;
            $image->image_url = $imageUrl;
        }

        $image->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الصورة بنجاح',
            'data' => $image
        ]);
    }

    /**
     * حذف صورة (Admin فقط)
     */
    public function destroy($id)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك. Admin فقط.'
            ], 403);
        }

        $image = Image::find($id);

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'الصورة غير موجودة'
            ], 404);
        }

        // حذف الملف من التخزين
        Storage::disk('public')->delete($image->image_path);

        // حذف السجل
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الصورة بنجاح'
        ]);
    }

    /**
     * إضافة/إزالة من المفضلة (Toggle)
     */
    public function toggleFavorite($id)
    {
        $image = Image::find($id);

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'الصورة غير موجودة'
            ], 404);
        }

        $user = Auth::user();
        $user->favoriteImages()->toggle($image->id);

        $isFavorited = $user->favoriteImages()->where('image_id', $image->id)->exists();

        $message = $isFavorited 
            ? 'تمت إضافة الصورة إلى المفضلة' 
            : 'تمت إزالة الصورة من المفضلة';

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorited' => $isFavorited,
        ]);
    }

    /**
     * عرض الصور المفضلة
     */
    public function getFavorites()
    {
        $user = Auth::user();

        $favoriteImages = $user->favoriteImages()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $formattedImages = $favoriteImages->through(function ($image) {
            return [
                'id' => $image->id,
                'title' => $image->title,
                'description' => $image->description,
                'image_url' => url($image->image_url),
                'is_private' => $image->is_private,
                'is_favorited' => true,
                'uploaded_by' => $image->user->name ?? null,
                'created_at' => $image->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $favoriteImages->total(),
            'meta' => [
                'current_page' => $favoriteImages->currentPage(),
                'last_page' => $favoriteImages->lastPage(),
                'per_page' => $favoriteImages->perPage(),
                'total' => $favoriteImages->total(),
            ],
            'data' => $formattedImages->items(),
        ]);
    }
}
