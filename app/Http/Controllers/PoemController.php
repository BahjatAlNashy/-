<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poem;
use Illuminate\Support\Facades\Auth;
use App\Models\Source;
use Illuminate\Support\Facades\Storage;


class PoemController extends Controller
{
    //
    public function store (Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255|unique:poems,title',
            'saying_date' => 'nullable|date_format:Y-m-d',
            'description' => 'nullable|string',
            'pdf_source.*' => 'nullable|file|mimes:pdf|max:10240',
            'audio_source.*' => 'nullable|file|mimes:mp3,wav,aac,ogg|max:10240',
            'video_source.*' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200',
            'is_private' => 'boolean',
        ]);
    
        $user = Auth::user();
    
        if ($user->role != 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action!'
            ], 403);
        }
    
        // إنشاء القصيدة
        $poem = Poem::create([
            'title' => $validatedData['title'],
            'saying_date' => $validatedData['saying_date'],
            'description' => $validatedData['description'],
            'user_id' => Auth::user()->id,
            'is_private'=>$validatedData['is_private'] ?? false,
        ]);
    
        // دالة مساعدة لتخزين المصادر
        $storeSource = function ($type, $file) use ($poem) {
            if (!$file) return null;
            
            $folder = match($type) {
                'pdf' => 'pdfs',
                'audio' => 'audios',
                'video' => 'videos',
                default => 'others'
            };
            
            $path = $file->store("poems/{$folder}", 'public');
            // $fullUrl = url(Storage::url($path));
            $fullUrl = Storage::url($path); 
            
            return $poem->sources()->create([
                'source_type' => $type,
                'source' => $path,
                'poem_id'=>$poem->id,
                'url' =>$fullUrl, // نضيف الرابط الكامل لنموذج المصدر

                // 'original_name' => $file->getClientOriginalName(),
                // 'size' => $file->getSize()
            ]);
        };
    
        // تخزين المصادر إذا كانت موجودة (دعم ملفات متعددة)
        $sources = [];
        
        if ($request->hasFile('pdf_source')) {
            $pdfFiles = is_array($request->file('pdf_source')) ? $request->file('pdf_source') : [$request->file('pdf_source')];
            foreach ($pdfFiles as $pdfFile) {
                $sources[] = $storeSource('pdf', $pdfFile);
            }
        }
        
        if ($request->hasFile('audio_source')) {
            $audioFiles = is_array($request->file('audio_source')) ? $request->file('audio_source') : [$request->file('audio_source')];
            foreach ($audioFiles as $audioFile) {
                $sources[] = $storeSource('audio', $audioFile);
            }
        }
        
        if ($request->hasFile('video_source')) {
            $videoFiles = is_array($request->file('video_source')) ? $request->file('video_source') : [$request->file('video_source')];
            foreach ($videoFiles as $videoFile) {
                $sources[] = $storeSource('video', $videoFile);
            }
        }
    
        // تحميل العلاقة مع المصادر
        $poem->load('sources');
    
        // إرجاع الاستجابة
        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء القصيدة بنجاح',
            'data' => [
                'poem' => $poem,
                // 'sources' => $poem->sources
            ]
        ], 201);
    }
  

    #AddSourcePoem>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\

    public function AddSourcePoem (Request $request, $poem_id)
{
    // 1. التحقق من صحة المدخلات (دعم ملفات متعددة)
    $validatedData = $request->validate([
        'pdf_source.*' => 'nullable|file|mimes:pdf|max:10240',
        'audio_source.*' => 'nullable|file|mimes:mp3,wav,aac,ogg|max:10240',
        'video_source.*' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200'
    ]);

    // 2. البحث عن القصيدة
    $poem = Poem::find($poem_id);

    if (!$poem) {
        return response()->json([
            'success' => false,
            'message' => 'القصيدة المطلوبة غير موجودة.'
        ], 404);
    }

    // 3. التحقق من صلاحيات المستخدم (يجب أن يكون مديرًا أو مالكًا للقصيدة)
    $user = Auth::user();
    
    // إذا لم يكن المستخدم مديراً ولم يكن مالكاً للقصيدة، يتم الرفض.
    if ($user->role !== 'admin' && $poem->user_id !== $user->id) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بإضافة مصادر لهذه القصيدة.'
        ], 403);
    }

    // 4. دالة مساعدة لتخزين وربط المصادر (تم تعديلها لتستخدم الدالة url())
    $storeSource = function ($type, $file) use ($poem) {
        if (!$file) return null;
        
        $folder = match($type) {
            'pdf' => 'pdfs',
            'audio' => 'audios',
            'video' => 'videos',
            default => 'others'
        };
        
        $path = $file->store("poems/{$folder}", 'public');
        
        // إنشاء الرابط الكامل باستخدام url()
        // $fullUrl = url(Storage::url($path)); 
        $fullUrl = Storage::url($path); 


        return $poem->sources()->create([
            'source_type' => $type,
            'source' => $path,
            'poem_id' => $poem->id,
            'url' => $fullUrl, // حفظ الرابط الكامل
        ]);
    };

    // 5. تخزين المصادر الجديدة (دعم ملفات متعددة)
    $sources = [];
    $message = "لم يتم إضافة أي مصدر.";

    if ($request->hasFile('pdf_source')) {
        $pdfFiles = is_array($request->file('pdf_source')) ? $request->file('pdf_source') : [$request->file('pdf_source')];
        foreach ($pdfFiles as $pdfFile) {
            $sources[] = $storeSource('pdf', $pdfFile);
        }
        $message = "تمت إضافة المصادر بنجاح.";
    }
    
    if ($request->hasFile('audio_source')) {
        $audioFiles = is_array($request->file('audio_source')) ? $request->file('audio_source') : [$request->file('audio_source')];
        foreach ($audioFiles as $audioFile) {
            $sources[] = $storeSource('audio', $audioFile);
        }
        $message = "تمت إضافة المصادر بنجاح.";
    }
    
    if ($request->hasFile('video_source')) {
        $videoFiles = is_array($request->file('video_source')) ? $request->file('video_source') : [$request->file('video_source')];
        foreach ($videoFiles as $videoFile) {
            $sources[] = $storeSource('video', $videoFile);
        }
        $message = "تمت إضافة المصادر بنجاح.";
    }

    // 6. تحميل المصادر الأخيرة للقصيدة وإرجاع الاستجابة
    $poem->load('sources');

    return response()->json([
        'success' => true,
        'message' => $message,
        'data' => [
            'poem' => $poem
        ]
    ], 201);
}

