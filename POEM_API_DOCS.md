# توثيق API للقصائد - دعم الملفات المتعددة

## ملخص التحديثات
تم تعديل جميع endpoints لدعم إضافة أكثر من ملف PDF، صوت، وفيديو لكل قصيدة.

---

## 1. إنشاء قصيدة جديدة (Create)
**Endpoint:** `POST /api/AddPoem`  
**Authentication:** Required (Bearer Token)  
**Content-Type:** `multipart/form-data`

### Parameters:
```
title: string (required, unique)
saying_date: date (nullable, format: Y-m-d)
description: string (nullable)
is_private: boolean (default: false)

# ملفات متعددة - يمكن إرسال أكثر من ملف
pdf_source[]: file[] (nullable, max: 10MB per file)
audio_source[]: file[] (nullable, max: 10MB per file)
video_source[]: file[] (nullable, max: 50MB per file)
```

### مثال باستخدام Postman:
```
1. اختر POST
2. الرابط: http://your-domain.com/api/AddPoem
3. Headers: 
   Authorization: Bearer YOUR_TOKEN
4. Body -> form-data (أضف كل صف كما يلي):

Key                | Type  | Value
-------------------|-------|------------------
title              | Text  | قصيدة جديدة
saying_date        | Text  | 2024-10-12
description        | Text  | وصف القصيدة
is_private         | Text  | false
pdf_source[]       | File  | [اختر ملف PDF 1]
pdf_source[]       | File  | [اختر ملف PDF 2]  ← صف جديد بنفس الاسم
pdf_source[]       | File  | [اختر ملف PDF 3]  ← صف جديد بنفس الاسم
audio_source[]     | File  | [اختر ملف صوت 1]
audio_source[]     | File  | [اختر ملف صوت 2]  ← صف جديد بنفس الاسم
video_source[]     | File  | [اختر ملف فيديو]
```

**⚠️ مهم جداً:**
- يجب أن يكون Type للملفات = **File** (وليس Text)
- لإضافة أكثر من ملف PDF: أضف صف جديد بنفس الاسم `pdf_source[]`
- استخدم الأقواس `[]` في نهاية اسم الحقل
- راجع ملف `HOW_TO_UPLOAD_MULTIPLE_FILES.md` للتفاصيل الكاملة

---

## 2. إضافة مصادر لقصيدة موجودة
**Endpoint:** `POST /api/AddSourcePoem/{poem_id}`  
**Authentication:** Required (Bearer Token)  
**Content-Type:** `multipart/form-data`

### Parameters:
```
pdf_source[]: file[] (nullable)
audio_source[]: file[] (nullable)
video_source[]: file[] (nullable)
```

### ملاحظات:
- يضيف ملفات جديدة دون حذف القديمة
- يمكن إرسال أكثر من ملف من نفس النوع

---

## 3. تحديث قصيدة (Update)
**Endpoint:** `POST /api/poems/{poem_id}/update`  
**Authentication:** Required (Bearer Token)  
**Content-Type:** `multipart/form-data`

### Parameters:
```
title: string (required)
saying_date: date (nullable)
description: string (nullable)

# إضافة ملفات جديدة (لن تحذف الملفات القديمة)
pdf_source[]: file[] (nullable)
audio_source[]: file[] (nullable)
video_source[]: file[] (nullable)
```

### ملاحظات:
- الملفات الجديدة تُضاف إلى الملفات الموجودة
- لحذف ملف محدد، استخدم endpoint منفصل (انظر أدناه)

---

## 4. حذف ملف مصدر واحد محدد
**Endpoint:** `DELETE /api/deleteSource/{source_id}`  
**Authentication:** Required (Bearer Token)

### Response:
```json
{
  "success": true,
  "message": "تم حذف المصدر بنجاح."
}
```

### ملاحظات:
- يحذف ملف واحد محدد بناءً على `source_id`
- يمكن الحصول على `source_id` من استجابة `GET /api/poems/{poem_id}`

---

## 5. عرض تفاصيل قصيدة
**Endpoint:** `GET /api/poems/{poem_id}`  
**Authentication:** Optional

