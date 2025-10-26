<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Poem;
use App\Models\Lesson;
use App\Models\Saying;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    //
     public function store(Request $request, $poem_id)
    {
        $request->validate([
            'content' => 'required|string|min:5|max:500',
        ]);

        // 1. التأكد من وجود القصيدة
        $poem = Poem::findOrFail($poem_id);
        
        // 2. إنشاء التعليق
        $comment = Comment::create([
            'poem_id' => $poem->id,
            'user_id' => Auth::id(), // ربط التعليق بالمستخدم الموثق
            'content' => $request->content,
        ]);

        // 3. إرجاع التعليق مع اسم المستخدم
        return response()->json([
            'success' => true,
            'message' => 'تم إضافة التعليق بنجاح.',
            'data' => $comment->load('user:id,name') // تحميل اسم المستخدم فقط
        ], 201);
    }
#--------------------------------------------------------------------------------------------------------------------

#هذا التابع لعرض جميع التعليقات على قصيدة معينة، مع اسم المستخدم الذي قام بالتعليق.


    public function index($poem_id)
{
    // العثور على القصيدة للتأكد من وجودها (سيرجع 404 إذا لم توجد)
    Poem::findOrFail($poem_id); 
    
    // جلب التعليقات للقصيدة المحددة
    $comments = Comment::where('poem_id', $poem_id)
                       // تحميل اسم المستخدم فقط (لتقليل البيانات)
                       ->with('user:id,name') 
                       ->latest() // عرض الأحدث أولاً
                       ->paginate(15); // تطبيق التصفح

    return response()->json([
        'success' => true,
        'message' => 'تم جلب التعليقات بنجاح',
        'meta' => [
            'current_page' => $comments->currentPage(),
            'last_page' => $comments->lastPage(),
            'per_page' => $comments->perPage(),
            'total' => $comments->total(),
            'from' => $comments->firstItem(),
            'to' => $comments->lastItem(),
        ],
        'data' => $comments->items()
    ]);
}

#--------------------------------------------------------------------------------------------------------------------
# UPDATE - تحديث تعليق على قصيدة
public function updatePoemComment(Request $request, $comment_id)
{
    $request->validate([
        'content' => 'required|string|min:5|max:500',
    ]);

    // 1. البحث عن التعليق
    $comment = Comment::findOrFail($comment_id);

    // 2. التحقق من الصلاحيات (فقط صاحب التعليق)
    $user = Auth::user();
    if ($comment->user_id !== $user->id) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بتعديل هذا التعليق.'
        ], 403);
    }

    // 3. تحديث التعليق
    $comment->update([
        'content' => $request->content
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث التعليق بنجاح.',
        'data' => $comment->load('user:id,name')
    ]);
}

#--------------------------------------------------------------------------------------------------------------------
# DELETE - حذف تعليق على قصيدة
public function destroyPoemComment($comment_id)
{
    // 1. البحث عن التعليق
    $comment = Comment::findOrFail($comment_id);

    // 2. التحقق من الصلاحيات (فقط صاحب التعليق أو Admin)
    $user = Auth::user();
    if ($comment->user_id !== $user->id && $user->role !== 'admin') {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بحذف هذا التعليق.'
        ], 403);
    }

    // 3. حذف التعليق
    $comment->delete();

    return response()->json([
        'success' => true,
        'message' => 'تم حذف التعليق بنجاح.'
    ]);
}

#====================================================================================================================
#                                      LESSON COMMENTS
#====================================================================================================================

# CREATE - إضافة تعليق على درس
public function storeLessonComment(Request $request, $lesson_id)
{
    $request->validate([
        'content' => 'required|string|min:5|max:500',
    ]);

    // 1. التأكد من وجود الدرس
    $lesson = Lesson::findOrFail($lesson_id);
    
    // 2. إنشاء التعليق
    $comment = Comment::create([
        'lesson_id' => $lesson->id,
        'user_id' => Auth::id(),
        'content' => $request->content,
    ]);

    // 3. إرجاع التعليق مع اسم المستخدم
    return response()->json([
        'success' => true,
        'message' => 'تم إضافة التعليق بنجاح.',
        'data' => $comment->load('user:id,name')
    ], 201);
}

#--------------------------------------------------------------------------------------------------------------------
# READ - عرض جميع التعليقات على درس
public function indexLessonComments($lesson_id)
{
    // العثور على الدرس للتأكد من وجوده
    Lesson::findOrFail($lesson_id); 
    
    // جلب التعليقات للدرس المحدد
    $comments = Comment::where('lesson_id', $lesson_id)
                       ->with('user:id,name') 
                       ->latest()
                       ->paginate(15);

    return response()->json([
        'success' => true,
        'message' => 'تم جلب التعليقات بنجاح',
        'meta' => [
            'current_page' => $comments->currentPage(),
            'last_page' => $comments->lastPage(),
            'per_page' => $comments->perPage(),
            'total' => $comments->total(),
            'from' => $comments->firstItem(),
            'to' => $comments->lastItem(),
        ],
        'data' => $comments->items()
    ]);
}

