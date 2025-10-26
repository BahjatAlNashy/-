# كيفية رفع ملفات متعددة - دليل Postman

## 🚨 المشكلة الشائعة
عند محاولة رفع أكثر من ملف PDF (أو صوت/فيديو)، يتم استقبال ملف واحد فقط!

---

## ✅ الحل الصحيح

### في Postman:

#### ❌ الطريقة الخاطئة:
```
Key: pdf_source[]
Value: file1.pdf

Key: pdf_source[]
Value: file2.pdf
```
**هذا لن يعمل! سيرسل ملف واحد فقط**

---

#### ✅ الطريقة الصحيحة:

**الخطوات:**

1. افتح Postman
2. اختر **POST**
3. ضع الرابط: `http://your-domain.com/api/AddLesson`
4. في **Headers**:
   ```
   Authorization: Bearer YOUR_TOKEN
   ```
5. اختر **Body** → **form-data**
6. أضف الحقول كالتالي:

```
Key                  | Type  | Value
---------------------|-------|------------------
title                | Text  | درس البرمجة
saying_date          | Text  | 2024-10-13
description          | Text  | وصف الدرس
is_private           | Text  | false
pdf_source[]         | File  | [اختر ملف PDF 1]
pdf_source[]         | File  | [اختر ملف PDF 2]
pdf_source[]         | File  | [اختر ملف PDF 3]
audio_source[]       | File  | [اختر ملف صوت 1]
audio_source[]       | File  | [اختر ملف صوت 2]
video_source[]       | File  | [اختر ملف فيديو]
```

**ملاحظة مهمة جداً:**
- يجب أن يكون **Type** للملفات: **File** (وليس Text)
- استخدم نفس الاسم `pdf_source[]` لكل ملفات PDF
- أضف صف جديد لكل ملف بنفس الاسم

---

## 📸 شرح بالصور (Steps في Postman):

### الخطوة 1: إعداد Request
```
Method: POST
URL: http://localhost:8000/api/AddLesson
```

### الخطوة 2: إضافة Authorization
```
Headers:
  Key: Authorization
  Value: Bearer YOUR_TOKEN_HERE
```

### الخطوة 3: إعداد Body
1. اختر **Body**
2. اختر **form-data**
3. أضف الحقول النصية أولاً

### الخطوة 4: إضافة الملفات
**لكل ملف PDF:**
1. اضغط على صف جديد
2. في خانة **Key** اكتب: `pdf_source[]`
3. غير **Type** من Text إلى **File** (من القائمة المنسدلة)
4. اضغط **Select Files** واختر الملف
5. **كرر هذه الخطوات لكل ملف PDF إضافي**

---

## 🔄 مثال كامل

### إرسال 3 ملفات PDF + 2 ملفات صوت:

```
Row 1:  title            | Text | درس جديد
Row 2:  saying_date      | Text | 2024-10-13
Row 3:  description      | Text | شرح الدرس
Row 4:  is_private       | Text | false
Row 5:  pdf_source[]     | File | lesson1.pdf
Row 6:  pdf_source[]     | File | lesson2.pdf
Row 7:  pdf_source[]     | File | lesson3.pdf
Row 8:  audio_source[]   | File | audio1.mp3
Row 9:  audio_source[]   | File | audio2.mp3
```

---

## 💻 مثال باستخدام cURL

```bash
curl -X POST http://localhost:8000/api/AddLesson \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "title=درس جديد" \
  -F "saying_date=2024-10-13" \
  -F "description=وصف الدرس" \
  -F "is_private=false" \
  -F "pdf_source[]=@/path/to/file1.pdf" \
  -F "pdf_source[]=@/path/to/file2.pdf" \
  -F "pdf_source[]=@/path/to/file3.pdf" \
  -F "audio_source[]=@/path/to/audio1.mp3" \
  -F "video_source[]=@/path/to/video1.mp4"
```

**ملاحظة:** استخدم `@` قبل مسار الملف و `[]` بعد اسم الحقل

---

## 🧪 مثال باستخدام JavaScript/Axios

