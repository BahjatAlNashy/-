# 📖 نظام الأقوال - دليل سريع

## 🎯 نظرة عامة

نظام بسيط للأقوال مع نوعين:
- **قول مأثور** (type: saying)
- **ورد/ذكر** (type: supplication)

---

## 📊 الحقول

### جدول sayings:
```
id          - معرف
type        - النوع (saying أو supplication)
content     - المحتوى (نص)
is_private  - خاص أو عام
user_id     - صاحب القول
created_at  - تاريخ الإنشاء
updated_at  - تاريخ التحديث
```

---

## 🚀 البداية السريعة

### 1️⃣ تشغيل Migrations:
```bash
php artisan migrate
```

### 2️⃣ إنشاء قول مأثور:
```
POST /api/AddSaying
Headers: Authorization: Bearer TOKEN
Body: form-data
  type: saying
  content: الحكمة ضالة المؤمن
  is_private: false
```

### 3️⃣ إنشاء ورد/ذكر:
```
POST /api/AddSaying
Headers: Authorization: Bearer TOKEN
Body: form-data
  type: supplication
  content: سبحان الله وبحمده
  is_private: false
```

### 4️⃣ عرض الكل:
```
GET /api/sayings/getall
```

### 5️⃣ عرض الأقوال المأثورة فقط:
```
GET /api/sayings/getall?type=saying
```

### 6️⃣ عرض الأوراد فقط:
```
GET /api/sayings/getall?type=supplication
```

---

## 🔑 جميع Endpoints

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| POST | `/api/AddSaying` | ✅ | إنشاء قول جديد |
| GET | `/api/sayings/getall` | ❌ | عرض الكل |
| GET | `/api/sayings/search` | ❌ | بحث |
| GET | `/api/sayings/favorites` | ✅ | عرض المفضلة |
| GET | `/api/sayings/{id}` | ❌ | عرض قول واحد |
| POST | `/api/sayings/{id}/update` | ✅ | تحديث |
| DELETE | `/api/deleteSaying/{id}` | ✅ | حذف |
| POST | `/api/FavoriteSaying/{id}` | ✅ | مفضلة |
| GET | `/api/sayings/{id}/comments` | ❌ | عرض التعليقات |
| POST | `/api/sayings/{id}/comments` | ✅ | إضافة تعليق |
| PUT | `/api/sayings/comments/{id}` | ✅ | تحديث تعليق |
| DELETE | `/api/sayings/comments/{id}` | ✅ | حذف تعليق |

---

## 🔍 البحث

```bash
# البحث بكلمة مفتاحية (بدون تسجيل دخول - يرجع العامة فقط)
GET /api/sayings/search?keyword=حكمة

# البحث مع تسجيل دخول (يرجع العامة + الخاصة)
GET /api/sayings/search?keyword=حكمة
Headers: Authorization: Bearer TOKEN

# البحث في الأقوال المأثورة فقط
GET /api/sayings/search?type=saying&keyword=حكمة

# البحث في الأوراد فقط
GET /api/sayings/search?type=supplication&keyword=الله
```

**ملاحظة مهمة:**
- ✅ **بدون Token:** يرجع الأقوال العامة فقط (`is_private: false`)
- ✅ **مع Token:** يرجع جميع الأقوال (العامة + الخاصة)

---

## 💾 Response Examples

### Index (عرض الكل):
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
        "is_private": false,
        "is_favorited": false,
        "comments_count": 3,
        "author_name": "أحمد",
        "created_at": "منذ ساعة"
      },
      {
        "id": 2,
        "type": "supplication",
        "content": "سبحان الله وبحمده",
        "is_private": false,
        "is_favorited": true,
        "comments_count": 5,
        "author_name": "محمد",
        "created_at": "منذ يومين"
      }
    ],
    "per_page": 15,
    "total": 50
  }
}
```

### Show (عرض واحد):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "type": "saying",
    "content": "الحكمة ضالة المؤمن",
    "is_private": false,
    "is_favorited": false,
    "comments": [...],
    "comments_count": 3,
    "author_name": "أحمد",
    "created_at": "2024-10-14 14:30:00"
  }
}
```

