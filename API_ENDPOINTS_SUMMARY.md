# ملخص جميع Endpoints - القصائد والدروس

## 🔐 Authentication Routes
```
POST   /api/register          # تسجيل مستخدم جديد
POST   /api/login             # تسجيل دخول
GET    /api/user              # معلومات المستخدم الحالي (auth required)
```

---

## 📝 القصائد (Poems)

### CRUD Operations
```
POST   /api/AddPoem                      # إنشاء قصيدة جديدة (Admin only)
GET    /api/poems/getall                 # عرض جميع القصائد (Public)
GET    /api/poems/{poem_id}              # عرض قصيدة واحدة (Public)
POST   /api/poems/{poem_id}/update       # تحديث قصيدة (Admin/Owner)
DELETE /api/deletePoem/{id}              # حذف قصيدة (Admin/Owner)
```

### Sources Management
```
POST   /api/AddSourcePoem/{poem_id}      # إضافة مصادر (Admin/Owner)
DELETE /api/deleteSource/{source_id}     # حذف مصدر واحد (Admin/Owner)
```

### Search & Favorites
```
GET    /api/poems/search                 # البحث في القصائد (Public)
POST   /api/FavoritePoem/{poem_id}       # إضافة/إزالة من المفضلة (Auth)
GET    /api/poems/favorites              # عرض القصائد المفضلة (Auth) ✨ جديد
```

### Comments (CRUD كامل) ✨
```
GET    /api/poems/{poem_id}/comments         # عرض تعليقات قصيدة (Public)
POST   /api/poems/{poem_id}/comments         # إضافة تعليق (Auth)
PUT    /api/poems/comments/{comment_id}      # تحديث تعليق (Owner/Admin)
DELETE /api/poems/comments/{comment_id}      # حذف تعليق (Owner/Admin)
```

---

## 📚 الدروس (Lessons)

### CRUD Operations
```
POST   /api/AddLesson                    # إنشاء درس جديد (Admin only)
GET    /api/lessons/getall               # عرض جميع الدروس (Public)
GET    /api/lessons/{lesson_id}          # عرض درس واحد (Public)
POST   /api/lessons/{lesson_id}/update   # تحديث درس (Admin/Owner)
DELETE /api/deleteLesson/{id}            # حذف درس (Admin/Owner)
```

### Sources Management
```
POST   /api/AddSourceLesson/{lesson_id}      # إضافة مصادر (Admin/Owner)
DELETE /api/deleteLessonSource/{source_id}   # حذف مصدر واحد (Admin/Owner)
```

### Search & Favorites
```
GET    /api/lessons/search               # البحث في الدروس (Public)
POST   /api/FavoriteLesson/{lesson_id}   # إضافة/إزالة من المفضلة (Auth)
GET    /api/lessons/favorites            # عرض الدروس المفضلة (Auth) ✨ جديد
```

### Comments (CRUD كامل) ✨
```
GET    /api/lessons/{lesson_id}/comments      # عرض تعليقات درس (Public)
POST   /api/lessons/{lesson_id}/comments      # إضافة تعليق (Auth)
PUT    /api/lessons/comments/{comment_id}     # تحديث تعليق (Owner/Admin)
DELETE /api/lessons/comments/{comment_id}     # حذف تعليق (Owner/Admin)
```

---

## 📊 جدول الصلاحيات

| العملية | Admin | Owner | Authenticated | Public |
|---------|-------|-------|---------------|--------|
| **Create** | ✅ | ❌ | ❌ | ❌ |
| **Read (Public items)** | ✅ | ✅ | ✅ | ✅ |
| **Read (Private items)** | ✅ | ✅ (own) | ❌ | ❌ |
| **Update** | ✅ | ✅ (own) | ❌ | ❌ |
| **Delete** | ✅ | ✅ (own) | ❌ | ❌ |
| **Add Sources** | ✅ | ✅ (own) | ❌ | ❌ |
| **Delete Source** | ✅ | ✅ (own) | ❌ | ❌ |
| **Search** | ✅ | ✅ | ✅ | ✅ |
| **Favorite/Unfavorite** | ✅ | ✅ | ✅ | ❌ |
| **View Favorites** | ✅ | ✅ | ✅ | ❌ |
| **Add Comment** | ✅ | ✅ | ✅ | ❌ |
| **Read Comments** | ✅ | ✅ | ✅ | ✅ |
| **Update Comment** | ❌ | ✅ (own only) | ❌ | ❌ |
| **Delete Comment** | ✅ | ✅ (own) | ❌ | ❌ |

