# 📚 API Documentation الكامل

**Base URL:** `http://localhost:8000/api`

---

## 📑 المحتويات

1. [Authentication](#auth)
2. [القصائد (Poems)](#poems)
3. [الدروس (Lessons)](#lessons)
4. [الأقوال (Sayings)](#sayings)
5. [المشاركات (Posts)](#posts)
6. [التعليقات (Comments)](#comments)
7. [Examples & Tips](#tips)

---

<a name="auth"></a>
## 🔐 1. Authentication

### 1.1 تسجيل مستخدم جديد
```
POST /api/register
```

**Request Body:**
```json
{
  "name": "أحمد محمد",
  "email": "ahmed@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "تم التسجيل بنجاح",
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "email": "ahmed@example.com",
      "role": "user"
    },
    "token": "1|abcdef123456..."
  }
}
```

---

### 1.2 تسجيل الدخول
```
POST /api/login
```

**Request Body:**
```json
{
  "email": "ahmed@example.com",
  "password": "password123"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "user": {...},
    "token": "2|xyz789..."
  }
}
```

---

### 1.3 معلومات المستخدم الحالي
```
GET /api/user
Authorization: Bearer TOKEN
```

**Response 200:**
```json
{
  "id": 1,
  "name": "أحمد محمد",
  "email": "ahmed@example.com",
  "role": "user"
}
```

---

<a name="poems"></a>
## 📖 2. القصائد (Poems)

### 2.1 إنشاء قصيدة
```
POST /api/AddPoem
Authorization: Bearer TOKEN (Admin Only)
Content-Type: multipart/form-data
```

**Request Body:**
```
title: "عنوان القصيدة"
content: "محتوى القصيدة..."
author: "اسم الشاعر"
is_private: false
pdf_source[]: [file]
audio_source[]: [file]
video_source[]: [file]
```

**Response 201:**
```json
{
  "success": true,
  "message": "تم إنشاء القصيدة بنجاح",
  "data": {
    "id": 1,
    "title": "عنوان القصيدة",
    "content": "محتوى...",
    "author": "الشاعر",
    "pdf_sources": ["url"],
    "audio_sources": [],
    "video_sources": []
  }
}
```

---

### 2.2 عرض جميع القصائد
```
GET /api/poems/getall?page=1
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "title": "عنوان",
        "content": "محتوى...",
        "author": "الشاعر",
        "is_favorited": false,
        "comments_count": 5,
        "created_at": "منذ ساعة"
      }
    ],
    "per_page": 15,
    "total": 50
  }
}
```

---

### 2.3 عرض قصيدة واحدة
```
GET /api/poems/{id}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "عنوان",
    "content": "محتوى...",
    "author": "الشاعر",
    "is_private": false,
    "is_favorited": false,
    "comments_count": 5,
    "pdf_sources": ["url"],
    "audio_sources": [],
    "video_sources": [],
    "comments": [...]
  }
}
```

---

### 2.4 تحديث قصيدة
```
POST /api/poems/{id}/update
Authorization: Bearer TOKEN (Owner or Admin)
```

**Request:**
```
title: "عنوان محدث"
content: "محتوى محدث"
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم التحديث بنجاح"
}
```

---

### 2.5 حذف قصيدة
```
DELETE /api/deletePoem/{id}
Authorization: Bearer TOKEN (Owner or Admin)
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم الحذف بنجاح"
}
```

---

### 2.6 البحث في القصائد
```
GET /api/poems/search?keyword=حب&author=نزار
```

**Response 200:**
```json
{
  "success": true,
  "count": 10,
  "data": {...}
}
```

---

### 2.7 إضافة/إزالة من المفضلة
```
POST /api/FavoritePoem/{id}
Authorization: Bearer TOKEN
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم إضافة القصيدة إلى المفضلة",
  "is_favorited": true
}
```

---

### 2.8 عرض القصائد المفضلة
```
GET /api/poems/favorites
Authorization: Bearer TOKEN
```

**Response 200:**
```json
{
  "success": true,
  "count": 5,
  "data": {...}
}
```

---

### 2.9 إضافة مصادر لقصيدة موجودة
```
POST /api/AddSourcePoem/{id}
Authorization: Bearer TOKEN (Owner or Admin)
Content-Type: multipart/form-data
```

**Request:**
```
pdf_source[]: [file]
audio_source[]: [file]
video_source[]: [file]
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم إضافة المصادر بنجاح"
}
```

---

### 2.10 حذف مصدر واحد
```
DELETE /api/deleteSource/{source_id}
Authorization: Bearer TOKEN (Owner or Admin)
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم حذف المصدر بنجاح"
}
```

---

<a name="lessons"></a>
## 📚 3. الدروس (Lessons)

### 3.1 إنشاء درس
```
POST /api/AddLesson
Authorization: Bearer TOKEN (Admin Only)
Content-Type: multipart/form-data
```

**Request:**
```
title: "عنوان الدرس"
content: "محتوى..."
is_private: false
pdf_source[]: [file]
audio_source[]: [file]
video_source[]: [file]
```

**Response 201:**
```json
{
  "success": true,
  "message": "تم إنشاء الدرس بنجاح",
  "data": {...}
}
```

---

### 3.2 عرض جميع الدروس
```
GET /api/lessons/getall?page=1
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [...],
    "per_page": 15,
    "total": 30
  }
}
```

---

### 3.3 عرض درس واحد
```
GET /api/lessons/{id}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "عنوان",
    "content": "محتوى...",
    "is_private": false,
    "is_favorited": false,
    "comments_count": 3,
    "pdf_sources": [],
    "audio_sources": [],
    "video_sources": [],
    "comments": []
  }
}
```

---

### 3.4 تحديث درس
```
POST /api/lessons/{id}/update
Authorization: Bearer TOKEN (Owner or Admin)
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم التحديث بنجاح"
}
```

---

### 3.5 حذف درس
```
DELETE /api/deleteLesson/{id}
Authorization: Bearer TOKEN (Owner or Admin)
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم الحذف بنجاح"
}
```

---

### 3.6 البحث في الدروس
```
GET /api/lessons/search?keyword=فقه
```

**Response 200:**
```json
{
  "success": true,
  "count": 8,
  "data": {...}
}
```

---

### 3.7 إضافة/إزالة من المفضلة
```
POST /api/FavoriteLesson/{id}
Authorization: Bearer TOKEN
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم إضافة الدرس إلى المفضلة",
  "is_favorited": true
}
```

---

### 3.8 عرض الدروس المفضلة
```
GET /api/lessons/favorites
Authorization: Bearer TOKEN
```

**Response 200:**
```json
{
  "success": true,
  "count": 3,
  "data": {...}
}
```

---

### 3.9 إضافة مصادر لدرس
```
POST /api/AddSourceLesson/{id}
Authorization: Bearer TOKEN (Owner or Admin)
```

---

### 3.10 حذف مصدر
```
DELETE /api/deleteSource/{source_id}
Authorization: Bearer TOKEN (Owner or Admin)
```

---

<a name="sayings"></a>
## 📖 4. الأقوال (Sayings)

### 4.1 إنشاء قول
```
POST /api/AddSaying
Authorization: Bearer TOKEN (Admin Only)
Content-Type: application/json
```

**Request:**
```json
{
  "type": "saying",
  "content": "الحكمة ضالة المؤمن",
  "is_private": false
}
```

**Validation:**
- type: required, in:saying,supplication
- content: required, string
- is_private: optional, boolean

**Response 201:**
```json
{
  "success": true,
  "message": "تم إنشاء القول بنجاح",
  "data": {
    "id": 1,
    "type": "saying",
    "content": "الحكمة ضالة المؤمن",
    "is_private": false
  }
}
```

---

### 4.2 عرض جميع الأقوال
```
GET /api/sayings/getall?type=saying&page=1
```

**Query Parameters:**
- type: optional (saying/supplication)
- page: optional

**Response 200:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "type": "saying",
        "content": "الحكمة ضالة المؤمن",
        "is_favorited": false,
        "comments_count": 2,
        "created_at": "منذ ساعة"
      }
    ],
    "per_page": 15,
    "total": 100
  }
}
```

---

### 4.3 عرض قول واحد
```
GET /api/sayings/{id}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "type": "saying",
    "content": "الحكمة ضالة المؤمن",
    "is_private": false,
    "is_favorited": false,
    "comments_count": 2,
    "comments": []
  }
}
```

---

### 4.4 تحديث قول
```
POST /api/sayings/{id}/update
Authorization: Bearer TOKEN (Owner Only)
```

**Request:**
```json
{
  "content": "محتوى محدث",
  "is_private": true
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم التحديث بنجاح"
}
```

---

### 4.5 حذف قول
```
DELETE /api/deleteSaying/{id}
Authorization: Bearer TOKEN (Owner or Admin)
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم الحذف بنجاح"
}
```

---

### 4.6 البحث في الأقوال
```
GET /api/sayings/search?keyword=حكمة&type=saying
```

**Response 200:**
```json
{
  "success": true,
  "count": 15,
  "data": {...}
}
```

---

### 4.7 إضافة/إزالة من المفضلة
```
POST /api/FavoriteSaying/{id}
Authorization: Bearer TOKEN
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم إضافة القول إلى المفضلة",
  "is_favorited": true
}
```

---

### 4.8 عرض الأقوال المفضلة
```
GET /api/sayings/favorites
Authorization: Bearer TOKEN
```

**Response 200:**
```json
{
  "success": true,
  "count": 10,
  "data": {...}
}
```

---

<a name="posts"></a>
## 📝 5. مشاركات الزوار (Posts)

### 5.1 إنشاء مشاركة
```
POST /api/posts
Authorization: Bearer TOKEN
Content-Type: application/json
```

**Request:**
```json
{
  "content": "هذه مشاركتي الأولى..."
}
```

**Validation:**
- content: required, string, min:10, max:2000

**Response 201:**
```json
{
  "success": true,
  "message": "تم إنشاء المشاركة بنجاح. في انتظار موافقة الإدارة.",
  "data": {
    "id": 1,
    "content": "هذه مشاركتي الأولى...",
    "author_name": "أحمد",
    "author_id": 1,
    "created_at": "2024-10-14 10:00:00"
  }
}
```

---

### 5.2 عرض المشاركات (الموافق عليها فقط)
```
GET /api/posts?page=1
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "content": "مشاركة...",
        "author_name": "أحمد",
        "author_id": 1,
        "created_at": "منذ ساعة"
      }
    ],
    "per_page": 15,
    "total": 50
  }
}
```

---

### 5.3 عرض مشاركة واحدة
```
GET /api/posts/{id}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "content": "مشاركة...",
    "author_name": "أحمد",
    "author_id": 1,
    "created_at": "2024-10-14 10:00:00"
  }
}
```

---

### 5.4 عرض مشاركاتي
```
GET /api/posts/my-posts
Authorization: Bearer TOKEN
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "content": "مشاركتي...",
        "is_approved": true,
        "status": "موافق عليها",
        "created_at": "منذ ساعة"
      },
      {
        "id": 2,
        "content": "مشاركة أخرى...",
        "is_approved": false,
        "status": "في انتظار الموافقة",
        "created_at": "منذ 5 دقائق"
      }
    ]
  }
}
```

---

### 5.5 المشاركات في انتظار الموافقة (Admin)
```
GET /api/posts/pending
Authorization: Bearer TOKEN (Admin Only)
```

**Response 200:**
```json
{
  "success": true,
  "count": 10,
  "data": {
    "data": [
      {
        "id": 5,
        "content": "مشاركة...",
        "author_name": "محمد",
        "author_id": 10,
        "created_at": "منذ دقيقتين"
      }
    ]
  }
}
```

---

### 5.6 الموافقة على مشاركة (Admin)
```
POST /api/posts/{id}/approve
Authorization: Bearer TOKEN (Admin Only)
Content-Type: application/json
```

**Request:**
```json
{
  "is_approved": true
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم الموافقة على المشاركة بنجاح",
  "data": {
    "id": 5,
    "is_approved": true
  }
}
```

---

### 5.7 تحديث مشاركة
```
POST /api/posts/{id}/update
Authorization: Bearer TOKEN (Owner Only)
```

**Request:**
```json
{
  "content": "محتوى محدث..."
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم التحديث بنجاح"
}
```

---

### 5.8 حذف مشاركة
```
DELETE /api/posts/{id}
Authorization: Bearer TOKEN (Owner or Admin)
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم الحذف بنجاح"
}
```

---

### 5.9 البحث في المشاركات
```
GET /api/posts/search?keyword=تجربة
```

**Response 200:**
```json
{
  "success": true,
  "count": 5,
  "data": {...}
}
```

---

<a name="comments"></a>
## 💬 6. التعليقات (Comments)

### 6.1 تعليقات القصائد

#### عرض تعليقات قصيدة
```
GET /api/poems/{id}/comments
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "content": "تعليق رائع",
      "user_name": "محمد",
      "created_at": "منذ دقيقتين"
    }
  ]
}
```

#### إضافة تعليق
```
POST /api/poems/{id}/comments
Authorization: Bearer TOKEN
```

**Request:**
```json
{
  "content": "تعليق جديد..."
}
```

**Validation:**
- content: required, string, min:5, max:500

**Response 201:**
```json
{
  "success": true,
  "message": "تم إضافة التعليق بنجاح",
  "data": {
    "id": 10,
    "content": "تعليق جديد...",
    "user_name": "أحمد",
    "created_at": "الآن"
  }
}
```

#### تحديث تعليق
```
PUT /api/poems/comments/{comment_id}
Authorization: Bearer TOKEN (Owner Only)
```

**Request:**
```json
{
  "content": "تعليق محدث..."
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم تحديث التعليق بنجاح"
}
```

#### حذف تعليق
```
DELETE /api/poems/comments/{comment_id}
Authorization: Bearer TOKEN (Owner or Admin)
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم حذف التعليق بنجاح"
}
```

---

### 6.2 تعليقات الدروس

نفس الـ Endpoints مع `/lessons/` بدلاً من `/poems/`:

```
GET    /api/lessons/{id}/comments
POST   /api/lessons/{id}/comments
PUT    /api/lessons/comments/{comment_id}
DELETE /api/lessons/comments/{comment_id}
```

---

### 6.3 تعليقات الأقوال

نفس الـ Endpoints مع `/sayings/` بدلاً من `/poems/`:

```
GET    /api/sayings/{id}/comments
POST   /api/sayings/{id}/comments
PUT    /api/sayings/comments/{comment_id}
DELETE /api/sayings/comments/{comment_id}
```

---

<a name="tips"></a>
## 🎯 7. Examples & Tips

### Status Codes

| Code | المعنى | متى يحدث |
|------|--------|-----------|
| 200 | OK | العملية نجحت |
| 201 | Created | تم إنشاء المورد |
| 400 | Bad Request | خطأ في البيانات |
| 401 | Unauthorized | يتطلب تسجيل دخول |
| 403 | Forbidden | ممنوع (لا صلاحية) |
| 404 | Not Found | المورد غير موجود |
| 422 | Validation Error | فشل Validation |
| 500 | Server Error | خطأ في الخادم |

---

### Authentication Header

**لجميع Endpoints التي تتطلب Authorization:**

```
Authorization: Bearer YOUR_TOKEN
```

**مثال cURL:**
```bash
curl -H "Authorization: Bearer 1|abcdef..." \
     http://localhost:8000/api/user