### Response (تم تحديثه):
```json
{
  "success": true,
  "message": "تم جلب تفاصيل القصيدة بنجاح.",
  "data": {
    "id": 1,
    "title": "قصيدة الأمل",
    "description": "...",
    "date": "2024-10-12",
    
    "videos": [
      {
        "id": 1,
        "url": "/storage/poems/videos/video1.mp4"
      },
      {
        "id": 2,
        "url": "/storage/poems/videos/video2.mp4"
      }
    ],
    "audios": [
      {
        "id": 3,
        "url": "/storage/poems/audios/audio1.mp3"
      }
    ],
    "pdfs": [
      {
        "id": 4,
        "url": "/storage/poems/pdfs/pdf1.pdf"
      },
      {
        "id": 5,
        "url": "/storage/poems/pdfs/pdf2.pdf"
      }
    ],
    
    "is_favorited": false,
    "comments": [],
    "comments_count": 0,
    "author_name": "أحمد"
  }
}
```

---

## 6. عرض القصائد المفضلة للمستخدم
**Endpoint:** `GET /api/poems/favorites`  
**Authentication:** Required (Bearer Token)

### Response:
```json
{
  "success": true,
  "message": "تم جلب القصائد المفضلة بنجاح.",
  "count": 5,
  "data": [
    {
      "id": 1,
      "title": "قصيدة الأمل",
      "description": "...",
      "date": "2024-10-12",
      "has_pdf": true,
      "has_audio": true,
      "has_video": false,
      "is_favorited": true,
      "author_name": "أحمد"
    }
  ]
}
```

### ملاحظات:
- يعرض فقط القصائد التي أضافها المستخدم للمفضلة
- مرتبة حسب تاريخ الإضافة (الأحدث أولاً)

---

## 7. حذف قصيدة كاملة
**Endpoint:** `DELETE /api/deletePoem/{id}`  
**Authentication:** Required (Bearer Token)

### ملاحظات:
- يحذف القصيدة وجميع المصادر المرتبطة بها
- يحذف جميع الملفات من التخزين

---

## الفروقات الرئيسية بعد التحديث:

### قبل التحديث:
- كان يمكن إضافة ملف واحد فقط من كل نوع
- Update كان يستبدل الملف القديم بالجديد
- Response كان يعيد ملف واحد فقط:
  ```json
  "video": { "id": 1, "url": "..." }
  "audio": { "id": 2, "url": "..." }
  "pdf": { "id": 3, "url": "..." }
  ```

### بعد التحديث:
- يمكن إضافة عدة ملفات من نفس النوع
- Update يضيف ملفات جديدة دون حذف القديمة
- Response يعيد مصفوفة من الملفات:
  ```json
  "videos": [{ "id": 1, "url": "..." }, { "id": 2, "url": "..." }]
  "audios": [{ "id": 3, "url": "..." }]
  "pdfs": [{ "id": 4, "url": "..." }, { "id": 5, "url": "..." }]
  ```

---

## نصائح للتطوير:

### 1. إرسال ملف واحد (متوافق مع النسخة القديمة):
```
pdf_source[]: [ملف واحد]
```

### 2. إرسال ملفات متعددة:
```
pdf_source[]: [ملف 1]
pdf_source[]: [ملف 2]
pdf_source[]: [ملف 3]
```

### 3. في JavaScript/React:
```javascript
const formData = new FormData();
formData.append('title', 'قصيدة جديدة');

// إضافة ملفات متعددة
pdfFiles.forEach(file => {
  formData.append('pdf_source[]', file);
});

audioFiles.forEach(file => {
  formData.append('audio_source[]', file);
});

fetch('/api/AddPoem', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`
  },
  body: formData
});
```

---

## Validation Rules:
- **PDF:** max 10MB, mimes: pdf
- **Audio:** max 10MB, mimes: mp3, wav, aac, ogg
- **Video:** max 50MB, mimes: mp4, avi, mov, wmv

---

## Authorization:
- Admin: يمكنه إنشاء، تعديل، حذف أي قصيدة
- Owner: يمكنه تعديل وحذف قصائده فقط
- Public User: يمكنه عرض القصائد العامة فقط