################################################################################################################

public function toggleFavorite($poem_id)
{
    $poem = Poem::findOrFail($poem_id);
    $user = Auth::user();

    // دالة toggle تقوم بإضافة المفتاح إذا لم يكن موجوداً أو إزالته إذا كان موجوداً
    $user->favoritePoems()->toggle($poem->id);

    // التحقق من حالة التفضيل بعد العملية
    $isFavorited = $user->favoritePoems()->where('poem_id', $poem->id)->exists();

    $message = $isFavorited 
        ? 'تمت إضافة القصيدة إلى المفضلة.' 
        : 'تمت إزالة القصيدة من المفضلة.';

    return response()->json([
        'success' => true,
        'message' => $message,
        'is_favorited' => $isFavorited,
    ]);
}

#____________________________________________________________________________________________________________

    #deleteSource - حذف ملف مصدر واحد محدد
    public function deleteSource($source_id)
    {
        // 1. البحث عن المصدر
        $source = Source::findOrFail($source_id);
        
        // 2. جلب القصيدة المرتبطة
        $poem = Poem::findOrFail($source->poem_id);
        
        // 3. التحقق من صلاحيات المستخدم
        $user = Auth::user();
        
        if ($user->role != 'admin' && $poem->user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا المصدر.'
            ], 403);
        }
        
        // 4. حذف الملف من التخزين
        Storage::disk('public')->delete($source->source);
        
        // 5. حذف السجل من قاعدة البيانات
        $source->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف المصدر بنجاح.'
        ]);
    }

    #delete
    public function destroy($id)
{
    // 1. العثور على القصيدة
    $poem = Poem::with('sources')->findOrFail($id);
    
    // 2. التحقق من صلاحيات المستخدم
    $user = Auth::user();

    // الشرط: إذا لم يكن المستخدم مديراً AND لم يكن مالك القصيدة
    if ($user->role != 'admin' && $poem->user_id != $user->id) {
        // ✨ هذا السطر هو مفتاح إيقاف التنفيذ! ✨
        return response()->json([ 
            'success' => false,
            'message' => 'غير مصرح لك بحذف هذه القصيدة.'
        ], 403);
    }
    
    // 3. حذف جميع الملفات من التخزين أولاً
    foreach ($poem->sources as $source) {
        Storage::disk('public')->delete($source->source);
    }

    // 4. حذف سجل القصيدة (والسجلات المرتبطة بها تلقائياً)
    $poem->delete();
    
    // 5. إرجاع استجابة النجاح
    return response()->json([
        'success' => true,
        'message' => 'تم حذف القصيدة وجميع مصادرها بنجاح.'
    ]);
}
#SEARCH SEARCH SEARCH SEARCH SEARCH SEARCH SEARCH SEARCH SEARCH SEARCH SEARCH SEARCH SEARCH SEARCH 

