# 🎬 نظام الأنشطة (Activities System)

## 📋 نظرة عامة

نظام الأنشطة هو قسم خاص لعرض فيديوهات الأنشطة مع وصف لكل نشاط وتاريخ اختياري.

### الميزات
- ✅ رفع فيديو واحد لكل نشاط
- ✅ عنوان ووصف للنشاط
- ✅ تاريخ اختياري للنشاط
- ✅ **Admin فقط** يمكنه إضافة/تعديل/حذف الأنشطة
- ✅ نظام المفضلة (جميع المستخدمين)
- ✅ **لا يوجد تعليقات** على الأنشطة
- ✅ بحث متقدم مع Pagination
- ✅ مصادقة اختيارية

---

## 🗄️ هيكل قاعدة البيانات

### جدول activities
```sql
activities
├── id (PK)
├── title (string) - عنوان النشاط
├── description (text, nullable) - وصف النشاط
├── video_path (string) - مسار الفيديو
├── activity_date (date, nullable) - تاريخ النشاط
├── user_id (FK -> users.id) - الأدمن الذي أضاف النشاط
├── created_at
└── updated_at
```

### جدول favorites (محدث)
```sql
favorites
├── id (PK)
├── user_id (FK -> users.id)
├── poem_id (FK, nullable)
├── lesson_id (FK, nullable)
├── saying_id (FK, nullable)
├── activity_id (FK, nullable) ← جديد
├── created_at
└── updated_at
```

---

## 🔐 الصلاحيات

| العملية | زائر | User | Admin |
|---------|------|------|-------|
| عرض الأنشطة | ✅ | ✅ | ✅ |
| البحث | ✅ | ✅ | ✅ |
| عرض التفاصيل | ✅ | ✅ | ✅ |
| إضافة نشاط | ❌ | ❌ | ✅ |
| تعديل نشاط | ❌ | ❌ | ✅ |
| حذف نشاط | ❌ | ❌ | ✅ |
| المفضلة | ❌ | ✅ | ✅ |

---

## 📡 APIs الكاملة

### 1. عرض جميع الأنشطة
**Endpoint**: `GET /api/activities/getall`

**Query Parameters**:
- `page` (optional): رقم الصفحة (default: 1)

**Headers** (Optional):
```
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "message": "تم جلب الأنشطة بنجاح",
  "meta": {
    "page_number": 1,
    "total_pages": 3,
    "has_previous": false,
    "has_next": true,
    "total_items": 45
  },
  "data": [
    {
      "id": 1,
      "title": "نشاط رمضاني",
      "date": "2024-03-15",
      "video_url": "/storage/activities/videos/video1.mp4",
      "is_favorited": false
    }
  ]
}
```

---

### 2. البحث في الأنشطة
**Endpoint**: `GET /api/activities/search`

**Query Parameters**:
- `keyword` (optional): البحث في العنوان والوصف
- `year` (optional): السنة (مثال: 2024)
- `month` (optional): الشهر (1-12)
- `date` (optional): تاريخ محدد (YYYY-MM-DD)
- `date_comparison` (optional): نوع المقارنة (=, >, <, >=, <=)
- `page` (optional): رقم الصفحة

**مثال 1: البحث بالكلمة المفتاحية**
```bash
GET /api/activities/search?keyword=رمضان
```

**مثال 2: البحث حسب السنة**
```bash
GET /api/activities/search?year=2024
```

**مثال 3: بحث مركب**
```bash
GET /api/activities/search?keyword=رمضان&year=2024&page=1
```

**Response**: نفس تنسيق `getall`

---

### 3. عرض تفاصيل نشاط
**Endpoint**: `GET /api/activities/{activity_id}`

**Headers** (Optional):
```
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "message": "تم جلب تفاصيل النشاط بنجاح",
  "data": {
    "id": 1,
    "title": "نشاط رمضاني",
    "description": "نشاط خيري في شهر رمضان المبارك",
    "date": "2024-03-15",
    "video_url": "/storage/activities/videos/video1.mp4",
    "is_favorited": true,
    "author_name": "الشيخ محمد"
  }
}
```

---

### 4. إضافة نشاط جديد (Admin فقط)
**Endpoint**: `POST /api/AddActivity`

**Headers**:
```
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data
```

**Request Body** (Form Data):
```
title: "نشاط رمضاني"
description: "وصف النشاط"
activity_date: "2024-03-15"
video: [file.mp4]
```

**Validation**:
- `title`: required, string, max:255
- `description`: nullable, string
- `activity_date`: nullable, date
- `video`: required, file, mimes:mp4,mov,avi,wmv, max:102400 (100MB)

**Response**:
```json
{
  "success": true,
  "message": "تم إنشاء النشاط بنجاح",
  "data": {
    "activity": {
      "id": 10,
      "title": "نشاط رمضاني",
      "description": "وصف النشاط",
      "date": "2024-03-15",
      "video_url": "/storage/activities/videos/video1.mp4"
    }
  }
}
```

