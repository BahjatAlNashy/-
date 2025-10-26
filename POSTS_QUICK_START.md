# 📝 مشاركات الزوار (Posts) - دليل سريع

## 📋 نظرة عامة

نظام **مشاركات الزوار** هو منصة بسيطة تسمح للمستخدمين المسجلين بمشاركة نصوص.

### ✨ الميزات
- ✅ CRUD كامل (إنشاء، قراءة، تحديث، حذف)
- ✅ Pagination
- ✅ البحث بالكلمة المفتاحية
- ✅ مستخدمين مسجلين فقط للإنشاء/التحديث/الحذف
- ✅ الجميع يمكنهم القراءة
- ❌ **بدون تعليقات** (نظام بسيط)
- ❌ **بدون مفضلة** (نظام بسيط)

---

## 🔑 جميع Endpoints

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| GET | `/api/posts` | ❌ | عرض المشاركات الموافق عليها |
| GET | `/api/posts/search` | ❌ | بحث في المشاركات الموافق عليها |
| GET | `/api/posts/my-posts` | ✅ | مشاركاتي (موافق + غير موافق) |
| GET | `/api/posts/pending` | ✅ | المشاركات في انتظار الموافقة (Admin) |
| GET | `/api/posts/{id}` | ❌ | عرض مشاركة واحدة |
| POST | `/api/posts` | ✅ | إنشاء مشاركة جديدة |
| POST | `/api/posts/{id}/update` | ✅ | تحديث مشاركة (المالك) |
| POST | `/api/posts/{id}/approve` | ✅ | الموافقة/رفض (Admin فقط) |
| DELETE | `/api/posts/{id}` | ✅ | حذف مشاركة (المالك أو Admin) |

---

## 📖 الاستخدام التفصيلي

### 1️⃣ عرض جميع المشاركات

```bash
GET /api/posts
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "content": "مشاركة رائعة من الزائر...",
        "author_name": "أحمد",
        "author_id": 5,
        "created_at": "منذ دقيقتين"
      }
    ],
    "per_page": 15,
    "total": 50
  }
}
```

---

### 2️⃣ عرض مشاركة واحدة

```bash
GET /api/posts/1
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "content": "مشاركة رائعة من الزائر...",
    "author_name": "أحمد",
    "author_id": 5,
    "created_at": "2024-10-14 15:30:00"
  }
}
```

---

### 3️⃣ إنشاء مشاركة جديدة

```bash
POST /api/posts
Headers: Authorization: Bearer YOUR_TOKEN
Body: form-data
  content: "هذه مشاركتي الجديدة..."
```

**Validation:**
- `content`: required, string, min:10, max:2000

**Response:**
```json
{
  "success": true,
  "message": "تم إنشاء المشاركة بنجاح",
  "data": {
    "id": 10,
    "content": "هذه مشاركتي الجديدة...",
    "author_name": "أحمد",
    "author_id": 5,
    "created_at": "2024-10-14 15:30:00"
  }
}
```

---

### 4️⃣ تحديث مشاركة

```bash
POST /api/posts/1/update
Headers: Authorization: Bearer YOUR_TOKEN
Body: form-data
  content: "مشاركة محدثة..."
```

**الصلاحيات:** المالك فقط

**Response:**
```json
{
  "success": true,
  "message": "تم التحديث بنجاح.",
  "data": {
    "id": 1,
    "content": "مشاركة محدثة...",
    "author_name": "أحمد",
    "author_id": 5,
    "created_at": "2024-10-14 15:30:00"
  }
}
```

---

### 5️⃣ حذف مشاركة

```bash
DELETE /api/posts/1
Headers: Authorization: Bearer YOUR_TOKEN
```

**الصلاحيات:** المالك أو Admin

**Response:**
```json
{
  "success": true,
  "message": "تم الحذف بنجاح."
}
```

---

### 6️⃣ البحث

```bash
GET /api/posts/search?keyword=رائع
```

**Response:**
```json
{
  "success": true,
  "count": 5,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "content": "مشاركة رائعة...",
        "author_name": "أحمد",
        "created_at": "منذ ساعة"
      }
    ],
    "total": 5
  }
}
```

---

### 7️⃣ مشاركاتي (My Posts)

```bash
GET /api/posts/my-posts
Headers: Authorization: Bearer YOUR_TOKEN
```

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "content": "مشاركتي الأولى...",
        "is_approved": true,
        "status": "موافق عليها",
        "created_at": "منذ ساعة"
      },
      {
        "id": 2,
        "content": "مشاركتي الثانية...",
        "is_approved": false,
        "status": "في انتظار الموافقة",
        "created_at": "منذ 5 دقائق"
      }
    ]
  }
}
```

---

### 8️⃣ المشاركات في انتظار الموافقة (Admin فقط)

```bash
GET /api/posts/pending
Headers: Authorization: Bearer ADMIN_TOKEN
```

**Response:**
```json
{
  "success": true,
  "count": 10,
  "data": {
    "data": [
      {
        "id": 5,
        "content": "مشاركة في انتظار الموافقة...",
        "author_name": "محمد",
        "author_id": 10,
        "created_at": "منذ دقيقتين"
      }
    ]
  }
}
```

---

### 9️⃣ الموافقة على مشاركة (Admin فقط)

```bash
# الموافقة
POST /api/posts/5/approve
Headers: Authorization: Bearer ADMIN_TOKEN
Body: form-data
  is_approved: true