```javascript
const formData = new FormData();

// إضافة الحقول النصية
formData.append('title', 'درس جديد');
formData.append('saying_date', '2024-10-13');
formData.append('description', 'وصف الدرس');
formData.append('is_private', false);

// إضافة ملفات PDF متعددة
formData.append('pdf_source[]', pdfFile1);
formData.append('pdf_source[]', pdfFile2);
formData.append('pdf_source[]', pdfFile3);

// إضافة ملفات صوت متعددة
formData.append('audio_source[]', audioFile1);
formData.append('audio_source[]', audioFile2);

// إضافة ملف فيديو
formData.append('video_source[]', videoFile1);

// إرسال Request
axios.post('/api/AddLesson', formData, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'multipart/form-data'
  }
})
.then(response => console.log(response.data))
.catch(error => console.error(error));
```

---

## 🔍 التحقق من استلام الملفات

### في Laravel، يمكنك التحقق:

```php
// في Controller
public function store(Request $request) {
    // طباعة عدد الملفات
    dd([
        'pdf_count' => count($request->file('pdf_source') ?? []),
        'audio_count' => count($request->file('audio_source') ?? []),
        'video_count' => count($request->file('video_source') ?? []),
        'all_files' => $request->allFiles(),
    ]);
}
```

---

## ⚙️ Validation Rules في الكود

```php
$request->validate([
    'pdf_source.*' => 'nullable|file|mimes:pdf|max:10240',
    'audio_source.*' => 'nullable|file|mimes:mp3,wav,aac,ogg|max:10240',
    'video_source.*' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200',
]);
```

**ملاحظة:**
- `pdf_source.*` يعني array من الملفات
- يتم التحقق من كل ملف على حدة

---

## 🐛 استكشاف الأخطاء

### المشكلة 1: يستقبل ملف واحد فقط
**السبب:** لم تستخدم `[]` في اسم الحقل  
**الحل:** استخدم `pdf_source[]` وليس `pdf_source`

### المشكلة 2: لا يستقبل أي ملفات
**السبب:** Type هو Text بدلاً من File  
**الحل:** تأكد أن Type = **File** في Postman

### المشكلة 3: Validation Error
**السبب:** حجم الملف كبير جداً  
**الحل:** 
- PDF/Audio: Max 10MB
- Video: Max 50MB

### المشكلة 4: 413 Request Entity Too Large
**السبب:** حجم الطلب الكلي كبير جداً  
**الحل:** زيادة `upload_max_filesize` في php.ini

---

## 📝 ملاحظات مهمة

1. ✅ **استخدم `[]` في اسم الحقل** للإشارة أنه array
2. ✅ **نفس الاسم لكل ملف** من نفس النوع
3. ✅ **Type = File** في Postman
4. ✅ **صف جديد لكل ملف** في Postman
5. ✅ **لا حد أقصى لعدد الملفات** (إلا حجم الـ request)

---

## ✅ التطبيق على القصائد (Poems)

**نفس الطريقة تماماً!**

```
Endpoint: POST /api/AddPoem
Body:
  title            | Text | قصيدة جديدة
  saying_date      | Text | 2024-10-13
  description      | Text | وصف القصيدة
  is_private       | Text | false
  pdf_source[]     | File | poem1.pdf
  pdf_source[]     | File | poem2.pdf
  audio_source[]   | File | audio.mp3
  video_source[]   | File | video.mp4
```

---

## 🎯 الخلاصة

**للنجاح في رفع ملفات متعددة:**
1. استخدم `pdf_source[]` (مع الأقواس)
2. أضف صف جديد لكل ملف
3. تأكد أن Type = **File**
4. نفس الاسم لجميع ملفات PDF

**هذا ينطبق على:**
- ✅ إنشاء درس (`POST /api/AddLesson`)
- ✅ إنشاء قصيدة (`POST /api/AddPoem`)
- ✅ إضافة مصادر لدرس (`POST /api/AddSourceLesson/{id}`)
- ✅ إضافة مصادر لقصيدة (`POST /api/AddSourcePoem/{id}`)
- ✅ تحديث درس (`POST /api/lessons/{id}/update`)
- ✅ تحديث قصيدة (`POST /api/poems/{id}/update`)
