<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Poem;
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
                       ->paginate(10); // تطبيق التصفح

    return response()->json([
        'success' => true,
        'data' => $comments
    ]);
}
}
