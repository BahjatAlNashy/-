# توثيق API للدروس - دعم الملفات المتعددة

## ملخص
نظام كامل لإدارة الدروس (Lessons) مع دعم ملفات متعددة من PDF، صوت، وفيديو. النظام مطابق تمامًا لنظام القصائد.

---

## 1. إنشاء درس جديد (Create)
**Endpoint:** `POST /api/AddLesson`  
**Authentication:** Required (Bearer Token - Admin only)  
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
2. الرابط: http://your-domain.com/api/AddLesson
3. Headers: 
   Authorization: Bearer YOUR_TOKEN
4. Body -> form-data (أضف كل صف كما يلي):

Key                | Type  | Value
-------------------|-------|------------------
title              | Text  | درس جديد
saying_date        | Text  | 2024-10-12
description        | Text  | وصف الدرس
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

### Response:
```json
{
  "success": true,
  "message": "تم إنشاء الدرس بنجاح",
  "data": {
    "lesson": {
      "id": 1,
      "title": "درس جديد",
      "saying_date": "2024-10-12",
      "description": "وصف الدرس",
      "is_private": false,
      "sources": [...]
    }
  }
}
```

---

## 2. إضافة مصادر لدرس موجود
**Endpoint:** `POST /api/AddSourceLesson/{lesson_id}`  
**Authentication:** Required (Bearer Token - Admin or Owner)  
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
- فقط Admin أو مالك الدرس يمكنه إضافة المصادر

---

## 3. تحديث درس (Update)
**Endpoint:** `POST /api/lessons/{lesson_id}/update`  
**Authentication:** Required (Bearer Token - Admin or Owner)  
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
- لحذف ملف محدد، استخدم endpoint منفصل

---

## 4. حذف ملف مصدر واحد محدد
**Endpoint:** `DELETE /api/deleteLessonSource/{source_id}`  
**Authentication:** Required (Bearer Token - Admin or Owner)

### Response:
```json
{
  "success": true,
  "message": "تم حذف المصدر بنجاح."
}
```

---

## 5. عرض جميع الدروس (مع Pagination)
**Endpoint:** `GET /api/lessons/getall`  
**Authentication:** Optional

### Query Parameters:
```
page: integer (default: 1)
```

### Response:
```json
{
  "success": true,
  "message": "تم جلب قائمة الدروس بنجاح.",
  "meta": {
    "page_number": 1,
    "total_pages": 3,
    "has_previous": false,
    "has_next": true,
    "total_items": 42
  },
  "data": [
    {
      "id": 1,
      "title": "درس الأول",
      "date": "2024-10-12",
      "has_pdf": true,
      "has_audio": true,
      "has_video": false,
      "is_favorited": false
    }
  ]
}
```

---

## 6. عرض تفاصيل درس
**Endpoint:** `GET /api/lessons/{lesson_id}`  
**Authentication:** Optional