---

## 🔑 Headers المطلوبة

### للعمليات المحمية (auth:sanctum):
```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

### لرفع الملفات:
```
Content-Type: multipart/form-data
```

---

## 📦 الملفات المدعومة

### PDF:
- **Max Size:** 10 MB
- **Mimes:** pdf

### Audio:
- **Max Size:** 10 MB
- **Mimes:** mp3, wav, aac, ogg

### Video:
- **Max Size:** 50 MB
- **Mimes:** mp4, avi, mov, wmv

---

## 💡 ملاحظات مهمة

### دعم الملفات المتعددة:
جميع endpoints الخاصة بـ Create و Update و AddSource تدعم **ملفات متعددة**:

**مثال في Postman:**
```
pdf_source[]: [file1.pdf]
pdf_source[]: [file2.pdf]
pdf_source[]: [file3.pdf]
audio_source[]: [audio1.mp3]
video_source[]: [video1.mp4]
```

**مثال في JavaScript:**
```javascript
const formData = new FormData();
formData.append('title', 'عنوان');

pdfFiles.forEach(file => {
  formData.append('pdf_source[]', file);
});

audioFiles.forEach(file => {
  formData.append('audio_source[]', file);
});
```

### Response للملفات المتعددة:
```json
{
  "videos": [
    {"id": 1, "url": "/storage/poems/videos/v1.mp4"},
    {"id": 2, "url": "/storage/poems/videos/v2.mp4"}
  ],
  "audios": [
    {"id": 3, "url": "/storage/poems/audios/a1.mp3"}
  ],
  "pdfs": [
    {"id": 4, "url": "/storage/poems/pdfs/p1.pdf"},
    {"id": 5, "url": "/storage/poems/pdfs/p2.pdf"}
  ]
}
```

---

## 🚀 أمثلة سريعة

### 1. الحصول على جميع القصائد المفضلة:
```bash
curl -X GET https://your-domain.com/api/poems/favorites \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 2. الحصول على جميع الدروس المفضلة:
```bash
curl -X GET https://your-domain.com/api/lessons/favorites \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. إضافة قصيدة للمفضلة:
```bash
curl -X POST https://your-domain.com/api/FavoritePoem/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. البحث في القصائد:
```bash
curl -X GET "https://your-domain.com/api/poems/search?keyword=أمل&year=2024&source_type=pdf"
```

### 5. إنشاء درس جديد مع ملفات متعددة:
```bash
curl -X POST https://your-domain.com/api/AddLesson \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "title=درس جديد" \
  -F "saying_date=2024-10-12" \
  -F "pdf_source[]=@file1.pdf" \
  -F "pdf_source[]=@file2.pdf" \
  -F "audio_source[]=@audio1.mp3"
```

---

## 📖 التوثيق الكامل

### مرجع سريع:
- **`ALL_ROUTES.md`** - 📋 **جدول شامل لجميع الـ Routes** (ابدأ من هنا!)

### توثيق مفصل:
- **`POEM_API_DOCS.md`** - توثيق شامل للقصائد
- **`LESSON_API_DOCS.md`** - توثيق شامل للدروس
- **`COMMENTS_API_DOCS.md`** - توثيق شامل للتعليقات
- **`PRIVACY_RULES.md`** - قواعد الخصوصية
- **`HOW_TO_UPLOAD_MULTIPLE_FILES.md`** - كيفية رفع ملفات متعددة ⚠️ مهم
