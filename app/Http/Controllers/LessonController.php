<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\Source;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    //
    /**
     * يعرض قائمة الدروس مع منطق المصادقة الاختيارية (Optional Authentication) للتحقق من حالة المفضلة.
     */
    public function index(Request $request)
    {
        // 💡 المصادقة الاختيارية: محاولة تحميل المستخدم من التوكن يدوياً إذا كان موجوداً
        if (!Auth::check() && $request->bearerToken()) {
            $userFromToken = Auth::guard('sanctum')->user();
            
            if ($userFromToken) {
                 Auth::login($userFromToken);
            }
        }
        
        $user = Auth::user();
        $user_id = Auth::id();

        // 1. بناء الاستعلام وتطبيق منطق الخصوصية (Private Lessons)
        $query = Lesson::query();
        
        // منطق الحماية: المسجلين يرون الكل، الزوار يرون العامة فقط
        if (!Auth::check()) {
            // الزائر (غير مسجل): يرى الدروس العامة فقط
            $query->where('is_private', false);
        }
        // المسجلين: يرون كل شيء (عام + خاص) - لا حاجة لفلتر

        // 2. التحميل المبكر للعلاقات
        // نستخدم 'favoritedBy' بدلاً من 'favorites' وفقاً لاسم العلاقة في نموذج Lesson المقدم
        $query->with(['sources', 'favoritedBy']); 

        // 3. تطبيق التصفح (Pagination)
        $lessons = $query->orderBy('saying_date', 'desc')->paginate(15); 

        // 4. تنسيق البيانات لإضافة الحقول المطلوبة وحالة المفضلة
        $formattedLessons = $lessons->through(function ($lesson) use ($user) {
            
            // حالة وجود المصادر
            $hasPdf = $lesson->sources->contains('source_type', 'pdf');
            $hasAudio = $lesson->sources->contains('source_type', 'audio');
            $hasVideo = $lesson->sources->contains('source_type', 'video');
            
            // حالة المفضلة (is_favorited) - تستخدم $user الذي تم تعيينه اختياريًا
            $isFavorited = false;
            if ($user) {
                $isFavorited = $lesson->isFavoritedBy($user); 
            }

            return [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'date' => $lesson->saying_date, 
                
                // حالة وجود الملفات
                'has_pdf' => $hasPdf,
                'has_audio' => $hasAudio,
                'has_video' => $hasVideo,
                
                // حالة المفضلة المطلوبة
                'is_favorited' => $isFavorited,
            ];
        })->items(); 

        // 5. إرجاع الاستجابة المنسقة 
        return response()->json([
            'success' => true,
            'message' => 'تم جلب قائمة الدروس بنجاح.',
            // بيانات التصفح المطلوبة للواجهة الأمامية
            'meta' => [
                'page_number' => $lessons->currentPage(),
                'total_pages' => $lessons->lastPage(),
                'has_previous' => $lessons->currentPage() > 1, 
                'has_next' => $lessons->hasMorePages(), 
                'total_items' => $lessons->total(),
            ],
            'data' => $formattedLessons
        ]);
    }

    /**
     * يعرض تفاصيل درس محدد.
     */
    public function show(Request $request, $lesson_id)
    {
        // 💡 تحميل المستخدم من التوكن إذا كان موجوداً (المصادقة الاختيارية)
        if (!Auth::check() && $request->bearerToken()) {
            $userFromToken = Auth::guard('sanctum')->user();
            if ($userFromToken) {
                Auth::login($userFromToken);
            }
        }
        
        $user = Auth::user();

        // 1. جلب الدرس مع العلاقات المطلوبة
        $lesson = Lesson::with(['user:id,name', 'sources', 'comments.user:id,name', 'favoritedBy'])->find($lesson_id);

        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'الدرس المطلوب غير موجود.'
            ], 404);
        }

        // 2. تطبيق منطق الخصوصية (Privacy Logic)
        if ($lesson->is_private) {
            // إذا كان خاص، نتحقق أن المستخدم مسجل الدخول
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الدرس خاص، يجب تسجيل الدخول للوصول إليه.'
                ], 403);
            }
        }

        // 3. استخراج المصادر (دعم ملفات متعددة) وحساب حالة المفضلة
        $videoSources = $lesson->sources->where('source_type', 'video');
        $audioSources = $lesson->sources->where('source_type', 'audio');
        $pdfSources = $lesson->sources->where('source_type', 'pdf');
        
        // حساب حالة المفضلة بشكل صحيح
        $isFavorited = false;
        if ($user) {
            $isFavorited = $lesson->isFavoritedBy($user);
        } 

        // 4. جلب التعليقات مع Pagination
        $page = request()->input('page', 1);
        $comments = $lesson->comments()
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
            'message' => 'تم جلب تفاصيل الدرس بنجاح.',
            'data' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'description' => $lesson->description,
                'date' => $lesson->saying_date, 

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
                'author_name' => $lesson->user->name ?? 'غير معروف',
                
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
     * ينشئ درساً جديداً ويخزن ملفات المصادر (متاح فقط للمستخدمين ذوي دور 'admin').
     * دعم الملفات المتعددة
     */
    public function store (Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255|unique:lessons,title', 
            'saying_date' => 'nullable|date_format:Y-m-d',
            'description' => 'nullable|string',
            'pdf_source.*' => 'nullable|file|mimes:pdf|max:10240',
            'audio_source.*' => 'nullable|file|mimes:mp3,wav,aac,ogg|max:10240',
            'video_source.*' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200',
            'is_private' => 'boolean', 
        ]);
    
        $user = Auth::user();
    
        // 1. التحقق من صلاحيات المدير
        if (!$user || $user->role != 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized action!'], 403);
        }
    
        // 2. إنشاء الدرس
        $lesson = Lesson::create([
            'title' => $validatedData['title'],
            'saying_date' => $validatedData['saying_date'] ?? null,
            'description' => $validatedData['description'] ?? null,
            'user_id' => Auth::user()->id,
            'is_private'=> $validatedData['is_private'] ?? false,
        ]);
    
        // 3. دالة مساعدة لتخزين المصادر
        $storeSource = function ($type, $file) use ($lesson) {
            if (!$file) return null;
            
            $folder = match($type) {
                'pdf' => 'pdfs',
                'audio' => 'audios',
                'video' => 'videos',
                default => 'others'
            };
            
            $path = $file->store("lessons/{$folder}", 'public');
            $publicSlug = Storage::url($path); 
            
            return $lesson->sources()->create([
                'source_type' => $type,
                'source' => $path,        
                'lesson_id'=>$lesson->id, 
                'url' => $publicSlug,     
            ]);
        };
    
        // 4. تخزين المصادر (دعم ملفات متعددة)
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
    
        // 5. إرجاع الاستجابة
        $lesson->load('sources');
    
        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الدرس بنجاح',
            'data' => ['lesson' => $lesson]
        ], 201);
    }

    
    #AddSourceLesson - إضافة مصادر لدرس موجود
    public function AddSourceLesson (Request $request, $lesson_id)
    {
        // 1. التحقق من صحة المدخلات (دعم ملفات متعددة)
        $validatedData = $request->validate([
            'pdf_source.*' => 'nullable|file|mimes:pdf|max:10240',
            'audio_source.*' => 'nullable|file|mimes:mp3,wav,aac,ogg|max:10240',
            'video_source.*' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200'
        ]);

        // 2. البحث عن الدرس
        $lesson = Lesson::find($lesson_id);

        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'الدرس المطلوب غير موجود.'
            ], 404);
        }

        // 3. التحقق من صلاحيات المستخدم
        $user = Auth::user();
        
        if ($user->role !== 'admin' && $lesson->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإضافة مصادر لهذا الدرس.'
            ], 403);
        }

        // 4. دالة مساعدة لتخزين وربط المصادر
        $storeSource = function ($type, $file) use ($lesson) {
            if (!$file) return null;
            
            $folder = match($type) {
                'pdf' => 'pdfs',
                'audio' => 'audios',
                'video' => 'videos',
                default => 'others'
            };
            
            $path = $file->store("lessons/{$folder}", 'public');
            $fullUrl = Storage::url($path); 

            return $lesson->sources()->create([
                'source_type' => $type,
                'source' => $path,
                'lesson_id' => $lesson->id,
                'url' => $fullUrl,
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

        // 6. تحميل المصادر الأخيرة للدرس وإرجاع الاستجابة
        $lesson->load('sources');

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'lesson' => $lesson
            ]
        ], 201);
    }

    /**
     * يضيف أو يزيل درساً من قائمة المفضلة للمستخدم الحالي.
     */
    public function toggleFavorite($lesson_id)
    {
        $lesson = Lesson::findOrFail($lesson_id);
        $user = Auth::user();

        // دالة toggle تقوم بإضافة المفتاح إذا لم يكن موجوداً أو إزالته إذا كان موجوداً
        $user->favoriteLessons()->toggle($lesson->id);

        // التحقق من حالة التفضيل بعد العملية
        $isFavorited = $user->favoriteLessons()->where('lesson_id', $lesson->id)->exists();

        $message = $isFavorited 
            ? 'تمت إضافة الدرس إلى المفضلة.' 
            : 'تمت إزالة الدرس من المفضلة.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorited' => $isFavorited,
        ]);
    }

    #delete
    public function destroy($id)
    {
        // 1. العثور على الدرس
        $lesson = Lesson::with('sources')->findOrFail($id);
        
        // 2. التحقق من صلاحيات المستخدم
        $user = Auth::user();

        // الشرط: إذا لم يكن المستخدم مديراً AND لم يكن مالك الدرس
        if ($user->role != 'admin' && $lesson->user_id != $user->id) {
            return response()->json([ 
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا الدرس.'
            ], 403);
        }
        
        // 3. حذف جميع الملفات من التخزين أولاً
        foreach ($lesson->sources as $source) {
            Storage::disk('public')->delete($source->source);
        }

        // 4. حذف سجل الدرس (والسجلات المرتبطة بها تلقائياً)
        $lesson->delete();
        
        // 5. إرجاع استجابة النجاح
        return response()->json([
            'success' => true,
            'message' => 'تم حذف الدرس وجميع مصادره بنجاح.'
        ]);
    }

    #deleteSource - حذف ملف مصدر واحد محدد
    public function deleteSource($source_id)
    {
        // 1. البحث عن المصدر
        $source = Source::findOrFail($source_id);
        
        // 2. جلب الدرس المرتبط
        $lesson = Lesson::findOrFail($source->lesson_id);
        
        // 3. التحقق من صلاحيات المستخدم
        $user = Auth::user();
        
        if ($user->role != 'admin' && $lesson->user_id != $user->id) {
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

    #SEARCH
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
        $query = Lesson::query();

        // 0. تطبيق منطق الخصوصية (Private Lessons)
        if (!Auth::check()) {
            // الزائر (غير مسجل): يرى الدروس العامة فقط
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

        // 3. الفلترة حسب نوع المصدر
        if ($request->filled('source_type')) {
            $sourceType = $request->input('source_type');
            
            $allowedTypes = ['pdf', 'audio', 'video'];
            
            if (in_array(strtolower($sourceType), $allowedTypes)) {
                $query->whereHas('sources', function ($q) use ($sourceType) {
                    $q->where('source_type', $sourceType);
                });
            }
        }
        
        // 4. التحميل المبكر للعلاقات
        $query->with(['sources', 'favoritedBy']);

        // 5. تطبيق الترتيب والتصفح (Pagination)
        $lessons = $query->orderBy('saying_date', 'desc')->paginate(15);

        // 6. تنسيق البيانات بنفس طريقة index
        $formattedLessons = $lessons->through(function ($lesson) use ($user) {
            // حالة وجود المصادر
            $hasPdf = $lesson->sources->contains('source_type', 'pdf');
            $hasAudio = $lesson->sources->contains('source_type', 'audio');
            $hasVideo = $lesson->sources->contains('source_type', 'video');
            
            // حالة المفضلة
            $isFavorited = false;
            if ($user) {
                $isFavorited = $lesson->isFavoritedBy($user);
            }

            return [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'date' => $lesson->saying_date,
                
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
            'message' => 'نتائج البحث عن الدروس',
            // بيانات التصفح
            'meta' => [
                'page_number' => $lessons->currentPage(),
                'total_pages' => $lessons->lastPage(),
                'has_previous' => $lessons->currentPage() > 1,
                'has_next' => $lessons->hasMorePages(),
                'total_items' => $lessons->total(),
            ],
            'data' => $formattedLessons,
        ]);
    }

    #UPDATE
    public function update(Request $request, $lesson_id)
    {
        // 1. التحقق من صحة المدخلات
        $validatedData = $request->validate([
            // تحديث البيانات النصية
            'title' => 'required|string|max:255|unique:lessons,title,' . $lesson_id, 
            'saying_date' => 'nullable|date_format:Y-m-d',
            'description' => 'nullable|string',
            
            // الملفات (إضافة ملفات جديدة دون حذف القديمة)
            'pdf_source.*' => 'nullable|file|mimes:pdf|max:10240',
            'audio_source.*' => 'nullable|file|mimes:mp3,wav,aac,ogg|max:10240',
            'video_source.*' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200'
        ]);

        // 2. البحث عن الدرس والتحقق من الصلاحيات
        $lesson = Lesson::findOrFail($lesson_id); 
        $user = Auth::user();

        if ($user->role !== 'admin' && $lesson->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا الدرس.'
            ], 403);
        }

        // 3. تحديث البيانات النصية أولاً
        $lesson->update([
            'title' => $validatedData['title'],
            'saying_date' => $validatedData['saying_date'] ?? $lesson->saying_date, 
            'description' => $validatedData['description'] ?? $lesson->description,
        ]);

        // 4. دالة مساعدة لإضافة مصادر جديدة (بدون حذف القديمة)
        $storeSource = function ($type, $file) use ($lesson) {
            if (!$file) return null;
            
            $folder = match($type) {
                'pdf' => 'pdfs',
                'audio' => 'audios',
                'video' => 'videos',
                default => 'others'
            };
            
            $path = $file->store("lessons/{$folder}", 'public');
            $fullUrl = Storage::url($path); 
            
            return $lesson->sources()->create([
                'source_type' => $type,
                'source' => $path,
                'lesson_id' => $lesson->id,
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
            'message' => 'تم تحديث الدرس وملفاته بنجاح.',
            'data' => [
                'lesson' => $lesson->load('sources')
            ]
        ], 200);
    }

    #getFavoriteLessons - عرض جميع الدروس المفضلة للمستخدم
    public function getFavoriteLessons()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً.'
            ], 401);
        }

        // جلب الدروس المفضلة مع العلاقات والتصفح (Pagination)
        $favoriteLessons = $user->favoriteLessons()
            ->with(['sources', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // تنسيق البيانات بنفس طريقة index
        $formattedLessons = $favoriteLessons->through(function($lesson) {
            return [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'date' => $lesson->saying_date,
                
                // حالة وجود المصادر
                'has_pdf' => $lesson->sources->contains('source_type', 'pdf'),
                'has_audio' => $lesson->sources->contains('source_type', 'audio'),
                'has_video' => $lesson->sources->contains('source_type', 'video'),
                
                'is_favorited' => true, // دائماً true لأنها في المفضلة
            ];
        })->items();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الدروس المفضلة بنجاح.',
            // بيانات التصفح
            'meta' => [
                'page_number' => $favoriteLessons->currentPage(),
                'total_pages' => $favoriteLessons->lastPage(),
                'has_previous' => $favoriteLessons->currentPage() > 1,
                'has_next' => $favoriteLessons->hasMorePages(),
                'total_items' => $favoriteLessons->total(),
            ],
            'data' => $formattedLessons
        ]);
    }
}


