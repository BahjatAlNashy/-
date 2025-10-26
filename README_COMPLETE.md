# 🕌 نظام إدارة المحتوى الإسلامي - Islamic Content Management System

## 📚 نظرة عامة

نظام شامل لإدارة المحتوى الإسلامي يتضمن:
- **القصائد** (Poems) مع مصادر متعددة
- **الدروس** (Lessons) التعليمية
- **الأقوال والأذكار** (Sayings & Supplications)
- **المشاركات** (Posts) مع نظام موافقة
- **التعليقات** (Comments) على المحتوى
- **نظام المفضلة** (Favorites)
- **بحث متقدم** مع Pagination

---

## 🚀 البدء السريع

### 1. تثبيت المشروع
```bash
# استنساخ المشروع
git clone <repository-url>
cd testWebsite

# تثبيت المكتبات
composer install

# نسخ ملف البيئة
cp .env.example .env

# توليد مفتاح التطبيق
php artisan key:generate

# إعداد قاعدة البيانات
php artisan migrate:fresh --seed

# ربط التخزين
php artisan storage:link

# تشغيل السيرفر
php artisan serve
```

### 2. إنشاء حسابات تجريبية

**Admin Account**:
```
Email: admin@test.com
Password: password
```

**User Account**:
```
Email: user@test.com
Password: password
```

### 3. اختبار سريع
```bash
# عرض القصائد
curl http://localhost:8000/api/poems/getall

# تسجيل الدخول
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@test.com","password":"password"}'
```

---

## 📖 الملفات التوثيقية

### 1. **SYSTEM_DOCUMENTATION.md** 📘
**الوثائق الكاملة للنظام**
- شرح مفصل لكل API
- هيكل قاعدة البيانات
- نظام المصادقة والصلاحيات
- أمثلة على كل endpoint
- جداول الحالات والشروط

**متى تستخدمه**: عندما تريد فهم كيفية عمل النظام بالتفصيل

### 2. **TESTING_GUIDE.md** 🧪
**دليل الاختبار الشامل**
- سيناريوهات اختبار كاملة
- Checklist للاختبار
- اختبارات الأداء
- حالات الأخطاء المتوقعة
- Postman Collection Structure

**متى تستخدمه**: عندما تريد اختبار النظام بشكل منهجي

### 3. **API_EXAMPLES.sh** 🔧
**أمثلة جاهزة للتنفيذ**
- سكريبت Bash تفاعلي
- 10 سيناريوهات اختبار جاهزة
- تنفيذ مباشر لجميع APIs
- ألوان وتنسيق جميل

**متى تستخدمه**: عندما تريد اختبار سريع ومباشر

---

## 🎯 الميزات الرئيسية

### 1. نظام المصادقة المتقدم
- ✅ تسجيل وتسجيل دخول آمن
- ✅ Laravel Sanctum Tokens
- ✅ صلاحيات متعددة (User, Admin)
- ✅ مصادقة اختيارية (Optional Authentication)

### 2. إدارة المحتوى الشاملة
- ✅ CRUD كامل لجميع أنواع المحتوى
- ✅ دعم المصادر المتعددة (PDF, Audio, Video)
- ✅ نظام الخصوصية (Public/Private)
- ✅ Soft Deletes

### 3. البحث المتقدم
- ✅ بحث بالكلمات المفتاحية
- ✅ فلترة حسب التاريخ (سنة، شهر، تاريخ محدد)
- ✅ فلترة حسب نوع المصدر
- ✅ بحث مركب (Multiple Filters)
- ✅ Pagination موحد (15 عنصر/صفحة)

### 4. نظام التفاعل
- ✅ تعليقات على المحتوى
- ✅ نظام المفضلة (Toggle System)
- ✅ مشاركات المستخدمين مع موافقة Admin

---

## 📊 هيكل API

### القصائد (Poems)
```
GET    /api/poems/getall          # عرض الكل مع pagination
GET    /api/poems/search          # بحث متقدم
GET    /api/poems/favorites       # المفضلة
GET    /api/poems/{id}            # التفاصيل
POST   /api/AddPoem               # إضافة (Admin)
POST   /api/poems/{id}/update     # تحديث
DELETE /api/deletePoem/{id}       # حذف
POST   /api/FavoritePoem/{id}     # Toggle مفضلة
```

### الدروس (Lessons)
```
نفس APIs القصائد، استبدل poems → lessons
```

### الأقوال (Sayings)
```
GET    /api/sayings/getall?type=saying|supplication
GET    /api/sayings/search
GET    /api/sayings/favorites
GET    /api/sayings/{id}
POST   /api/AddSaying
POST   /api/sayings/{id}/update
DELETE /api/deleteSaying/{id}
POST   /api/FavoriteSaying/{id}
```