public function search(Request $request)
{
    // 💡 تحميل المستخدم من التوكن إذا كان موجوداً (المصادقة الاختيارية)
    if (!Auth::check() && $request->bearerToken()) {
        $userFromToken = Auth::guard('sanctum')->user();
        if ($userFromToken) {
            Auth::login($userFromToken);
        }
    }
    
    $user = Auth::user();
    $query = Poem::query();

    // 0. تطبيق منطق الخصوصية (Private Poems)
    if (!Auth::check()) {
        // الزائر (غير مسجل): يرى القصائد العامة فقط
        $query->where('is_private', false);
    }
    // المسجلين: يرون كل شيء (عام + خاص) - لا حاجة لفلتر

    // 1. البحث حسب العنوان والوصف
    if ($request->filled('keyword')) {
        $keyword = $request->input('keyword');
        
        $query->where(function ($q) use ($keyword) {
            $q->where('title', 'LIKE', '%' . $keyword . '%')
              ->orWhere('description', 'LIKE', '%' . $keyword . '%');
        });
    }

    // 2. البحث حسب التاريخ
    if ($request->filled('year')) {
        $query->whereYear('saying_date', $request->input('year'));
    }

    if ($request->filled('month')) {
        $query->whereMonth('saying_date', $request->input('month'));
    }

    if ($request->filled('date')) {
        $date = $request->input('date');
        $comparison = $request->input('date_comparison', '=');
        
        if (in_array($comparison, ['=', '>', '<', '>=', '<='])) {
             $query->whereDate('saying_date', $comparison, $date);
        } else {
             $query->whereDate('saying_date', '=', $date);
        }
    }

    // 3. الفلترة حسب نوع المصدر (Source Type)
    if ($request->filled('source_type')) {
        $sourceType = $request->input('source_type');
        
        // التحقق من أن نوع المصدر المدخل هو ضمن الأنواع المسموح بها
        $allowedTypes = ['pdf', 'audio', 'video'];
        
        if (in_array(strtolower($sourceType), $allowedTypes)) {
            // استخدام whereHas للفلترة بناءً على حقل في العلاقة (Sources)
            $query->whereHas('sources', function ($q) use ($sourceType) {
                $q->where('source_type', $sourceType);
            });
        }
    }
    
    // 4. التحميل المبكر للعلاقات
    $query->with(['sources', 'favorites']);

    // 5. تطبيق الترتيب والتصفح (Pagination)
    $poems = $query->orderBy('saying_date', 'desc')->paginate(15);

    // 6. تنسيق البيانات بنفس طريقة index
    $formattedPoems = $poems->through(function ($poem) use ($user) {
        // حالة وجود المصادر
        $hasPdf = $poem->sources->contains('source_type', 'pdf');
        $hasAudio = $poem->sources->contains('source_type', 'audio');
        $hasVideo = $poem->sources->contains('source_type', 'video');
        
        // حالة المفضلة
        $isFavorited = false;
        if ($user) {
            $isFavorited = $poem->isFavoritedBy($user);
        }

        return [
            'id' => $poem->id,
            'title' => $poem->title,
            'date' => $poem->saying_date,
            
            // حالة وجود الملفات
            'has_pdf' => $hasPdf,
            'has_audio' => $hasAudio,
            'has_video' => $hasVideo,
            
            // حالة المفضلة
            'is_favorited' => $isFavorited,
        ];
    })->items();

    // 7. إرجاع الاستجابة مع معلومات التصفح
    return response()->json([
        'success' => true,
        'message' => 'نتائج البحث عن القصائد',
        // بيانات التصفح
        'meta' => [
            'page_number' => $poems->currentPage(),
            'total_pages' => $poems->lastPage(),
            'has_previous' => $poems->currentPage() > 1,
            'has_next' => $poems->hasMorePages(),
            'total_items' => $poems->total(),
        ],
        'data' => $formattedPoems,
    ]);
}

    #UPDATE UPDATE UPDATE UPDATE UPDATE UPDATE UPDATE UPDATE UPDATE UPDATE UPDATE UPDATE UPDATE UPDATE UPDATE

    public function update(Request $request, $poem_id)
    {
        // 1. التحقق من صحة المدخلات
        $validatedData = $request->validate([
            // تحديث البيانات النصية
            'title' => 'required|string|max:255|unique:poems,title,' . $poem_id, 
            'saying_date' => 'nullable|date_format:Y-m-d',
            'description' => 'nullable|string',
            
            // الملفات (إضافة ملفات جديدة دون حذف القديمة)
            'pdf_source.*' => 'nullable|file|mimes:pdf|max:10240',
            'audio_source.*' => 'nullable|file|mimes:mp3,wav,aac,ogg|max:10240',
            'video_source.*' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200'
        ]);

        // 2. البحث عن القصيدة والتحقق من الصلاحيات
        $poem = Poem::findOrFail($poem_id); 
        $user = Auth::user();

        if ($user->role !== 'admin' && $poem->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذه القصيدة.'
            ], 403);
        }

        // 3. تحديث البيانات النصية أولاً
        $poem->update([
            'title' => $validatedData['title'],
            'saying_date' => $validatedData['saying_date'] ?? $poem->saying_date, 
            'description' => $validatedData['description'] ?? $poem->description,
        ]);

        // 4. دالة مساعدة لإضافة مصادر جديدة (بدون حذف القديمة)
        $storeSource = function ($type, $file) use ($poem) {
            if (!$file) return null;
            
            $folder = match($type) {
                'pdf' => 'pdfs',
                'audio' => 'audios',
                'video' => 'videos',
                default => 'others'
            };
            
            $path = $file->store("poems/{$folder}", 'public');
            $fullUrl = Storage::url($path); 
            
            return $poem->sources()->create([
                'source_type' => $type,
                'source' => $path,
                'poem_id' => $poem->id,
                'url' => $fullUrl,
            ]);
        };

        // 5. إضافة ملفات جديدة (دعم ملفات متعددة)
        if ($request->hasFile('pdf_source')) {
            $pdfFiles = is_array($request->file('pdf_source')) ? $request->file('pdf_source') : [$request->file('pdf_source')];
            foreach ($pdfFiles as $pdfFile) {
                $storeSource('pdf', $pdfFile);
            }
        }
        
        if ($request->hasFile('audio_source')) {
            $audioFiles = is_array($request->file('audio_source')) ? $request->file('audio_source') : [$request->file('audio_source')];
            foreach ($audioFiles as $audioFile) {
                $storeSource('audio', $audioFile);
            }
        }
        
        if ($request->hasFile('video_source')) {
            $videoFiles = is_array($request->file('video_source')) ? $request->file('video_source') : [$request->file('video_source')];
            foreach ($videoFiles as $videoFile) {
                $storeSource('video', $videoFile);
            }
        }

        // 6. إرجاع الاستجابة
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث القصيدة وملفاتها بنجاح.',
            'data' => [
                'poem' => $poem->load('sources') // أعد التحميل لعرض جميع المصادر المحدثة
            ]
        ], 200);
    }


    #عرض ملخص للقصائد

    public function index(Request $request)
{
    // 💡 الحل: محاولة تحميل المستخدم من التوكن يدوياً (المصادقة الاختيارية)
    // إذا لم يكن هناك مستخدم مسجل الدخول (Auth::check() == false)
    // ولكن الطلب يحتوي على Bearer Token، نحاول تحميل المستخدم عبر Guard 'sanctum'.
    if (!Auth::check() && $request->bearerToken()) {
        $userFromToken = Auth::guard('sanctum')->user();
        
        // إذا نجح التحميل، نقوم بتسجيل دخول المستخدم مؤقتاً في الجلسة الحالية
        if ($userFromToken) {
             Auth::login($userFromToken);
        }
    }
    $user = Auth::user();
    $user_id = Auth::id();

    // 1. بناء الاستعلام وتطبيق منطق الخصوصية (Private Poems)
    $query = Poem::query();
    
    // منطق الحماية: المسجلين يرون الكل، الزوار يرون العامة فقط
    if (!Auth::check()) {
        // الزائر (غير مسجل): يرى القصائد العامة فقط
        $query->where('is_private', false);
    }
    // المسجلين: يرون كل شيء (عام + خاص) - لا حاجة لفلتر

    // 2. التحميل المبكر للعلاقات
    $query->with(['sources', 'favorites']); 

    // 3. تطبيق التصفح (Pagination)
    // نرتب حسب التاريخ الأحدث أولاً، ثم نطبق التصفح
    $poems = $query->orderBy('saying_date', 'desc')->paginate(15); 

    // 4. تنسيق البيانات لإضافة الحقول المطلوبة وحالة المفضلة
    $formattedPoems = $poems->through(function ($poem) use ($user) {
        
        // حالة وجود المصادر
        $hasPdf = $poem->sources->contains('source_type', 'pdf');
        $hasAudio = $poem->sources->contains('source_type', 'audio');
        $hasVideo = $poem->sources->contains('source_type', 'video');
        
        // حالة المفضلة (is_favorited)
        $isFavorited = false;
        if ($user) {
            $isFavorited = $poem->isFavoritedBy($user); 
        }

        return [
            'id' => $poem->id,
            'title' => $poem->title,
            'date' => $poem->saying_date, // تم تغيير الاسم إلى 'date' كما طلب
            
            // حالة وجود الملفات
            'has_pdf' => $hasPdf,
            'has_audio' => $hasAudio,
            'has_video' => $hasVideo,
            
            // حالة المفضلة المطلوبة
            'is_favorited' => $isFavorited,
        ];
    })->items(); // استخدام items() للحصول على مصفوفة البيانات فقط

    // 5. إرجاع الاستجابة المنسقة (فصل البيانات عن الميتا)
    return response()->json([
        'success' => true,
        'message' => 'تم جلب قائمة القصائد بنجاح.',
        // بيانات التصفح المطلوبة للواجهة الأمامية
        'meta' => [
            'page_number' => $poems->currentPage(),
            'total_pages' => $poems->lastPage(),
            'has_previous' => $poems->currentPage() > 1, 
            'has_next' => $poems->hasMorePages(), 
            'total_items' => $poems->total(),
        ],
        'data' => $formattedPoems
    ]);
}