### Response (مع دعم الملفات المتعددة):
```json
{
  "success": true,
  "message": "تم جلب تفاصيل الدرس بنجاح.",
  "data": {
    "id": 1,
    "title": "درس الأمل",
    "description": "...",
    "date": "2024-10-12",
    
    "videos": [
      {
        "id": 1,
        "url": "/storage/lessons/videos/video1.mp4"
      },
      {
        "id": 2,
        "url": "/storage/lessons/videos/video2.mp4"
      }
    ],
    "audios": [
      {
        "id": 3,
        "url": "/storage/lessons/audios/audio1.mp3"
      }
    ],
    "pdfs": [
      {
        "id": 4,
        "url": "/storage/lessons/pdfs/pdf1.pdf"
      },
      {
        "id": 5,
        "url": "/storage/lessons/pdfs/pdf2.pdf"
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

## 7. البحث عن الدروس
**Endpoint:** `GET /api/lessons/search`  
**Authentication:** Optional

### Query Parameters:
```
keyword: string (البحث في العنوان والوصف)
year: integer (البحث بالسنة)
month: integer (البحث بالشهر: 1-12)
date: date (البحث بتاريخ محدد، format: Y-m-d)
date_comparison: string (=, >, <, >=, <=)
source_type: string (pdf, audio, video)
```

### مثال:
```
GET /api/lessons/search?keyword=برمجة&source_type=pdf&year=2024
```

### Response:
```json
{
  "success": true,
  "count": 5,
  "message": "نتائج البحث عن الدروس",
  "data": [...]
}
```

---

## 8. إضافة/إزالة من المفضلة
**Endpoint:** `POST /api/FavoriteLesson/{lesson_id}`  
**Authentication:** Required (Bearer Token)

### Response:
```json
{
  "success": true,
  "message": "تمت إضافة الدرس إلى المفضلة.",
  "is_favorited": true
}
```

---

## 9. عرض الدروس المفضلة للمستخدم
**Endpoint:** `GET /api/lessons/favorites`  
**Authentication:** Required (Bearer Token)

### Response:
```json
{
  "success": true,
  "message": "تم جلب الدروس المفضلة بنجاح.",
  "count": 5,
  "data": [
    {
      "id": 1,
      "title": "درس البرمجة",
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
- يعرض فقط الدروس التي أضافها المستخدم للمفضلة
- مرتبة حسب تاريخ الإضافة (الأحدث أولاً)

---

## 10. حذف درس كامل
**Endpoint:** `DELETE /api/deleteLesson/{id}`  
**Authentication:** Required (Bearer Token - Admin or Owner)

### ملاحظات:
- يحذف الدرس وجميع المصادر المرتبطة به
- يحذف جميع الملفات من التخزين

### Response:
```json
{
  "success": true,
  "message": "تم حذف الدرس وجميع مصادره بنجاح."
}
```

---

## الصلاحيات (Authorization):

| العملية | Admin | Owner | Public User |
|---------|-------|-------|-------------|
| Create | ✅ | ❌ | ❌ |
| Read (Public) | ✅ | ✅ | ✅ |
| Read (Private) | ✅ | ✅ (Own only) | ❌ |
| Update | ✅ | ✅ (Own only) | ❌ |
| Delete | ✅ | ✅ (Own only) | ❌ |
| Add Sources | ✅ | ✅ (Own only) | ❌ |
| Delete Source | ✅ | ✅ (Own only) | ❌ |
| Favorite | ✅ | ✅ | ✅ |
| Search | ✅ | ✅ | ✅ |

---

## Validation Rules:
- **Title:** required, unique, max 255 characters
- **PDF:** max 10MB, mimes: pdf
- **Audio:** max 10MB, mimes: mp3, wav, aac, ogg
- **Video:** max 50MB, mimes: mp4, avi, mov, wmv

---

## Models & Relationships:

### Lesson Model:
- `id`
- `title`
- `saying_date`
- `description`
- `user_id` (foreign key)
- `is_private` (boolean)
- `created_at`
- `updated_at`

### Relationships:
- `sources()` - hasMany (Source)
- `user()` - belongsTo (User)
- `comments()` - hasMany (Comment)
- `favorites()` - hasMany (LessonFavorite)
- `favoritedBy()` - belongsToMany (User through lesson_favorites)

---

## Database Tables:

### lessons
```sql
id, title, saying_date, description, user_id, is_private, created_at, updated_at
```

### sources
```sql
id, source_type, source, url, poem_id, lesson_id, created_at, updated_at
```

### lesson_favorites
```sql
id, user_id, lesson_id, created_at, updated_at
UNIQUE(user_id, lesson_id)
```

### comments
```sql
id, poem_id, lesson_id, user_id, content, created_at, updated_at
```

---

## نصائح للتطوير:

### JavaScript/React Example:
```javascript
const formData = new FormData();
formData.append('title', 'درس جديد');
formData.append('saying_date', '2024-10-12');
formData.append('is_private', false);

// إضافة ملفات متعددة
pdfFiles.forEach(file => {
  formData.append('pdf_source[]', file);
});

audioFiles.forEach(file => {
  formData.append('audio_source[]', file);
});

videoFiles.forEach(file => {
  formData.append('video_source[]', file);
});

fetch('/api/AddLesson', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`
  },
  body: formData
});
```

---

## الفرق بين Poems و Lessons:

| Feature | Poems | Lessons |
|---------|-------|---------|
| Endpoint Prefix | `/poems` | `/lessons` |
| Create Route | `/AddPoem` | `/AddLesson` |
| Favorites Table | `favorites` | `lesson_favorites` |
| Delete Source Route | `/deleteSource/{id}` | `/deleteLessonSource/{id}` |
| Storage Path | `poems/{type}` | `lessons/{type}` |

**الوظائف والميزات متطابقة 100% بين النظامين!**