```

**مثال JavaScript:**
```javascript
fetch('http://localhost:8000/api/user', {
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Accept': 'application/json'
  }
})
```

---

### File Upload

**في Postman:**
```
POST /api/AddPoem
Body: form-data

Key: title              Type: Text
Key: content            Type: Text
Key: pdf_source[]       Type: File
Key: pdf_source[]       Type: File
Key: audio_source[]     Type: File
```

**ملاحظات:**
- استخدم `[]` للملفات المتعددة
- Content-Type: multipart/form-data
- حدود الحجم: PDF 10MB, Audio 50MB, Video 100MB

---

### Pagination

```bash
GET /api/poems/getall?page=1
GET /api/poems/getall?page=2
```

**Response:**
```json
{
  "current_page": 2,
  "data": [...],
  "per_page": 15,
  "total": 100,
  "last_page": 7
}
```

---

### أمثلة عملية كاملة

#### 1. التسجيل والدخول
```bash
# تسجيل
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "أحمد",
    "email": "ahmed@test.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# تسجيل دخول
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "ahmed@test.com",
    "password": "password123"
  }'
```

#### 2. إنشاء قصيدة
```bash
curl -X POST http://localhost:8000/api/AddPoem \
  -H "Authorization: Bearer TOKEN" \
  -F "title=قصيدة جديدة" \
  -F "content=محتوى..." \
  -F "author=الشاعر"