### المشاركات (Posts)
```
GET    /api/posts                 # المعتمدة
GET    /api/posts/search
GET    /api/posts/my-posts        # مشاركاتي
GET    /api/posts/pending         # المعلقة (Admin)
GET    /api/posts/{id}
POST   /api/posts                 # إنشاء
POST   /api/posts/{id}/update
POST   /api/posts/{id}/approve    # موافقة (Admin)
DELETE /api/posts/{id}
```

### التعليقات (Comments)
```
GET    /api/{type}/{id}/comments
POST   /api/{type}/{id}/comments
PUT    /api/{type}/comments/{id}
DELETE /api/{type}/comments/{id}

# type = poems | lessons | sayings
```

---

## 🔐 نظام الصلاحيات

### جدول الصلاحيات

| العملية | زائر | User | Admin |
|---------|------|------|-------|
| عرض محتوى عام | ✅ | ✅ | ✅ |
| عرض محتوى خاص | ❌ | ✅ | ✅ |
| إضافة محتوى | ❌ | ❌ | ✅ |
| تعديل محتوى | ❌ | مالك فقط | ✅ |
| حذف محتوى | ❌ | مالك فقط | ✅ |
| إضافة تعليق | ❌ | ✅ | ✅ |
| المفضلة | ❌ | ✅ | ✅ |
| إنشاء مشاركة | ❌ | ✅ | ✅ |
| موافقة مشاركة | ❌ | ❌ | ✅ |

### منطق الخصوصية

```
is_private = false → الجميع يراها
is_private = true  → المسجلين فقط
```

---

## 🧪 كيفية الاختبار

### الطريقة 1: استخدام السكريبت التفاعلي
```bash
# امنح صلاحيات التنفيذ
chmod +x API_EXAMPLES.sh

# تشغيل السكريبت
./API_EXAMPLES.sh

# اختر من القائمة:
# 1-10: اختبارات محددة
# 11: تشغيل جميع الاختبارات
```

### الطريقة 2: استخدام cURL مباشرة
```bash
# 1. تسجيل الدخول
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@test.com","password":"password"}' \
  | jq -r '.data.token')

# 2. عرض القصائد
curl http://localhost:8000/api/poems/getall \
  -H "Authorization: Bearer $TOKEN"

# 3. إضافة للمفضلة
curl -X POST http://localhost:8000/api/FavoritePoem/1 \
  -H "Authorization: Bearer $TOKEN"
```

### الطريقة 3: استخدام Postman
1. افتح Postman
2. استورد Collection من TESTING_GUIDE.md
3. أنشئ Environment مع المتغيرات
4. شغل الاختبارات

---

## 📋 Checklist الاختبار الكامل

### المصادقة ✅
- [ ] تسجيل ناجح
- [ ] تسجيل بإيميل مكرر (فشل)
- [ ] دخول ناجح
- [ ] دخول بكلمة سر خاطئة (فشل)

### القصائد ✅
- [ ] عرض بدون Token (عامة فقط)
- [ ] عرض مع Token (الكل)
- [ ] Pagination (3 صفحات على الأقل)
- [ ] بحث بالكلمة
- [ ] بحث بالسنة
- [ ] بحث بنوع المصدر
- [ ] بحث مركب
- [ ] تفاصيل عامة
- [ ] تفاصيل خاصة (مع/بدون Token)
- [ ] إضافة (Admin/User)
- [ ] تحديث (المالك/Admin)
- [ ] حذف (المالك/Admin)

### المفضلة ✅
- [ ] إضافة (Toggle ON)
- [ ] إزالة (Toggle OFF)
- [ ] عرض المفضلة
- [ ] is_favorited صحيح في getall

### التعليقات ✅
- [ ] عرض تعليقات
- [ ] إضافة (مع/بدون Token)
- [ ] تحديث (المالك/غيره)
- [ ] حذف (المالك/Admin)

### المشاركات ✅
- [ ] إنشاء (pending)
- [ ] عرض العامة
- [ ] عرض مشاركاتي
- [ ] عرض المعلقة (Admin)
- [ ] الموافقة (Admin)

### الدروس والأقوال ✅
- [ ] نفس اختبارات القصائد

---

## 🎓 أمثلة الاستخدام

### مثال 1: رحلة مستخدم كاملة
```bash
# 1. التسجيل
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "أحمد محمد",
    "email": "ahmed@test.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# 2. احفظ الـ Token
TOKEN="..."

# 3. تصفح القصائد
curl http://localhost:8000/api/poems/getall \
  -H "Authorization: Bearer $TOKEN"

# 4. البحث عن قصيدة
curl "http://localhost:8000/api/poems/search?keyword=محمد" \
  -H "Authorization: Bearer $TOKEN"

# 5. عرض تفاصيل قصيدة
curl http://localhost:8000/api/poems/1 \
  -H "Authorization: Bearer $TOKEN"

# 6. إضافة للمفضلة
curl -X POST http://localhost:8000/api/FavoritePoem/1 \
  -H "Authorization: Bearer $TOKEN"

# 7. إضافة تعليق
curl -X POST http://localhost:8000/api/poems/1/comments \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content": "قصيدة رائعة"}'

# 8. عرض المفضلة
curl http://localhost:8000/api/poems/favorites \
  -H "Authorization: Bearer $TOKEN"
```