#--------------------------------------------------------------------------------------------------------------------
# UPDATE - تحديث تعليق على درس
public function updateLessonComment(Request $request, $comment_id)
{
    $request->validate([
        'content' => 'required|string|min:5|max:500',
    ]);

    // 1. البحث عن التعليق
    $comment = Comment::findOrFail($comment_id);

    // 2. التحقق من الصلاحيات (فقط صاحب التعليق)
    $user = Auth::user();
    if ($comment->user_id !== $user->id) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بتعديل هذا التعليق.'
        ], 403);
    }

    // 3. تحديث التعليق
    $comment->update([
        'content' => $request->content
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث التعليق بنجاح.',
        'data' => $comment->load('user:id,name')
    ]);
}

#--------------------------------------------------------------------------------------------------------------------
# DELETE - حذف تعليق على درس
public function destroyLessonComment($comment_id)
{
    // 1. البحث عن التعليق
    $comment = Comment::findOrFail($comment_id);

    // 2. التحقق من الصلاحيات (فقط صاحب التعليق أو Admin)
    $user = Auth::user();
    if ($comment->user_id !== $user->id && $user->role !== 'admin') {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بحذف هذا التعليق.'
        ], 403);
    }

    // 3. حذف التعليق
    $comment->delete();

    return response()->json([
        'success' => true,
        'message' => 'تم حذف التعليق بنجاح.'
    ]);
}

#====================================================================================================================
#                                      SAYING COMMENTS
#====================================================================================================================

# CREATE - إضافة تعليق على قول
public function storeSayingComment(Request $request, $saying_id)
{
    $request->validate([
        'content' => 'required|string|min:5|max:500',
    ]);

    // 1. التأكد من وجود القول
    $saying = Saying::findOrFail($saying_id);
    
    // 2. إنشاء التعليق
    $comment = Comment::create([
        'saying_id' => $saying->id,
        'user_id' => Auth::id(),
        'content' => $request->content,
    ]);

    // 3. إرجاع التعليق مع اسم المستخدم
    return response()->json([
        'success' => true,
        'message' => 'تم إضافة التعليق بنجاح.',
        'data' => $comment->load('user:id,name')
    ], 201);
}

#--------------------------------------------------------------------------------------------------------------------
# READ - عرض جميع التعليقات على قول
public function indexSayingComments($saying_id)
{
    // العثور على القول للتأكد من وجوده
    Saying::findOrFail($saying_id); 
    
    // جلب التعليقات للقول المحدد
    $comments = Comment::where('saying_id', $saying_id)
                       ->with('user:id,name') 
                       ->latest()
                       ->paginate(15);

    return response()->json([
        'success' => true,
        'message' => 'تم جلب التعليقات بنجاح',
        'meta' => [
            'current_page' => $comments->currentPage(),
            'last_page' => $comments->lastPage(),
            'per_page' => $comments->perPage(),
            'total' => $comments->total(),
            'from' => $comments->firstItem(),
            'to' => $comments->lastItem(),
        ],
        'data' => $comments->items()
    ]);
}

#--------------------------------------------------------------------------------------------------------------------
# UPDATE - تحديث تعليق على قول
public function updateSayingComment(Request $request, $comment_id)
{
    $request->validate([
        'content' => 'required|string|min:5|max:500',
    ]);

    // 1. البحث عن التعليق
    $comment = Comment::findOrFail($comment_id);

    // 2. التحقق من الصلاحيات (فقط صاحب التعليق)
    $user = Auth::user();
    if ($comment->user_id !== $user->id) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بتعديل هذا التعليق.'
        ], 403);
    }

    // 3. تحديث التعليق
    $comment->update([
        'content' => $request->content
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث التعليق بنجاح.',
        'data' => $comment->load('user:id,name')
    ]);
}

#--------------------------------------------------------------------------------------------------------------------
# DELETE - حذف تعليق على قول
public function destroySayingComment($comment_id)
{
    // 1. البحث عن التعليق
    $comment = Comment::findOrFail($comment_id);

    // 2. التحقق من الصلاحيات (فقط صاحب التعليق أو Admin)
    $user = Auth::user();
    if ($comment->user_id !== $user->id && $user->role !== 'admin') {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بحذف هذا التعليق.'
        ], 403);
    }

    // 3. حذف التعليق
    $comment->delete();

    return response()->json([
        'success' => true,
        'message' => 'تم حذف التعليق بنجاح.'
    ]);
}
}