**مثال cURL**:
```bash
curl -X POST http://localhost:8000/api/AddActivity \
  -H "Authorization: Bearer {admin_token}" \
  -F "title=نشاط رمضاني" \
  -F "description=نشاط خيري" \
  -F "activity_date=2024-03-15" \
  -F "video=@/path/to/video.mp4"
```

---

### 5. تحديث نشاط (Admin فقط)
**Endpoint**: `POST /api/activities/{activity_id}/update`

**Headers**:
```
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data
```

**Request Body**: نفس `AddActivity` لكن جميع الحقول optional

**ملاحظة**: إذا تم رفع فيديو جديد، سيتم حذف الفيديو القديم تلقائياً

**Response**:
```json
{
  "success": true,
  "message": "تم تحديث النشاط بنجاح",
  "data": {
    "activity": {
      "id": 10,
      "title": "نشاط محدث",
      "description": "وصف محدث",
      "date": "2024-03-20",
      "video_url": "/storage/activities/videos/new_video.mp4"
    }
  }
}
```

---

### 6. حذف نشاط (Admin فقط)
**Endpoint**: `DELETE /api/deleteActivity/{activity_id}`

**Headers**:
```
Authorization: Bearer {admin_token}
```

**Response**:
```json
{
  "success": true,
  "message": "تم حذف النشاط بنجاح"
}
```

**ملاحظة**: سيتم حذف الفيديو من التخزين وجميع المفضلة المرتبطة تلقائياً

---

### 7. إضافة/إزالة من المفضلة (Toggle)
**Endpoint**: `POST /api/FavoriteActivity/{activity_id}`

**Headers**:
```
Authorization: Bearer {token}
```

**Response (إضافة)**:
```json
{
  "success": true,
  "message": "تمت إضافة النشاط إلى المفضلة.",
  "is_favorited": true
}
```

**Response (إزالة)**:
```json
{
  "success": true,
  "message": "تمت إزالة النشاط من المفضلة.",
  "is_favorited": false
}
```

---

### 8. عرض الأنشطة المفضلة
**Endpoint**: `GET /api/activities/favorites`

**Headers**:
```
Authorization: Bearer {token}
```

**Query Parameters**:
- `page` (optional): رقم الصفحة

**Response**:
```json
{
  "success": true,
  "message": "تم جلب الأنشطة المفضلة بنجاح",
  "meta": {
    "page_number": 1,
    "total_pages": 2,
    "has_previous": false,
    "has_next": true,
    "total_items": 25
  },
  "data": [
    {
      "id": 1,
      "title": "نشاط رمضاني",
      "date": "2024-03-15",
      "video_url": "/storage/activities/videos/video1.mp4",
      "is_favorited": true
    }
  ]
}
```

---

## 🧪 أمثلة الاختبار

### السيناريو 1: زائر يتصفح الأنشطة

```bash
# 1. عرض جميع الأنشطة
curl http://localhost:8000/api/activities/getall

# 2. البحث عن نشاط
curl "http://localhost:8000/api/activities/search?keyword=رمضان"

# 3. عرض تفاصيل نشاط
curl http://localhost:8000/api/activities/1

# 4. محاولة إضافة للمفضلة (فشل - يحتاج Token)
curl -X POST http://localhost:8000/api/FavoriteActivity/1
# Result: 401 Unauthorized
```

---

### السيناريو 2: مستخدم مسجل

```bash
# 1. تسجيل الدخول
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@test.com","password":"password"}' \
  | jq -r '.data.token')

# 2. عرض الأنشطة مع is_favorited صحيح
curl http://localhost:8000/api/activities/getall \
  -H "Authorization: Bearer $TOKEN"

# 3. إضافة نشاط للمفضلة
curl -X POST http://localhost:8000/api/FavoriteActivity/1 \
  -H "Authorization: Bearer $TOKEN"

# 4. عرض الأنشطة المفضلة
curl http://localhost:8000/api/activities/favorites \
  -H "Authorization: Bearer $TOKEN"

# 5. إزالة من المفضلة
curl -X POST http://localhost:8000/api/FavoriteActivity/1 \
  -H "Authorization: Bearer $TOKEN"

# 6. محاولة إضافة نشاط (فشل - ليس Admin)
curl -X POST http://localhost:8000/api/AddActivity \
  -H "Authorization: Bearer $TOKEN" \
  -F "title=نشاط جديد"
# Result: 403 Unauthorized action!
```

---

### السيناريو 3: مدير (Admin)