# الرفض
POST /api/posts/5/approve
Headers: Authorization: Bearer ADMIN_TOKEN
Body: form-data
  is_approved: false
```

**Response:**
```json
{
  "success": true,
  "message": "تم الموافقة على المشاركة بنجاح.",
  "data": {
    "id": 5,
    "is_approved": true
  }
}
```

---

## 🔑 الصلاحيات

### إنشاء مشاركة:
- **مستخدم مسجل** ✅
- **زائر** ❌ 401 Unauthorized

### تحديث مشاركة:
- **المالك فقط** ✅
- **Admin** ❌ (حتى Admin لا يمكنه التحديث)
- **مستخدم آخر** ❌ 403 Forbidden

### حذف مشاركة:
- **المالك** ✅
- **Admin** ✅
- **مستخدم آخر** ❌ 403 Forbidden

### عرض وبحث:
- **الجميع** ✅ (المشاركات الموافق عليها فقط)

### الموافقة على مشاركة:
- **Admin فقط** ✅
- **المستخدم العادي** ❌ 403 Forbidden

**ملخص:**
```
إنشاء     → مستخدم مسجل (is_approved = false)
موافقة    → Admin فقط
تحديث     → المالك فقط
حذف       → المالك أو Admin
عرض       → الجميع (الموافق عليها فقط)
```

---

## 🔄 سير عمل المشاركة (Workflow)

### 1. المستخدم ينشئ مشاركة:
```bash
POST /api/posts
Headers: Authorization: Bearer USER_TOKEN
Body: { "content": "مشاركة جديدة..." }
```
→ **الحالة:** `is_approved = false` (في انتظار الموافقة)

### 2. المستخدم يرى مشاركته في "مشاركاتي":
```bash
GET /api/posts/my-posts
Headers: Authorization: Bearer USER_TOKEN
```
→ **النتيجة:** يرى مشاركته مع status = "في انتظار الموافقة"

### 3. Admin يراجع المشاركات المعلقة:
```bash
GET /api/posts/pending
Headers: Authorization: Bearer ADMIN_TOKEN
```
→ **النتيجة:** يرى جميع المشاركات غير الموافق عليها

### 4. Admin يوافق على المشاركة:
```bash
POST /api/posts/1/approve
Headers: Authorization: Bearer ADMIN_TOKEN
Body: { "is_approved": true }
```
→ **الحالة:** `is_approved = true`

### 5. المشاركة تظهر للجميع:
```bash
GET /api/posts
```
→ **النتيجة:** المشاركة تظهر في القائمة العامة

---

## ✅ Validation Rules

### عند الإنشاء والتحديث:
- `content`: required, string, min:10, max:2000

---

## 📊 جدول المقارنة

| الميزة | القصائد/الدروس | الأقوال | المشاركات |
|--------|----------------|---------|-----------|
| **الإنشاء** | Admin فقط | Admin فقط | مستخدم مسجل |
| **التحديث** | المالك أو Admin | المالك فقط | المالك فقط |
| **الحذف** | المالك أو Admin | المالك أو Admin | المالك أو Admin |
| **التعليقات** | ✅ | ✅ | ❌ |
| **المفضلة** | ✅ | ✅ | ❌ |
| **الملفات** | ✅ | ❌ | ❌ |
| **الخصوصية** | ✅ | ✅ | ❌ |

---

## 📝 أمثلة Postman

### إنشاء مشاركة:
```
POST http://localhost:8000/api/posts
Headers:
  Authorization: Bearer YOUR_TOKEN
Body: form-data
  content: هذه مشاركتي الأولى في الموقع...
```

### تحديث مشاركة:
```
POST http://localhost:8000/api/posts/1/update
Headers:
  Authorization: Bearer YOUR_TOKEN
Body: form-data
  content: مشاركة محدثة بمعلومات جديدة...
```

### حذف مشاركة:
```
DELETE http://localhost:8000/api/posts/1
Headers:
  Authorization: Bearer YOUR_TOKEN
```

### البحث:
```
GET http://localhost:8000/api/posts/search?keyword=تجربة
```

---

## ⚠️ ملاحظات مهمة

1. ✅ **بدون تعليقات**: المشاركات لا تدعم التعليقات (نظام بسيط)
2. ✅ **بدون مفضلة**: المشاركات لا تدعم المفضلة
3. ✅ **عامة للجميع**: جميع المشاركات عامة (لا يوجد is_private)
4. ✅ **نص فقط**: content نصي فقط، بدون ملفات
5. ✅ **حد النص**: min: 10 حرف، max: 2000 حرف

---

## 🚀 الخطوة التالية

تشغيل Migration:
```bash
php artisan migrate
```

---

**جاهز للاستخدام! 🎉**
