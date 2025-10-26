# 🧪 دليل الاختبار الشامل - Testing Guide

## 📋 جدول المحتويات
1. [إعداد بيئة الاختبار](#setup)
2. [سيناريوهات الاختبار الكاملة](#scenarios)
3. [Postman Collection](#postman)
4. [اختبارات الأداء](#performance)
5. [حالات الأخطاء](#errors)

---

## ⚙️ إعداد بيئة الاختبار {#setup}

### المتطلبات
```bash
# 1. تشغيل السيرفر
php artisan serve

# 2. التأكد من قاعدة البيانات
php artisan migrate:fresh --seed

# 3. ربط التخزين
php artisan storage:link
```

### إنشاء بيانات تجريبية

**1. إنشاء Admin**
```sql
INSERT INTO users (name, email, password, role) 
VALUES ('Admin', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password: password
```

**2. إنشاء User عادي**
```sql
INSERT INTO users (name, email, password, role) 
VALUES ('أحمد محمد', 'user@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');
```

**3. إنشاء قصائد تجريبية**
```sql
-- قصيدة عامة
INSERT INTO poems (title, description, saying_date, is_private, user_id) 
VALUES ('قصيدة البردة', 'قصيدة في مدح النبي', '2024-01-15', 0, 1);

-- قصيدة خاصة
INSERT INTO poems (title, description, saying_date, is_private, user_id) 
VALUES ('قصيدة خاصة', 'للمسجلين فقط', '2024-02-20', 1, 1);
```

---

## 🎯 سيناريوهات الاختبار الكاملة {#scenarios}

### السيناريو 1: اختبار المصادقة الكامل

#### Test 1.1: التسجيل الناجح
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "محمد علي",
    "email": "mohamed@test.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**النتيجة المتوقعة**:
- ✅ Status: 201
- ✅ يحتوي على user و token
- ✅ role = "user"

#### Test 1.2: التسجيل بإيميل مكرر (فشل)
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "اسم آخر",
    "email": "mohamed@test.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**النتيجة المتوقعة**:
- ❌ Status: 422
- ❌ خطأ: The email has already been taken

#### Test 1.3: التسجيل بكلمة سر غير متطابقة (فشل)
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "اسم",
    "email": "test@test.com",
    "password": "password123",
    "password_confirmation": "password456"
  }'
```

**النتيجة المتوقعة**:
- ❌ Status: 422
- ❌ خطأ: The password confirmation does not match

#### Test 1.4: تسجيل الدخول الناجح
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "mohamed@test.com",
    "password": "password123"
  }'
```

**النتيجة المتوقعة**:
- ✅ Status: 200
- ✅ يحتوي على user و token

#### Test 1.5: تسجيل الدخول بكلمة سر خاطئة (فشل)
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "mohamed@test.com",
    "password": "wrongpassword"
  }'
```

**النتيجة المتوقعة**:
- ❌ Status: 401
- ❌ خطأ: Invalid credentials

---

### السيناريو 2: اختبار القصائد الشامل

#### Test 2.1: عرض القصائد بدون Token
```bash
curl http://localhost:8000/api/poems/getall
```

**النتيجة المتوقعة**:
- ✅ Status: 200
- ✅ يعرض القصائد العامة فقط (is_private = false)
- ✅ is_favorited = false لجميع القصائد
- ✅ meta يحتوي على معلومات الصفحات

#### Test 2.2: عرض القصائد مع Token
```bash
curl http://localhost:8000/api/poems/getall \
  -H "Authorization: Bearer {user_token}"
```

**النتيجة المتوقعة**:
- ✅ Status: 200
- ✅ يعرض جميع القصائد (عامة + خاصة)
- ✅ is_favorited صحيح حسب المفضلة

#### Test 2.3: Pagination - الصفحة الأولى
```bash
curl "http://localhost:8000/api/poems/getall?page=1"
```

**النتيجة المتوقعة**:
- ✅ meta.page_number = 1
- ✅ meta.has_previous = false
- ✅ meta.has_next = true (إذا كان هناك أكثر من 15 قصيدة)
- ✅ data.length <= 15

#### Test 2.4: Pagination - الصفحة الثانية
```bash
curl "http://localhost:8000/api/poems/getall?page=2"
```

**النتيجة المتوقعة**:
- ✅ meta.page_number = 2
- ✅ meta.has_previous = true

#### Test 2.5: البحث بالكلمة المفتاحية
```bash
curl "http://localhost:8000/api/poems/search?keyword=محمد"
```

**النتيجة المتوقعة**:
- ✅ يعرض القصائد التي تحتوي على "محمد" في العنوان أو الوصف
- ✅ مع pagination

#### Test 2.6: البحث حسب السنة
```bash
curl "http://localhost:8000/api/poems/search?year=2024"
```

**النتيجة المتوقعة**:
- ✅ يعرض القصائد من سنة 2024 فقط

#### Test 2.7: البحث حسب نوع المصدر
```bash
curl "http://localhost:8000/api/poems/search?source_type=audio"
```

**النتيجة المتوقعة**:
- ✅ يعرض القصائد التي لها ملفات صوتية فقط

#### Test 2.8: البحث المركب
```bash
curl "http://localhost:8000/api/poems/search?keyword=البردة&year=2024&source_type=pdf"
```

**النتيجة المتوقعة**:
- ✅ يطبق جميع الفلاتر معاً

#### Test 2.9: عرض تفاصيل قصيدة عامة بدون Token
```bash
curl http://localhost:8000/api/poems/1
```

**النتيجة المتوقعة**:
- ✅ Status: 200
- ✅ تفاصيل كاملة مع المصادر والتعليقات
- ✅ is_favorited = false

#### Test 2.10: عرض تفاصيل قصيدة خاصة بدون Token (فشل)
```bash
curl http://localhost:8000/api/poems/2
```

**النتيجة المتوقعة**:
- ❌ Status: 403
- ❌ خطأ: هذه القصيدة خاصة، يجب تسجيل الدخول

#### Test 2.11: عرض تفاصيل قصيدة خاصة مع Token
```bash
curl http://localhost:8000/api/poems/2 \
  -H "Authorization: Bearer {user_token}"
```

**النتيجة المتوقعة**:
- ✅ Status: 200
- ✅ تفاصيل كاملة

#### Test 2.12: إضافة قصيدة كـ User (فشل)
```bash
curl -X POST http://localhost:8000/api/AddPoem \
  -H "Authorization: Bearer {user_token}" \
  -F "title=قصيدة جديدة"
```

**النتيجة المتوقعة**:
- ❌ Status: 403
- ❌ خطأ: Unauthorized action!

#### Test 2.13: إضافة قصيدة كـ Admin (نجاح)
```bash
curl -X POST http://localhost:8000/api/AddPoem \
  -H "Authorization: Bearer {admin_token}" \
  -F "title=قصيدة من الأدمن" \
  -F "description=وصف القصيدة" \
  -F "saying_date=2024-03-01" \
  -F "is_private=false" \
  -F "pdf_source[]=@/path/to/file.pdf"
```

**النتيجة المتوقعة**:
- ✅ Status: 201
- ✅ قصيدة جديدة مع المصادر

#### Test 2.14: إضافة قصيدة بعنوان مكرر (فشل)
```bash
curl -X POST http://localhost:8000/api/AddPoem \
  -H "Authorization: Bearer {admin_token}" \
  -F "title=قصيدة من الأدمن"
```

**النتيجة المتوقعة**:
- ❌ Status: 422
- ❌ خطأ: The title has already been taken

---

### السيناريو 3: اختبار المفضلة الكامل

#### Test 3.1: إضافة للمفضلة (أول مرة)
```bash
curl -X POST http://localhost:8000/api/FavoritePoem/1 \
  -H "Authorization: Bearer {user_token}"
```

**النتيجة المتوقعة**:
- ✅ Status: 200
- ✅ message: "تمت إضافة القصيدة إلى المفضلة"
- ✅ is_favorited: true

#### Test 3.2: إزالة من المفضلة (ثاني مرة)
```bash
curl -X POST http://localhost:8000/api/FavoritePoem/1 \
  -H "Authorization: Bearer {user_token}"
```

**النتيجة المتوقعة**:
- ✅ Status: 200
- ✅ message: "تمت إزالة القصيدة من المفضلة"
- ✅ is_favorited: false

#### Test 3.3: عرض المفضلة الفارغة
```bash
curl http://localhost:8000/api/poems/favorites \
  -H "Authorization: Bearer {user_token}"
```

**النتيجة المتوقعة**:
- ✅ Status: 200
- ✅ data: []
- ✅ meta.total_items: 0

#### Test 3.4: إضافة 3 قصائد للمفضلة
```bash
# إضافة قصيدة 1
curl -X POST http://localhost:8000/api/FavoritePoem/1 \
  -H "Authorization: Bearer {user_token}"

# إضافة قصيدة 2
curl -X POST http://localhost:8000/api/FavoritePoem/2 \
  -H "Authorization: Bearer {user_token}"

# إضافة قصيدة 3
curl -X POST http://localhost:8000/api/FavoritePoem/3 \
  -H "Authorization: Bearer {user_token}"
```

#### Test 3.5: عرض المفضلة (3 قصائد)
```bash
curl http://localhost:8000/api/poems/favorites \
  -H "Authorization: Bearer {user_token}"
```

**النتيجة المتوقعة**:
- ✅ data.length = 3
- ✅ is_favorited = true لجميع القصائد
- ✅ meta.total_items = 3

#### Test 3.6: التحقق من is_favorited في getall
```bash
curl http://localhost:8000/api/poems/getall \
  -H "Authorization: Bearer {user_token}"
```

**النتيجة المتوقعة**:
- ✅ القصائد 1, 2, 3 → is_favorited: true
- ✅ باقي القصائد → is_favorited: false

---

### السيناريو 4: اختبار التعليقات الكامل

#### Test 4.1: عرض تعليقات قصيدة (فارغة)
```bash
curl http://localhost:8000/api/poems/1/comments
```

**النتيجة المتوقعة**:
- ✅ Status: 200
- ✅ data: []

#### Test 4.2: إضافة تعليق بدون Token (فشل)
```bash
curl -X POST http://localhost:8000/api/poems/1/comments \
  -H "Content-Type: application/json" \
  -d '{"content": "تعليق رائع"}'
```

**النتيجة المتوقعة**:
- ❌ Status: 401
- ❌ Unauthenticated

#### Test 4.3: إضافة تعليق مع Token (نجاح)
```bash
curl -X POST http://localhost:8000/api/poems/1/comments \
  -H "Authorization: Bearer {user_token}" \
  -H "Content-Type: application/json" \
  -d '{"content": "قصيدة رائعة جداً، بارك الله فيكم"}'
```

**النتيجة المتوقعة**:
- ✅ Status: 201
- ✅ تعليق مضاف بنجاح

#### Test 4.4: عرض التعليقات (1 تعليق)
```bash
curl http://localhost:8000/api/poems/1/comments
```

**النتيجة المتوقعة**:
- ✅ data.length = 1
- ✅ يحتوي على content, user, created_at

#### Test 4.5: تحديث تعليق (المالك)
```bash
curl -X PUT http://localhost:8000/api/poems/comments/1 \
  -H "Authorization: Bearer {user_token}" \
  -H "Content-Type: application/json" \
  -d '{"content": "تعليق محدث"}'
```

**النتيجة المتوقعة**:
- ✅ Status: 200
- ✅ تعليق محدث

#### Test 4.6: تحديث تعليق شخص آخر (فشل)
```bash
curl -X PUT http://localhost:8000/api/poems/comments/1 \
  -H "Authorization: Bearer {another_user_token}" \
  -H "Content-Type: application/json" \
  -d '{"content": "محاولة تعديل"}'
```

**النتيجة المتوقعة**:
- ❌ Status: 403
- ❌ غير مصرح لك

#### Test 4.7: حذف تعليق (Admin)
```bash
curl -X DELETE http://localhost:8000/api/poems/comments/1 \
  -H "Authorization: Bearer {admin_token}"
```

**النتيجة المتوقعة**:
- ✅ Status: 200
- ✅ تعليق محذوف

---

### السيناريو 5: اختبار المشاركات الكامل

#### Test 5.1: إنشاء مشاركة (User)
```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer {user_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "مشاركتي الأولى",
    "content": "هذا محتوى المشاركة الطويل..."
  }'
```

**النتيجة المتوقعة**:
- ✅ Status: 201
- ✅ status: "pending"
- ✅ message: في انتظار موافقة الإدارة

#### Test 5.2: عرض المشاركات العامة (لا تظهر pending)
```bash
curl http://localhost:8000/api/posts
```

**النتيجة المتوقعة**:
- ✅ لا تظهر المشاركة pending
- ✅ فقط approved

#### Test 5.3: عرض مشاركاتي (تظهر pending)
```bash
curl http://localhost:8000/api/posts/my-posts \
  -H "Authorization: Bearer {user_token}"
```

**النتيجة المتوقعة**:
- ✅ تظهر المشاركة pending

#### Test 5.4: عرض المشاركات المعلقة (Admin)
```bash
curl http://localhost:8000/api/posts/pending \
  -H "Authorization: Bearer {admin_token}"
```

**النتيجة المتوقعة**:
- ✅ تظهر جميع المشاركات pending

#### Test 5.5: الموافقة على مشاركة (Admin)
```bash
curl -X POST http://localhost:8000/api/posts/1/approve \
  -H "Authorization: Bearer {admin_token}"
```

**النتيجة المتوقعة**:
- ✅ Status: 200
- ✅ status: "approved"

#### Test 5.6: عرض المشاركات العامة (تظهر الآن)
```bash
curl http://localhost:8000/api/posts
```

**النتيجة المتوقعة**:
- ✅ تظهر المشاركة المعتمدة

---

## 📊 Postman Collection {#postman}

### إنشاء Environment

**Variables**:
```json
{
  "base_url": "http://localhost:8000/api",
  "user_token": "",
  "admin_token": "",
  "poem_id": "",
  "lesson_id": "",
  "saying_id": "",
  "comment_id": "",
  "post_id": ""
}
```

### Collection Structure

```
📁 Islamic Content System
├── 📁 Authentication
│   ├── Register User
│   ├── Login User
│   ├── Login Admin
│   └── Get Current User
├── 📁 Poems
│   ├── 📁 CRUD
│   │   ├── Get All Poems
│   │   ├── Search Poems
│   │   ├── Get Poem Details
│   │   ├── Create Poem (Admin)
│   │   ├── Update Poem
│   │   └── Delete Poem
│   ├── 📁 Sources
│   │   ├── Add Sources
│   │   └── Delete Source
│   ├── 📁 Favorites
│   │   ├── Toggle Favorite
│   │   └── Get Favorites
│   └── 📁 Comments
│       ├── Get Comments
│       ├── Add Comment
│       ├── Update Comment
│       └── Delete Comment
├── 📁 Lessons (نفس Poems)
├── 📁 Sayings
└── 📁 Posts
```

### مثال Request في Postman

**Get All Poems**:
```
Method: GET
URL: {{base_url}}/poems/getall?page=1
Headers:
  Authorization: Bearer {{user_token}}
Tests:
  pm.test("Status is 200", function() {
    pm.response.to.have.status(200);
  });
  pm.test("Has meta", function() {
    pm.expect(pm.response.json()).to.have.property('meta');
  });
  pm.test("Has data array", function() {
    pm.expect(pm.response.json().data).to.be.an('array');
  });
```

---

## ⚡ اختبارات الأداء {#performance}

### Test 1: سرعة الاستجابة
```bash
# يجب أن تكون أقل من 200ms
time curl http://localhost:8000/api/poems/getall
```

### Test 2: Pagination Performance
```bash
# اختبار 100 صفحة
for i in {1..100}; do
  curl "http://localhost:8000/api/poems/getall?page=$i" > /dev/null
done
```

### Test 3: البحث المعقد
```bash
# يجب أن يكون سريع حتى مع فلاتر متعددة
time curl "http://localhost:8000/api/poems/search?keyword=test&year=2024&source_type=pdf"
```

---

## ❌ حالات الأخطاء {#errors}

### جدول الأخطاء المتوقعة

| Code | الحالة | المثال |
|------|--------|---------|
| 401 | غير مصادق | طلب بدون Token لـ API محمي |
| 403 | غير مصرح | User يحاول إضافة قصيدة |
| 404 | غير موجود | /api/poems/9999 |
| 422 | خطأ في البيانات | إيميل مكرر، عنوان مكرر |
| 500 | خطأ في السيرفر | خطأ برمجي |

### اختبار كل الأخطاء

```bash
# 401
curl http://localhost:8000/api/poems/favorites

# 403
curl -X POST http://localhost:8000/api/AddPoem \
  -H "Authorization: Bearer {user_token}"

# 404
curl http://localhost:8000/api/poems/99999

# 422
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name": "test"}'  # بدون email
```

---

## ✅ Checklist الاختبار الكامل

### المصادقة
- [ ] تسجيل ناجح
- [ ] تسجيل بإيميل مكرر (فشل)
- [ ] تسجيل بكلمة سر غير متطابقة (فشل)
- [ ] دخول ناجح
- [ ] دخول بكلمة سر خاطئة (فشل)

### القصائد
- [ ] عرض بدون Token (عامة فقط)
- [ ] عرض مع Token (الكل)
- [ ] Pagination (صفحة 1، 2، آخر صفحة)
- [ ] بحث بالكلمة
- [ ] بحث بالسنة
- [ ] بحث بنوع المصدر
- [ ] بحث مركب
- [ ] تفاصيل عامة بدون Token
- [ ] تفاصيل خاصة بدون Token (فشل)
- [ ] تفاصيل خاصة مع Token
- [ ] إضافة كـ User (فشل)
- [ ] إضافة كـ Admin
- [ ] تحديث (المالك/Admin)
- [ ] حذف (المالك/Admin)

### المفضلة
- [ ] إضافة (أول مرة)
- [ ] إزالة (ثاني مرة)
- [ ] عرض المفضلة
- [ ] التحقق من is_favorited

### التعليقات
- [ ] عرض تعليقات
- [ ] إضافة بدون Token (فشل)
- [ ] إضافة مع Token
- [ ] تحديث (المالك)
- [ ] تحديث (شخص آخر - فشل)
- [ ] حذف (المالك/Admin)

### المشاركات
- [ ] إنشاء (pending)
- [ ] عرض العامة (لا تظهر pending)
- [ ] عرض مشاركاتي
- [ ] عرض المعلقة (Admin)
- [ ] الموافقة (Admin)
- [ ] تحديث
- [ ] حذف

### الدروس والأقوال
- [ ] نفس اختبارات القصائد

---

## 🎓 نصائح نهائية

1. **اختبر بالترتيب**: ابدأ بالمصادقة ثم CRUD ثم المفضلة والتعليقات

2. **احفظ الـ Tokens**: استخدم Environment Variables في Postman

3. **تحقق من الـ Response**: تأكد من البنية والبيانات

4. **اختبر Edge Cases**: صفحات غير موجودة، بيانات فارغة، إلخ

5. **استخدم Tests**: اكتب Tests في Postman للتحقق التلقائي

6. **وثق الأخطاء**: سجل أي أخطاء تجدها مع الخطوات

---

**جاهز للاختبار! 🚀**
