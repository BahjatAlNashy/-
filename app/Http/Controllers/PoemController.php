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
            'pdf_source' => 'nullable|file|mimes:pdf|max:10240',
            'audio_source' => 'nullable|file|mimes:mp3,wav,aac,ogg|max:10240',
            'video_source' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200',
            'is_private' => 'boolean', // ✨ قاعدة جديدة: يجب أن يكون منطقياً (True/False) ✨
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
            'is_private'=>$validatedData['is_private']
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
            $fullUrl = url(Storage::url($path));
            // $fullUrl = Storage::url($path); 
            
            return $poem->sources()->create([
                'source_type' => $type,
                'source' => $path,
                'poem_id'=>$poem->id,
                'url' =>$fullUrl, // نضيف الرابط الكامل لنموذج المصدر

                // 'original_name' => $file->getClientOriginalName(),
                // 'size' => $file->getSize()
            ]);
        };
    
        // تخزين المصادر إذا كانت موجودة
        $sources = [];
        
        if ($request->hasFile('pdf_source')) {
            $sources[] = $storeSource('pdf', $request->file('pdf_source'));
        }
        
        if ($request->hasFile('audio_source')) {
            $sources[] = $storeSource('audio', $request->file('audio_source'));
        }
        
        if ($request->hasFile('video_source')) {
            $sources[] = $storeSource('video', $request->file('video_source'));
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
    // 1. التحقق من صحة المدخلات (نفس قواعد التحقق للملفات)
    $validatedData = $request->validate([
        'pdf_source' => 'nullable|file|mimes:pdf|max:10240',
        'audio_source' => 'nullable|file|mimes:mp3,wav,aac,ogg|max:10240',
        'video_source' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200'
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
    if ($user->role !== 'admin' || $poem->user_id !== $user->id) {
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
        $fullUrl = url(Storage::url($path)); 

        return $poem->sources()->create([
            'source_type' => $type,
            'source' => $path,
            'poem_id' => $poem->id,
            'url' => $fullUrl, // حفظ الرابط الكامل
        ]);
    };

    // 5. تخزين المصادر الجديدة
    $sources = [];
    $message = "لم يتم إضافة أي مصدر.";

    if ($request->hasFile('pdf_source')) {
        $sources[] = $storeSource('pdf', $request->file('pdf_source'));
        $message = "تمت إضافة المصادر بنجاح.";
    }
    
    if ($request->hasFile('audio_source')) {
        $sources[] = $storeSource('audio', $request->file('audio_source'));
        $message = "تمت إضافة المصادر بنجاح.";
    }
    
    if ($request->hasFile('video_source')) {
        $sources[] = $storeSource('video', $request->file('video_source'));
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

    #delete
    public function destroy($id)
{
    // 1. العثور على القصيدة
    $poem = Poem::with('sources')->findOrFail($id);
    
    // 2. التحقق من صلاحيات المستخدم
    $user = Auth::user();

    // الشرط: إذا لم يكن المستخدم مديراً AND لم يكن مالك القصيدة
    if ($user->role != 'admin'|| $poem->user_id != $user->id) {
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
        $query = Poem::query();

        // 1. البحث حسب العنوان (Title) والوصف (Description) - بحث جزئي
        // نستخدم where و orWhere في شرط واحد للبحث في كلا الحقلين بنفس القيمة
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            
            $query->where(function ($q) use ($keyword) {
                // البحث في العنوان
                $q->where('title', 'LIKE', '%' . $keyword . '%')
                  // أو البحث في الوصف
                  ->orWhere('description', 'LIKE', '%' . $keyword . '%');
            });
        }
        
        // 2. البحث حسب التاريخ (Saying Date) - الخيارات المرنة
        
        // أ. البحث بالسنة فقط
        if ($request->filled('year')) {
            $query->whereYear('saying_date', $request->input('year'));
        }

        // ب. البحث بالشهر فقط (ضمن أي سنة)
        if ($request->filled('month')) {
            $query->whereMonth('saying_date', $request->input('month'));
        }

        // ج. البحث بتاريخ محدد أو البحث عن تاريخ قبل/بعد تاريخ معين
        if ($request->filled('date')) {
            $date = $request->input('date');
            
            // تحقق من نوع المقارنة المطلوبة
            $comparison = $request->input('date_comparison', '='); // = (مطابقة تامة)، > (بعد)، < (قبل)
            
            // تحقق من أن قيمة المقارنة المدخلة صحيحة لتجنب الأخطاء
            if (in_array($comparison, ['=', '>', '<', '>=', '<='])) {
                 $query->whereDate('saying_date', $comparison, $date);
            } else {
                 // إذا لم يتم تحديد مقارنة صالحة، نعود إلى المطابقة التامة
                 $query->whereDate('saying_date', '=', $date);
            }
        }
        
        // 3. تنفيذ الاستعلام وجلب النتائج
        $poems = $query->with('sources')->get();

        // إرجاع الاستجابة
        return response()->json([
            'success' => true,
            'count' => $poems->count(),
            'message' => 'نتائج البحث عن القصائد',
            'data' => $poems,
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
            
            // الملفات (قابلة للاستبدال أو الإضافة)
            'pdf_source' => 'nullable|file|mimes:pdf|max:10240',
            'audio_source' => 'nullable|file|mimes:mp3,wav,aac,ogg|max:10240',
            'video_source' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200'
        ]);

        // 2. البحث عن القصيدة والتحقق من الصلاحيات
        $poem = Poem::findOrFail($poem_id); 
        $user = Auth::user();

        if ($user->role !== 'admin' || $poem->user_id !== $user->id) {
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

        // 4. دالة مساعدة لمعالجة تحديث المصدر
        $handleSourceUpdate = function ($fileKey, $sourceType) use ($poem, $request) {
            // تحقق: هل قام المستخدم برفع ملف جديد لهذا النوع؟
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                
                // البحث عن مصدر موجود لنفس النوع (pdf, audio, video)
                $existingSource = $poem->sources()->where('source_type', $sourceType)->first();

                // تهيئة المسارات
                $folder = match($sourceType) {
                    'pdf' => 'pdfs',
                    'audio' => 'audios',
                    'video' => 'videos',
                    default => 'others'
                };
                $newPath = $file->store("poems/{$folder}", 'public');
                $fullUrl = url(Storage::url($newPath));

                if ($existingSource) {
                    // أ. استبدال: حذف الملف القديم من التخزين
                    Storage::disk('public')->delete($existingSource->source);

                    // ب. تحديث السجل بمسار الملف الجديد
                    $existingSource->update([
                        'source' => $newPath,
                        'url' => $fullUrl,
                    ]);
                } else {
                    // ج. إضافة جديدة: في حالة لم يكن هناك مصدر سابق وتم رفعه الآن
                    $poem->sources()->create([
                        'source_type' => $sourceType,
                        'source' => $newPath,
                        'url' => $fullUrl,
                        'poem_id' => $poem->id,
                    ]);
                }
            }
        };

        // 5. تطبيق دالة التحديث على جميع أنواع الملفات
        $handleSourceUpdate('pdf_source', 'pdf');
        $handleSourceUpdate('audio_source', 'audio');
        $handleSourceUpdate('video_source', 'video');

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
    $user = Auth::user();
    $user_id = Auth::id();

    // 1. بناء الاستعلام وتطبيق منطق الخصوصية (Private Poems)
    $query = Poem::query();
    
    // منطق الحماية: عرض العامة للجميع، والخاصة فقط للمالك.
    if (Auth::check()) {
        $query->where('is_private', false)
              ->orWhere(function ($q) use ($user_id) {
                  $q->where('is_private', true)
                    ->where('user_id', $user_id);
              });
    } else {
        // المستخدم غير المسجل يرى القصائد العامة فقط
        $query->where('is_private', false);
    }

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

public function show($poem_id)
{
    $user = Auth::user();

    // 1. جلب القصيدة مع العلاقات المطلوبة: المستخدم (المؤلف)، المصادر، والتعليقات (مع مستخدمي التعليقات).
    $poem = Poem::with(['user:id,name', 'sources', 'comments.user:id,name'])->find($poem_id);

    if (!$poem) {
        return response()->json([
            'success' => false,
            'message' => 'القصيدة المطلوبة غير موجودة.'
        ], 404);
    }

    // 2. تطبيق منطق الخصوصية (Privacy Logic)
    if ($poem->is_private) {
        // إذا كانت خاصة، نتحقق أن المستخدم مسجل الدخول وهو مالك القصيدة
        if (!$user || $poem->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'هذه القصيدة خاصة ولا يمكن الوصول إليها.'
            ], 403);
        }
    }

    // 3. استخراج المصادر المحددة (Video, Audio, PDF)
    $getVideoSource = $poem->sources->firstWhere('source_type', 'video');
    $getAudioSource = $poem->sources->firstWhere('source_type', 'audio');
    $getPdfSource = $poem->sources->firstWhere('source_type', 'pdf');
    
    // حساب حالة المفضلة
    $isFavorited = $user ? $poem->isFavoritedBy($user) : false; 

    // 4. تنسيق بيانات التعليقات
    $formattedComments = $poem->comments->map(function($comment) {
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
            'date' => $poem->saying_date, // التاريخ
            
            // المصادر المطلوبة
            'video' => $getVideoSource ? [
                'id' => $getVideoSource->id,
                'url' => $getVideoSource->url,
            ] : null,
            'audio' => $getAudioSource ? [
                'id' => $getAudioSource->id,
                'url' => $getAudioSource->url,
            ] : null,
            'pdf' => $getPdfSource ? [
                'id' => $getPdfSource->id,
                'url' => $getPdfSource->url,
            ] : null,

            'is_favorited' => $isFavorited,
            
            'comments' => $formattedComments,
            'comments_count' => $poem->comments->count(),
            'author_name' => $poem->user->name ?? 'غير معروف',
        ]
    ]);
}
}