---

## 💬 التعليقات

### عرض التعليقات:
```bash
GET /api/sayings/1/comments
```

### إضافة تعليق:
```bash
POST /api/sayings/1/comments
Headers: Authorization: Bearer TOKEN
Body: form-data
  content: تعليق رائع!
```

### تحديث تعليق:
```bash
PUT /api/sayings/comments/5
Headers: Authorization: Bearer TOKEN
Body: form-data
  content: تعليق محدث
```

### حذف تعليق:
```bash
DELETE /api/sayings/comments/5
Headers: Authorization: Bearer TOKEN
```

**ملاحظة:** فقط صاحب التعليق يمكنه تحديثه، وصاحب التعليق أو Admin يمكنهم حذفه.

---

## 🔐 الخصوصية

### في عرض قول واحد (show):
**`is_private: false` (عام):**
- ✅ الجميع يمكنهم الوصول

**`is_private: true` (خاص):**
- ✅ المستخدمين المسجلين فقط
- ❌ الزوار: 403 Forbidden

### في القوائم والبحث (index, search):
**بدون تسجيل دخول:**
- ✅ يرجع الأقوال العامة فقط
- ❌ لا يرجع الأقوال الخاصة

**مع تسجيل دخول:**
- ✅ يرجع جميع الأقوال (العامة + الخاصة)

**مثال:**
```bash
# بدون Token - يرجع العامة فقط
GET /api/sayings/getall
GET /api/sayings/search?keyword=حكمة

# مع Token - يرجع الكل
GET /api/sayings/getall
Headers: Authorization: Bearer YOUR_TOKEN
```

---

## 📝 أمثلة Postman

### إنشاء قول مأثور:
```
POST http://localhost:8000/api/AddSaying
Headers:
  Authorization: Bearer YOUR_TOKEN
Body: form-data
  type             | Text | saying
  content          | Text | العلم نور والجهل ظلام
  is_private       | Text | false
```

### إنشاء ورد/ذكر:
```
POST http://localhost:8000/api/AddSaying
Headers:
  Authorization: Bearer YOUR_TOKEN
Body: form-data
  type             | Text | supplication
  content          | Text | اللهم إني أعوذ بك من الهم والحزن
  is_private       | Text | false
```

### تحديث:
```
POST http://localhost:8000/api/sayings/1/update
Headers:
  Authorization: Bearer YOUR_TOKEN
Body: form-data
  content          | Text | محتوى جديد
  is_private       | Text | true
```

### إضافة للمفضلة:
```
POST http://localhost:8000/api/FavoriteSaying/1
Headers:
  Authorization: Bearer YOUR_TOKEN
```

---

## 🔑 الصلاحيات

### إنشاء قول:
- **Admin فقط** ✅
- المستخدمين العاديين: ❌ 403 Forbidden

### تحديث قول:
- **المالك فقط** ✅
- Admin: ❌ (حتى Admin لا يمكنه التحديث)
- مستخدم آخر: ❌ 403 Forbidden

### حذف قول:
- **المالك** ✅
- **Admin** ✅
- مستخدم آخر: ❌ 403 Forbidden

**ملخص:**
```
إنشاء  → Admin فقط
تحديث  → المالك فقط
حذف    → المالك أو Admin
```

---

## ✅ Validation Rules

### عند الإنشاء:
- `type`: required, in:saying,supplication
- `content`: required, string
- `is_private`: optional, boolean

### عند التحديث:
- `content`: required, string
- `is_private`: optional, boolean

---

## 🎉 كل شيء جاهز!

النظام بسيط ومباشر:
- ✅ نوعين في جدول واحد
- ✅ content فقط (بدون تعقيد)
- ✅ خاص أو عام
- ✅ CRUD كامل
- ✅ بحث وفلترة
- ✅ pagination
- ✅ مفضلة
- ✅ تعليقات (قريباً)

---

**ابدأ الآن! 🚀**