public function show(Request $request, $poem_id)
{
    // 💡 تحميل المستخدم من التوكن إذا كان موجوداً (المصادقة الاختيارية)
    if (!Auth::check() && $request->bearerToken()) {
        $userFromToken = Auth::guard('sanctum')->user();
        if ($userFromToken) {
            Auth::login($userFromToken);
        }
    }
    
    $user = Auth::user();

    // 1. جلب القصيدة مع العلاقات المطلوبة: المستخدم (المؤلف)، المصادر، والتعليقات (مع مستخدمي التعليقات).
    $poem = Poem::with(['user:id,name', 'sources', 'comments.user:id,name', 'favorites'])->find($poem_id);

    if (!$poem) {
        return response()->json([
            'success' => false,
            'message' => 'القصيدة المطلوبة غير موجودة.'
        ], 404);
    }

    // 2. تطبيق منطق الخصوصية (Privacy Logic)
    if ($poem->is_private) {
        // إذا كانت خاصة، نتحقق أن المستخدم مسجل الدخول
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'هذه القصيدة خاصة، يجب تسجيل الدخول للوصول إليها.'
            ], 403);
        }
    }

    // 3. استخراج المصادر المحددة (Video, Audio, PDF) - دعم ملفات متعددة
    $videoSources = $poem->sources->where('source_type', 'video');
    $audioSources = $poem->sources->where('source_type', 'audio');
    $pdfSources = $poem->sources->where('source_type', 'pdf');
    
    // حساب حالة المفضلة بشكل صحيح
    $isFavorited = false;
    if ($user) {
        $isFavorited = $poem->isFavoritedBy($user);
    }

    // 4. جلب التعليقات مع Pagination
    $page = request()->input('page', 1);
    $comments = $poem->comments()
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

    // 5. إرجاع الاستجابة المنسقة
    return response()->json([
        'success' => true,
        'message' => 'تم جلب تفاصيل القصيدة بنجاح.',
        'data' => [
            'id' => $poem->id,
            'title' => $poem->title,
            'description' => $poem->description,
            'date' => $poem->saying_date,
            
            // المصادر المطلوبة (مصفوفات لدعم ملفات متعددة)
            'videos' => $videoSources->map(function($source) {
                return [
                    'id' => $source->id,
                    'url' => $source->url,
                ];
            })->values(),
            'audios' => $audioSources->map(function($source) {
                return [
                    'id' => $source->id,
                    'url' => $source->url,
                ];
            })->values(),
            'pdfs' => $pdfSources->map(function($source) {
                return [
                    'id' => $source->id,
                    'url' => $source->url,
                ];
            })->values(),

            'is_favorited' => $isFavorited,
            'author_name' => $poem->user->name ?? 'غير معروف',
            
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

#getFavoritePoems - عرض جميع القصائد المفضلة للمستخدم
public function getFavoritePoems()
{
    $user = Auth::user();
    
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'يجب تسجيل الدخول أولاً.'
        ], 401);
    }

    // جلب القصائد المفضلة مع العلاقات والتصفح (Pagination)
    $favoritePoems = $user->favoritePoems()
        ->with(['sources', 'user:id,name'])
        ->orderBy('created_at', 'desc')
        ->paginate(15);

    // تنسيق البيانات بنفس طريقة index
    $formattedPoems = $favoritePoems->through(function($poem) {
        return [
            'id' => $poem->id,
            'title' => $poem->title,
            'date' => $poem->saying_date,
            
            // حالة وجود المصادر
            'has_pdf' => $poem->sources->contains('source_type', 'pdf'),
            'has_audio' => $poem->sources->contains('source_type', 'audio'),
            'has_video' => $poem->sources->contains('source_type', 'video'),
            
            'is_favorited' => true, // دائماً true لأنها في المفضلة
        ];
    })->items();

    return response()->json([
        'success' => true,
        'message' => 'تم جلب القصائد المفضلة بنجاح.',
        // بيانات التصفح
        'meta' => [
            'page_number' => $favoritePoems->currentPage(),
            'total_pages' => $favoritePoems->lastPage(),
            'has_previous' => $favoritePoems->currentPage() > 1,
            'has_next' => $favoritePoems->hasMorePages(),
            'total_items' => $favoritePoems->total(),
        ],
        'data' => $formattedPoems
    ]);
}
}