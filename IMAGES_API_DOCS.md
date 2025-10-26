# 📸 API Documentation - الصور (Images)

**Base URL:** `http://localhost:8000/api`

---

## 📋 جدول المحتويات

1. [رفع صورة (Admin)](#upload)
2. [عرض جميع الصور / البحث](#search)
3. [عرض صورة واحدة](#show)
4. [تحديث صورة (Admin)](#update)
5. [حذف صورة (Admin)](#delete)
6. [إضافة/إزالة من المفضلة](#favorite)
7. [عرض الصور المفضلة](#favorites)

---

<a name="upload"></a>
## 1. رفع صورة جديدة (Admin فقط)

```
POST /api/images
Authorization: Bearer ADMIN_TOKEN
Content-Type: multipart/form-data
```

**Request Body:**
```
title: "عنوان الصورة" (optional)
description: "وصف الصورة..." (optional)
image: [file] (required)
is_private: false (optional, boolean)
```

**Validation:**
- `image`: required, image, mimes:jpeg,png,jpg,gif,webp, max:5MB
- `title`: optional, string, max:255
- `description`: optional, string
- `is_private`: optional, boolean

**Success Response (201):**
```json
{
  "success": true,
  "message": "تم رفع الصورة بنجاح",
  "data": {
    "id": 1,
    "title": "عنوان الصورة",
    "description": "وصف...",
    "image_path": "images/abc123.jpg",
    "image_url": "/storage/images/abc123.jpg",
    "user_id": 1,
    "is_private": false,
    "created_at": "2024-10-15 20:00:00"
  }
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "غير مصرح لك. Admin فقط."
}
```

---

<a name="search"></a>
## 2. عرض جميع الصور / البحث

```
GET /api/images/search?keyword=منظر&page=1
```

**Query Parameters:**
- `keyword`: optional, string (يبحث في العنوان والوصف)
- `page`: optional, integer (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "message": "تم جلب الصور بنجاح",
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 45,
    "from": 1,
    "to": 15
  },
  "data": [
    {
      "id": 1,
      "title": "منظر طبيعي",
      "description": "صورة جميلة...",
      "image_url": "http://localhost:8000/storage/images/abc123.jpg",
      "is_private": false,
      "is_favorited": true,
      "uploaded_by": "أحمد",
      "created_at": "منذ ساعة"
    },
    {
      "id": 2,
      "title": "صورة أخرى",
      "description": null,
      "image_url": "http://localhost:8000/storage/images/xyz789.jpg",
      "is_private": false,
      "is_favorited": false,
      "uploaded_by": "محمد",
      "created_at": "منذ يومين"
    }
  ]
}
```

**ملاحظات:**
- ✅ بدون Token: يعرض الصور العامة فقط (`is_private: false`)
- ✅ مع Token: يعرض العامة والخاصة
- ✅ بدون `keyword`: يعرض جميع الصور
- ✅ مع `keyword`: يبحث في العنوان والوصف

---

<a name="show"></a>
## 3. عرض صورة واحدة

```
GET /api/images/{id}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "منظر طبيعي",
    "description": "صورة جميلة للطبيعة...",
    "image_url": "http://localhost:8000/storage/images/abc123.jpg",
    "is_private": false,
    "is_favorited": true,
    "uploaded_by": "أحمد",
    "created_at": "2024-10-15 20:00:00"
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "الصورة غير موجودة"
}
```

**Error Response (403 - Private):**
```json
{
  "success": false,
  "message": "هذه الصورة خاصة. يجب تسجيل الدخول."
}
```

---

<a name="update"></a>
## 4. تحديث صورة (Admin فقط)

```
POST /api/images/{id}/update
Authorization: Bearer ADMIN_TOKEN
Content-Type: multipart/form-data
```

**Request Body:**
```
title: "عنوان محدث" (optional)
description: "وصف محدث..." (optional)
image: [file جديد] (optional)
is_private: true (optional)
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "تم تحديث الصورة بنجاح",
  "data": {
    "id": 1,
    "title": "عنوان محدث",
    "description": "وصف محدث...",
    "image_url": "/storage/images/new123.jpg",
    "is_private": true
  }
}
```

**ملاحظات:**
- إذا تم رفع صورة جديدة، يتم حذف الصورة القديمة تلقائياً
- يمكن تحديث العنوان والوصف فقط بدون تغيير الصورة

---

<a name="delete"></a>
## 5. حذف صورة (Admin فقط)

```
DELETE /api/images/{id}
Authorization: Bearer ADMIN_TOKEN
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "تم حذف الصورة بنجاح"
}
```

**ملاحظات:**
- يتم حذف الملف من التخزين تلقائياً
- يتم حذف الصورة من المفضلة لجميع المستخدمين

---

<a name="favorite"></a>
## 6. إضافة/إزالة من المفضلة (Toggle)

```
POST /api/images/{id}/favorite
Authorization: Bearer TOKEN
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "تمت إضافة الصورة إلى المفضلة",
  "is_favorited": true
}
```

**أو:**
```json
{
  "success": true,
  "message": "تمت إزالة الصورة من المفضلة",
  "is_favorited": false
}
```

---

<a name="favorites"></a>
## 7. عرض الصور المفضلة

```
GET /api/images/favorites/my?page=1
Authorization: Bearer TOKEN
```

**Success Response (200):**
```json
{
  "success": true,
  "count": 10,
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 10
  },
  "data": [
    {
      "id": 1,
      "title": "صورة مفضلة",
      "description": "وصف...",
      "image_url": "http://localhost:8000/storage/images/abc123.jpg",
      "is_private": false,
      "is_favorited": true,
      "uploaded_by": "أحمد",
      "created_at": "منذ ساعة"
    }
  ]
}
```

---

## 🎯 أمثلة عملية

### 1. Admin يرفع صورة

```bash
curl -X POST http://localhost:8000/api/images \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -F "title=منظر طبيعي" \
  -F "description=صورة جميلة للطبيعة" \
  -F "image=@/path/to/image.jpg" \
  -F "is_private=false"
```

---

### 2. عرض جميع الصور

```bash
# بدون Token (عامة فقط)
curl http://localhost:8000/api/images/search

# مع Token (عامة + خاصة)
curl http://localhost:8000/api/images/search \
  -H "Authorization: Bearer TOKEN"
```

---

### 3. البحث في الصور

```bash
curl "http://localhost:8000/api/images/search?keyword=منظر&page=1"
```

---

### 4. إضافة للمفضلة

```bash
curl -X POST http://localhost:8000/api/images/1/favorite \
  -H "Authorization: Bearer TOKEN"
```

---

### 5. عرض المفضلة

```bash
curl http://localhost:8000/api/images/favorites/my \
  -H "Authorization: Bearer TOKEN"
```

---

### 6. تحديث صورة

```bash
curl -X POST http://localhost:8000/api/images/1/update \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -F "title=عنوان جديد" \
  -F "description=وصف جديد" \
  -F "is_private=true"
```

---

### 7. حذف صورة

```bash
curl -X DELETE http://localhost:8000/api/images/1 \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

---

## 📊 ملخص الصلاحيات

| العملية | الصلاحية المطلوبة |
|---------|-------------------|
| رفع صورة | Admin فقط |
| تحديث صورة | Admin فقط |
| حذف صورة | Admin فقط |
| عرض الصور | الجميع (عامة)، مسجلين (عامة+خاصة) |
| إضافة للمفضلة | مستخدم مسجل |
| عرض المفضلة | مستخدم مسجل |

---

## 📝 ملاحظات مهمة

### 1. أنواع الصور المدعومة:
- JPEG, PNG, JPG, GIF, WEBP

### 2. الحد الأقصى للحجم:
- 5MB لكل صورة

### 3. التخزين:
- المسار: `storage/app/public/images/`
- URL: `http://localhost:8000/storage/images/filename.jpg`

### 4. الخصوصية:
- `is_private: false` → يراها الجميع
- `is_private: true` → يراها المسجلين فقط

---

## ✅ الخطوات التالية:

1. **تشغيل Migration:**
```bash
php artisan migrate
```

2. **التأكد من Storage Link:**
```bash
php artisan storage:link
```

3. **اختبار في Postman:**
- رفع صورة كـ Admin
- عرض الصور
- إضافة للمفضلة

---

**قسم الصور جاهز! 🎉**