```

#### 3. عرض والبحث
```bash
# عرض كل القصائد
curl http://localhost:8000/api/poems/getall

# البحث
curl "http://localhost:8000/api/poems/search?keyword=حب&author=نزار"
```

#### 4. المفضلة والتعليقات
```bash
# إضافة للمفضلة
curl -X POST http://localhost:8000/api/FavoritePoem/1 \
  -H "Authorization: Bearer TOKEN"

# إضافة تعليق
curl -X POST http://localhost:8000/api/poems/1/comments \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content": "تعليق رائع"}'
```

#### 5. نظام موافقة المشاركات
```bash
# User ينشئ مشاركة
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content": "مشاركتي..."}'

# Admin يوافق
curl -X POST http://localhost:8000/api/posts/1/approve \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"is_approved": true}'
```

---

## 📊 ملخص الصلاحيات

### القصائد & الدروس:
```
إنشاء  → Admin فقط
تحديث  → المالك أو Admin
حذف    → المالك أو Admin
```

### الأقوال:
```
إنشاء  → Admin فقط
تحديث  → المالك فقط
حذف    → المالك أو Admin
```

### المشاركات:
```
إنشاء     → مستخدم مسجل (is_approved = false)
موافقة    → Admin فقط
تحديث     → المالك فقط
حذف       → المالك أو Admin
```

### التعليقات:
```
إضافة    → مستخدم مسجل
تحديث    → صاحب التعليق
حذف      → صاحب التعليق أو Admin
```

---

## 📈 الإحصائيات

- **إجمالي Endpoints:** 55
- **Public:** 20
- **Protected:** 35
- **أنواع المحتوى:** 4 (قصائد، دروس، أقوال، مشاركات)
- **أنواع الملفات:** 3 (PDF, Audio, Video)

---

**✅ API Documentation كامل - جاهز للاستخدام!**