```bash
# 1. تسجيل الدخول كـ Admin
ADMIN_TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}' \
  | jq -r '.data.token')

# 2. إضافة نشاط جديد
curl -X POST http://localhost:8000/api/AddActivity \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -F "title=نشاط رمضاني" \
  -F "description=نشاط خيري في رمضان" \
  -F "activity_date=2024-03-15" \
  -F "video=@/path/to/video.mp4"

# 3. تحديث النشاط
curl -X POST http://localhost:8000/api/activities/1/update \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -F "title=نشاط محدث" \
  -F "description=وصف جديد"

# 4. تحديث الفيديو فقط
curl -X POST http://localhost:8000/api/activities/1/update \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -F "video=@/path/to/new_video.mp4"

# 5. حذف النشاط
curl -X DELETE http://localhost:8000/api/deleteActivity/1 \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

---

### السيناريو 4: اختبار البحث المتقدم

```bash
# 1. بحث بالكلمة المفتاحية
curl "http://localhost:8000/api/activities/search?keyword=رمضان"

# 2. بحث حسب السنة
curl "http://localhost:8000/api/activities/search?year=2024"

# 3. بحث حسب الشهر
curl "http://localhost:8000/api/activities/search?month=3"

# 4. بحث بتاريخ محدد
curl "http://localhost:8000/api/activities/search?date=2024-03-15"

# 5. بحث بتاريخ أكبر من
curl "http://localhost:8000/api/activities/search?date=2024-03-01&date_comparison=>="

# 6. بحث مركب
curl "http://localhost:8000/api/activities/search?keyword=رمضان&year=2024&month=3"
```

---

## 📊 الفروقات عن القصائد والدروس

| الميزة | القصائد/الدروس | الأنشطة |
|--------|----------------|---------|
| المصادر | PDF, Audio, Video (متعدد) | Video فقط (واحد) |
| التعليقات | ✅ يوجد | ❌ لا يوجد |
| المفضلة | ✅ يوجد | ✅ يوجد |
| الخصوصية | ✅ يوجد (is_private) | ❌ لا يوجد (الكل عام) |
| من يضيف | Admin فقط | Admin فقط |
| من يعدل | Admin أو المالك | Admin فقط |
| من يحذف | Admin أو المالك | Admin فقط |

---

## ✅ Checklist الاختبار

### APIs الأساسية
- [ ] عرض جميع الأنشطة بدون Token
- [ ] عرض جميع الأنشطة مع Token
- [ ] Pagination (3 صفحات على الأقل)
- [ ] عرض تفاصيل نشاط

### البحث
- [ ] بحث بالكلمة المفتاحية
- [ ] بحث حسب السنة
- [ ] بحث حسب الشهر
- [ ] بحث حسب التاريخ
- [ ] بحث مركب

### الصلاحيات
- [ ] User يحاول إضافة نشاط (فشل)
- [ ] Admin يضيف نشاط (نجاح)
- [ ] User يحاول تعديل نشاط (فشل)
- [ ] Admin يعدل نشاط (نجاح)
- [ ] User يحاول حذف نشاط (فشل)
- [ ] Admin يحذف نشاط (نجاح)

### المفضلة
- [ ] إضافة للمفضلة (Toggle ON)
- [ ] إزالة من المفضلة (Toggle OFF)
- [ ] عرض الأنشطة المفضلة
- [ ] is_favorited صحيح في getall

### الفيديو
- [ ] رفع فيديو mp4
- [ ] رفع فيديو mov
- [ ] رفع فيديو كبير (>100MB - فشل)
- [ ] تحديث الفيديو (حذف القديم)
- [ ] حذف النشاط (حذف الفيديو)

---

## 🎓 ملاحظات مهمة

### 1. حجم الفيديو
- الحد الأقصى: **100MB**
- الصيغ المدعومة: **mp4, mov, avi, wmv**

### 2. التخزين
- المسار: `storage/activities/videos/`
- يجب تشغيل: `php artisan storage:link`

### 3. الحذف التلقائي
عند حذف نشاط:
- ✅ يُحذف الفيديو من التخزين
- ✅ تُحذف جميع المفضلة المرتبطة (cascade)

### 4. التحديث
عند تحديث الفيديو:
- ✅ يُحذف الفيديو القديم تلقائياً
- ✅ يُرفع الفيديو الجديد

### 5. المصادقة الاختيارية
- بدون Token: `is_favorited = false` دائماً
- مع Token: `is_favorited` صحيح حسب المفضلة

---

## 🚀 البدء السريع

```bash
# 1. تشغيل الـ migrations
php artisan migrate

# 2. ربط التخزين
php artisan storage:link

# 3. تسجيل الدخول كـ Admin
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}'

# 4. إضافة نشاط
curl -X POST http://localhost:8000/api/AddActivity \
  -H "Authorization: Bearer {admin_token}" \
  -F "title=نشاط تجريبي" \
  -F "description=وصف النشاط" \
  -F "activity_date=2024-03-15" \
  -F "video=@video.mp4"

# 5. عرض الأنشطة
curl http://localhost:8000/api/activities/getall
```

---

**تم إنشاء نظام الأنشطة بنجاح! 🎉**
