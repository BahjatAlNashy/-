# مرجع سريع - API Endpoints

## 🔐 Authentication
```
POST   /api/register                    تسجيل مستخدم جديد
POST   /api/login                        تسجيل الدخول
GET    /api/user                         ✅ معلومات المستخدم
```

## 📖 القصائد (Poems)

### CRUD
```
POST   /api/AddPoem                      ✅ إنشاء قصيدة
GET    /api/poems/getall                 عرض الكل
GET    /api/poems/{id}                   عرض واحدة
POST   /api/poems/{id}/update            ✅ تحديث
DELETE /api/deletePoem/{id}              ✅ حذف
```

### المصادر
```
POST   /api/AddSourcePoem/{id}           ✅ إضافة مصادر
DELETE /api/deleteSource/{id}            ✅ حذف مصدر
```

### البحث والمفضلة
```
GET    /api/poems/search                 بحث
POST   /api/FavoritePoem/{id}            ✅ مفضلة
GET    /api/poems/favorites              ✅ عرض المفضلة
```

### التعليقات
```
GET    /api/poems/{id}/comments          عرض
POST   /api/poems/{id}/comments          ✅ إضافة
PUT    /api/poems/comments/{id}          ✅ تحديث (صاحبه)
DELETE /api/poems/comments/{id}          ✅ حذف (صاحبه/Admin)
```

## 📚 الدروس (Lessons)

### CRUD
```
POST   /api/AddLesson                    ✅ إنشاء (Admin)
GET    /api/lessons/getall               عرض الكل
GET    /api/lessons/{id}                 عرض واحد
POST   /api/lessons/{id}/update          ✅ تحديث
DELETE /api/deleteLesson/{id}            ✅ حذف
```

### المصادر
```
POST   /api/AddSourceLesson/{id}         ✅ إضافة مصادر
DELETE /api/deleteLessonSource/{id}      ✅ حذف مصدر
```

### البحث والمفضلة
```
GET    /api/lessons/search               بحث
POST   /api/FavoriteLesson/{id}          ✅ مفضلة
GET    /api/lessons/favorites            ✅ عرض المفضلة
```

### التعليقات
```
GET    /api/lessons/{id}/comments        عرض
POST   /api/lessons/{id}/comments        ✅ إضافة
PUT    /api/lessons/comments/{id}        ✅ تحديث (صاحبه)
DELETE /api/lessons/comments/{id}        ✅ حذف (صاحبه/Admin)
```

## 📖 الأقوال (Sayings)

### CRUD
```
POST   /api/AddSaying                    ✅ إنشاء (Admin فقط)
GET    /api/sayings/getall               عرض الكل (?type=saying|supplication)
GET    /api/sayings/search               بحث (?type, keyword)
GET    /api/sayings/favorites            ✅ عرض المفضلة
GET    /api/sayings/{id}                 عرض واحد
POST   /api/sayings/{id}/update          ✅ تحديث (المالك فقط)
DELETE /api/deleteSaying/{id}            ✅ حذف (المالك أو Admin)
POST   /api/FavoriteSaying/{id}          ✅ مفضلة
```

### الصلاحيات
```
إنشاء  → Admin فقط
تحديث  → المالك فقط (حتى Admin ممنوع!)
حذف    → المالك أو Admin
```

### التعليقات
```
GET    /api/sayings/{id}/comments        عرض
POST   /api/sayings/{id}/comments        ✅ إضافة
PUT    /api/sayings/comments/{id}        ✅ تحديث (صاحبه)
DELETE /api/sayings/comments/{id}        ✅ حذف (صاحبه/Admin)
```

---

## 📝 مشاركات الزوار (Posts)

### CRUD
```
GET    /api/posts                        عرض الموافق عليها
GET    /api/posts/search                 بحث (?keyword)
GET    /api/posts/my-posts               ✅ مشاركاتي
GET    /api/posts/pending                ✅ في انتظار الموافقة (Admin)
GET    /api/posts/{id}                   عرض واحدة
POST   /api/posts                        ✅ إنشاء (مستخدم مسجل)
POST   /api/posts/{id}/update            ✅ تحديث (المالك فقط)
POST   /api/posts/{id}/approve           ✅ موافقة/رفض (Admin)
DELETE /api/posts/{id}                   ✅ حذف (المالك أو Admin)
```

### الصلاحيات
```
إنشاء     → مستخدم مسجل (is_approved = false)
موافقة    → Admin فقط
تحديث     → المالك فقط
حذف       → المالك أو Admin
عرض       → الجميع (الموافق عليها فقط)
```

**ملاحظة:** نظام موافقة Admin إجباري • بدون تعليقات • بدون مفضلة

---

## 🔑 الرموز
- ✅ = يتطلب Bearer Token
- بدون = Public

---

## 📤 رفع ملفات متعددة (Postman)
```
Key: pdf_source[]    Type: File    [ملف 1]
Key: pdf_source[]    Type: File    [ملف 2]
Key: pdf_source[]    Type: File    [ملف 3]
```

## 🔒 قواعد الخصوصية
```
is_private = false → الجميع
is_private = true  → مسجلين فقط
```

## 📝 Base URL
```
http://localhost:8000/api
```

## 🔐 Headers
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json (للـ JSON)
Content-Type: multipart/form-data (للملفات)
```