### مثال 2: رحلة مدير (Admin)
```bash
# 1. تسجيل الدخول كـ Admin
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}'

# 2. احفظ Admin Token
ADMIN_TOKEN="..."

# 3. إضافة قصيدة جديدة
curl -X POST http://localhost:8000/api/AddPoem \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -F "title=قصيدة جديدة" \
  -F "description=وصف القصيدة" \
  -F "saying_date=2024-03-15" \
  -F "is_private=false" \
  -F "pdf_source[]=@/path/to/file.pdf" \
  -F "audio_source[]=@/path/to/audio.mp3"

# 4. عرض المشاركات المعلقة
curl http://localhost:8000/api/posts/pending \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# 5. الموافقة على مشاركة
curl -X POST http://localhost:8000/api/posts/5/approve \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# 6. حذف قصيدة
curl -X DELETE http://localhost:8000/api/deletePoem/10 \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

---

## 🔍 نصائح مهمة

### 1. ترتيب المسارات
```php
// ✅ صحيح
Route::get('/poems/favorites', ...);
Route::get('/poems/{id}', ...);

// ❌ خطأ
Route::get('/poems/{id}', ...);
Route::get('/poems/favorites', ...);  // سيعتبر favorites كـ id
```

### 2. المصادقة الاختيارية
```php
// هذه APIs تعمل مع وبدون Token
GET /api/poems/getall
GET /api/poems/search
GET /api/poems/{id}
GET /api/lessons/getall
GET /api/lessons/search
GET /api/lessons/{id}
GET /api/sayings/getall
GET /api/sayings/search
GET /api/sayings/{id}
```

### 3. Pagination موحد
```json
{
  "meta": {
    "page_number": 1,
    "total_pages": 5,
    "has_previous": false,
    "has_next": true,
    "total_items": 75
  }
}
```

### 4. Toggle System
```
أول ضغطة → إضافة (is_favorited: true)
ثاني ضغطة → إزالة (is_favorited: false)
```

---

## 🐛 استكشاف الأخطاء

### خطأ 401 Unauthorized
**السبب**: لم ترسل Token أو Token غير صحيح
**الحل**: تأكد من إرسال `Authorization: Bearer {token}`

### خطأ 403 Forbidden
**السبب**: ليس لديك صلاحية
**الحل**: استخدم حساب Admin أو تأكد أنك المالك

### خطأ 404 Not Found
**السبب**: المحتوى غير موجود أو المسار خاطئ
**الحل**: تحقق من ID والمسار

### خطأ 422 Validation Error
**السبب**: بيانات غير صحيحة
**الحل**: تحقق من البيانات المطلوبة

### "القصيدة المطلوبة غير موجودة"
**السبب**: مشكلة في ترتيب المسارات
**الحل**: تأكد أن `/poems/favorites` قبل `/poems/{id}`

---

## 📞 الدعم والمساعدة

### الملفات المرجعية
1. **SYSTEM_DOCUMENTATION.md** - للفهم الشامل
2. **TESTING_GUIDE.md** - للاختبار المنهجي
3. **API_EXAMPLES.sh** - للاختبار السريع

### الموارد الإضافية
- Laravel Documentation: https://laravel.com/docs
- Sanctum Documentation: https://laravel.com/docs/sanctum
- Postman Documentation: https://learning.postman.com

---

## 📝 ملاحظات نهائية

### ما تم تنفيذه ✅
- ✅ نظام مصادقة كامل مع Sanctum
- ✅ CRUD شامل لجميع أنواع المحتوى
- ✅ بحث متقدم مع فلاتر متعددة
- ✅ Pagination موحد (15 عنصر/صفحة)
- ✅ نظام مفضلة مع Toggle
- ✅ نظام تعليقات كامل
- ✅ نظام مشاركات مع موافقة
- ✅ صلاحيات متعددة المستويات
- ✅ مصادقة اختيارية
- ✅ دعم مصادر متعددة
- ✅ نظام خصوصية

### التحسينات المستقبلية 🚀
- [ ] إضافة Notifications
- [ ] نظام Ratings
- [ ] Export إلى PDF
- [ ] API Documentation (Swagger)
- [ ] Rate Limiting
- [ ] Caching
- [ ] Search Indexing (Elasticsearch)

---

## 🎉 الخلاصة

لديك الآن نظام كامل ومتكامل لإدارة المحتوى الإسلامي مع:
- 📖 توثيق شامل ومفصل
- 🧪 دليل اختبار كامل
- 🔧 أمثلة جاهزة للتنفيذ
- ✅ جميع الميزات المطلوبة

**جاهز للاستخدام والتطوير! 🚀**

---

**تم التطوير بواسطة**: Bahjat Al-Nashy
**التاريخ**: 2024
**الإصدار**: 1.0.0
