# 📚 دليل النظام الشامل - نظام إدارة المحتوى الإسلامي

## 📋 جدول المحتويات
1. [نظرة عامة](#overview)
2. [هيكل قاعدة البيانات](#database)
3. [نظام المصادقة](#auth)
4. [القصائد](#poems)
5. [الدروس](#lessons)
6. [الأقوال](#sayings)
7. [المشاركات](#posts)
8. [التعليقات](#comments)
9. [نظام المفضلة](#favorites)
10. [أمثلة تجريبية](#examples)

---

## 🎯 نظرة عامة {#overview}

### الهدف
نظام إدارة محتوى إسلامي يتيح:
- إدارة القصائد والدروس والأقوال والأذكار
- إضافة مصادر متعددة (PDF, Audio, Video)
- نظام المفضلة والتعليقات
- البحث المتقدم مع Pagination

### أنواع المستخدمين
1. **زائر**: عرض المحتوى العام فقط
2. **مستخدم مسجل**: عرض كل المحتوى + تعليقات ومفضلة
3. **مدير**: صلاحيات كاملة

### التقنيات
- Laravel 10+ with Sanctum
- MySQL/MariaDB
- RESTful API with JSON

---

## 🗄️ هيكل قاعدة البيانات {#database}

### الجداول الرئيسية

**users**: id, name, email, password, role (user/admin)

**poems**: id, title, description, saying_date, is_private, user_id

**lessons**: id, title, description, saying_date, is_private, user_id

**sayings**: id, content, type (saying/supplication), source, user_id

**sources**: id, source_type (pdf/audio/video), source, url, poem_id, lesson_id

**favorites**: id, user_id, poem_id, lesson_id, saying_id

**comments**: id, content, user_id, poem_id, lesson_id, saying_id

**posts**: id, title, content, status (pending/approved/rejected), user_id

---

## 🔐 نظام المصادقة {#auth}

### 1. التسجيل
```
POST /api/register
Body: {name, email, password, password_confirmation}
Response: {user, token}
```

### 2. تسجيل الدخول
```
POST /api/login
Body: {email, password}
Response: {user, token}
```

### 3. بيانات المستخدم
```
GET /api/user
Headers: Authorization: Bearer {token}
```

### نظام الصلاحيات

**المصادقة الاختيارية**: بعض APIs تعمل بدون Token لكن مع Token تحصل على is_favorited الصحيح

**منطق الخصوصية**:
- is_private = false: الجميع يراها
- is_private = true: المسجلين فقط

---

## 📖 القصائد {#poems}

### APIs الأساسية

**1. عرض جميع القصائد**
```
GET /api/poems/getall?page=1
Headers: Authorization: Bearer {token} (optional)
Response: {success, message, meta: {page_number, total_pages, has_previous, has_next, total_items}, data: [{id, title, date, has_pdf, has_audio, has_video, is_favorited}]}
```

**2. البحث**
```
GET /api/poems/search?keyword=محمد&year=2024&month=1&source_type=audio&page=1
Parameters:
  - keyword: البحث في العنوان والوصف
  - year: السنة
  - month: الشهر (1-12)
  - date: تاريخ محدد (YYYY-MM-DD)
  - date_comparison: (=, >, <, >=, <=)
  - source_type: (pdf, audio, video)
  - page: رقم الصفحة
```

**3. تفاصيل قصيدة**
```
GET /api/poems/{id}
Response: {id, title, description, date, videos: [{id, url}], audios: [{id, url}], pdfs: [{id, url}], is_favorited, comments: [], comments_count, author_name}
```

**4. إضافة قصيدة (Admin)**
```
POST /api/AddPoem
Headers: Authorization: Bearer {admin_token}, Content-Type: multipart/form-data
Body: title, description, saying_date, is_private, pdf_source[], audio_source[], video_source[]
```

**5. تحديث قصيدة**
```
POST /api/poems/{id}/update
Headers: Authorization: Bearer {token}
```

**6. إضافة مصادر**
```
POST /api/AddSourcePoem/{poem_id}
Body: pdf_source[], audio_source[], video_source[]
```

**7. حذف مصدر**
```
DELETE /api/deleteSource/{source_id}
```

**8. حذف قصيدة**
```
DELETE /api/deletePoem/{id}
```

**9. المفضلة (Toggle)**
```
POST /api/FavoritePoem/{id}
Response: {success, message, is_favorited}
```

**10. عرض المفضلة**
```
GET /api/poems/favorites?page=1
```

---

## 📚 الدروس {#lessons}

نفس آلية القصائد تماماً، استبدل:
- poems → lessons
- poem_id → lesson_id
- AddPoem → AddLesson
- FavoritePoem → FavoriteLesson

### جميع Endpoints
```
GET    /api/lessons/getall
GET    /api/lessons/search
GET    /api/lessons/favorites
GET    /api/lessons/{id}
POST   /api/AddLesson
POST   /api/lessons/{id}/update
POST   /api/AddSourceLesson/{id}
DELETE /api/deleteLesson/{id}
DELETE /api/deleteLessonSource/{id}
POST   /api/FavoriteLesson/{id}
```

---

## 💬 الأقوال والأذكار {#sayings}

### الفرق
- لا مصادر (PDF/Audio/Video)
- نوعين: saying (أقوال) / supplication (أذكار)
- حقل source نصي

### APIs

**1. عرض الأقوال**
```
GET /api/sayings/getall?type=saying&page=1
Parameters: type (saying/supplication), page
Response: {id, content, type, source, is_favorited}
```

**2. البحث**
```
GET /api/sayings/search?keyword=الصلاة&type=supplication
```

**3. تفاصيل قول**
```
GET /api/sayings/{id}
```

**4. إضافة قول (Admin)**
```
POST /api/AddSaying
Body: {content, type, source}
```

**5. تحديث**
```
POST /api/sayings/{id}/update
```

**6. حذف**
```
DELETE /api/deleteSaying/{id}
```

**7. المفضلة**
```
POST /api/FavoriteSaying/{id}
GET  /api/sayings/favorites
```

---

## 📝 المشاركات {#posts}

### نظام الموافقة
- User ينشئ → pending
- Admin يوافق → approved
- Admin يرفض → rejected

### APIs

**1. المشاركات المعتمدة**
```
GET /api/posts?page=1
```

**2. البحث**
```
GET /api/posts/search?keyword=موضوع
```

**3. تفاصيل**
```
GET /api/posts/{id}
```

**4. إنشاء (User)**
```
POST /api/posts
Body: {title, content}
Response: status = pending
```

**5. مشاركاتي**
```
GET /api/posts/my-posts
```

**6. المعلقة (Admin)**
```
GET /api/posts/pending
```

**7. الموافقة (Admin)**
```
POST /api/posts/{id}/approve
```

**8. تحديث**
```
POST /api/posts/{id}/update
```

**9. حذف**
```
DELETE /api/posts/{id}
```

---

## 💬 التعليقات {#comments}

### على القصائد
```
GET    /api/poems/{id}/comments
POST   /api/poems/{id}/comments
PUT    /api/poems/comments/{id}
DELETE /api/poems/comments/{id}
```

### على الدروس
```
GET    /api/lessons/{id}/comments
POST   /api/lessons/{id}/comments
PUT    /api/lessons/comments/{id}
DELETE /api/lessons/comments/{id}
```

### على الأقوال
```
GET    /api/sayings/{id}/comments
POST   /api/sayings/{id}/comments
PUT    /api/sayings/comments/{id}
DELETE /api/sayings/comments/{id}
```

### مثال
```
POST /api/poems/1/comments
Headers: Authorization: Bearer {token}
Body: {content: "تعليق رائع"}
```

---

## ⭐ نظام المفضلة {#favorites}

### آلية العمل
1. أول ضغطة → إضافة للمفضلة (is_favorited: true)
2. ضغطة ثانية → إزالة من المفضلة (is_favorited: false)
3. Toggle System

### APIs
```
POST /api/FavoritePoem/{id}
GET  /api/poems/favorites

POST /api/FavoriteLesson/{id}
GET  /api/lessons/favorites

POST /api/FavoriteSaying/{id}
GET  /api/sayings/favorites
```

---

## 🧪 أمثلة تجريبية شاملة {#examples}

### السيناريو 1: زائر (Guest)

**1. عرض القصائد**
```bash
curl http://localhost:8000/api/poems/getall
# النتيجة: قصائد عامة فقط، is_favorited = false
```

**2. البحث**
```bash
curl "http://localhost:8000/api/poems/search?keyword=محمد"
# النتيجة: قصائد عامة تحتوي على "محمد"
```

**3. تفاصيل قصيدة**
```bash
curl http://localhost:8000/api/poems/1
# النتيجة: تفاصيل كاملة، is_favorited = false
```

**4. محاولة تعليق (فشل)**
```bash
curl -X POST http://localhost:8000/api/poems/1/comments \
  -H "Content-Type: application/json" \
  -d '{"content": "تعليق"}'
# النتيجة: 401 Unauthorized
```

---

### السيناريو 2: مستخدم مسجل

**1. التسجيل**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "أحمد محمد",
    "email": "ahmed@test.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
# احفظ الـ token
```

**2. عرض القصائد مع Token**
```bash
curl http://localhost:8000/api/poems/getall \
  -H "Authorization: Bearer {token}"
# النتيجة: جميع القصائد (عامة + خاصة)، is_favorited صحيح
```

**3. إضافة للمفضلة**
```bash
curl -X POST http://localhost:8000/api/FavoritePoem/1 \
  -H "Authorization: Bearer {token}"
# النتيجة: is_favorited = true
```

**4. عرض المفضلة**
```bash
curl http://localhost:8000/api/poems/favorites \
  -H "Authorization: Bearer {token}"
# النتيجة: القصائد المفضلة فقط
```

**5. إضافة تعليق**
```bash
curl -X POST http://localhost:8000/api/poems/1/comments \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"content": "قصيدة رائعة جداً"}'
# النتيجة: تعليق مضاف بنجاح
```

**6. إنشاء مشاركة**
```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "مشاركتي الأولى",
    "content": "محتوى المشاركة..."
  }'
# النتيجة: status = pending
```

---

### السيناريو 3: مدير (Admin)

**1. تسجيل الدخول كـ Admin**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@test.com",
    "password": "admin123"
  }'
# احفظ admin_token
```

**2. إضافة قصيدة جديدة**
```bash
curl -X POST http://localhost:8000/api/AddPoem \
  -H "Authorization: Bearer {admin_token}" \
  -F "title=قصيدة البردة" \
  -F "description=قصيدة في مدح النبي" \
  -F "saying_date=2024-01-15" \
  -F "is_private=false" \
  -F "pdf_source[]=@/path/to/file.pdf" \
  -F "audio_source[]=@/path/to/audio.mp3"
# النتيجة: قصيدة جديدة مع المصادر
```

**3. عرض المشاركات المعلقة**
```bash
curl http://localhost:8000/api/posts/pending \
  -H "Authorization: Bearer {admin_token}"
# النتيجة: جميع المشاركات pending
```

**4. الموافقة على مشاركة**
```bash
curl -X POST http://localhost:8000/api/posts/5/approve \
  -H "Authorization: Bearer {admin_token}"
# النتيجة: status = approved
```

**5. حذف قصيدة**
```bash
curl -X DELETE http://localhost:8000/api/deletePoem/10 \
  -H "Authorization: Bearer {admin_token}"
# النتيجة: قصيدة محذوفة مع جميع مصادرها
```

---

### السيناريو 4: اختبار البحث المتقدم

**1. بحث بسيط**
```bash
curl "http://localhost:8000/api/poems/search?keyword=محمد"
```

**2. بحث حسب السنة**
```bash
curl "http://localhost:8000/api/poems/search?year=2024"
```

**3. بحث حسب نوع المصدر**
```bash
curl "http://localhost:8000/api/poems/search?source_type=audio"
```

**4. بحث مركب**
```bash
curl "http://localhost:8000/api/poems/search?keyword=البردة&year=2024&source_type=pdf&page=2"
```

**5. بحث بالتاريخ**
```bash
curl "http://localhost:8000/api/poems/search?date=2024-01-15&date_comparison=>="
# القصائد من 2024-01-15 وما بعد
```

---

### السيناريو 5: اختبار Pagination

**1. الصفحة الأولى**
```bash
curl "http://localhost:8000/api/poems/getall?page=1"
# meta: {page_number: 1, total_pages: 5, has_previous: false, has_next: true}
```

**2. الصفحة الثانية**
```bash
curl "http://localhost:8000/api/poems/getall?page=2"
# meta: {page_number: 2, total_pages: 5, has_previous: true, has_next: true}
```

**3. آخر صفحة**
```bash
curl "http://localhost:8000/api/poems/getall?page=5"
# meta: {page_number: 5, total_pages: 5, has_previous: true, has_next: false}
```

---

### السيناريو 6: اختبار الخصوصية

**1. قصيدة خاصة بدون Token**
```bash
curl http://localhost:8000/api/poems/getall
# النتيجة: لا تظهر القصائد الخاصة
```

**2. قصيدة خاصة مع Token**
```bash
curl http://localhost:8000/api/poems/getall \
  -H "Authorization: Bearer {token}"
# النتيجة: تظهر جميع القصائد (عامة + خاصة)
```

**3. محاولة عرض تفاصيل قصيدة خاصة بدون Token**
```bash
curl http://localhost:8000/api/poems/5
# إذا كانت خاصة: 403 Forbidden
```

---

### السيناريو 7: اختبار الصلاحيات

**1. User يحاول إضافة قصيدة (فشل)**
```bash
curl -X POST http://localhost:8000/api/AddPoem \
  -H "Authorization: Bearer {user_token}" \
  -F "title=قصيدة"
# النتيجة: 403 Unauthorized action!
```

**2. User يحاول حذف قصيدة غيره (فشل)**
```bash
curl -X DELETE http://localhost:8000/api/deletePoem/1 \
  -H "Authorization: Bearer {user_token}"
# النتيجة: 403 غير مصرح لك
```

**3. User يحذف قصيدته (نجاح)**
```bash
curl -X DELETE http://localhost:8000/api/deletePoem/10 \
  -H "Authorization: Bearer {user_token}"
# إذا كان مالك القصيدة: نجاح
```

**4. Admin يحذف أي قصيدة (نجاح)**
```bash
curl -X DELETE http://localhost:8000/api/deletePoem/1 \
  -H "Authorization: Bearer {admin_token}"
# النتيجة: نجاح دائماً
```

---

## 📊 ملخص الحالات والشروط

### حالات المصادقة
| الحالة | الوصف | النتيجة |
|--------|-------|---------|
| بدون Token | زائر | محتوى عام فقط، is_favorited = false |
| Token عادي | مستخدم | كل المحتوى، is_favorited صحيح |
| Token Admin | مدير | كل المحتوى + صلاحيات إدارة |

### حالات الخصوصية
| is_private | بدون Token | مع Token |
|-----------|-----------|----------|
| false | ✅ يظهر | ✅ يظهر |
| true | ❌ لا يظهر | ✅ يظهر |

### حالات الصلاحيات
| العملية | User | Admin | المالك |
|---------|------|-------|--------|
| عرض | ✅ | ✅ | ✅ |
| إضافة | ❌ | ✅ | - |
| تعديل | ❌ | ✅ | ✅ |
| حذف | ❌ | ✅ | ✅ |
| تعليق | ✅ | ✅ | ✅ |
| مفضلة | ✅ | ✅ | ✅ |

### حالات المشاركات
| Status | الوصف | من يراها |
|--------|-------|----------|
| pending | في الانتظار | المالك + Admin |
| approved | معتمدة | الجميع |
| rejected | مرفوضة | المالك + Admin |

---

## 🔍 نصائح للاختبار

### 1. استخدم Postman
- أنشئ Collection لكل نوع محتوى
- احفظ الـ Tokens في Environment Variables
- استخدم Tests للتحقق من الاستجابات

### 2. اختبر جميع الحالات
- ✅ بدون Token
- ✅ مع Token عادي
- ✅ مع Token Admin
- ✅ محتوى عام
- ✅ محتوى خاص
- ✅ Pagination
- ✅ البحث المتقدم

### 3. تحقق من الأخطاء
- 401: غير مصادق
- 403: غير مصرح
- 404: غير موجود
- 422: خطأ في البيانات

### 4. اختبر الـ Edge Cases
- صفحة غير موجودة (page=999)
- بحث بدون نتائج
- إضافة للمفضلة مرتين
- حذف محتوى محذوف

---

## 📝 ملاحظات مهمة

1. **الترتيب مهم**: المسارات الثابتة قبل الديناميكية
   ```
   ✅ /poems/favorites قبل /poems/{id}
   ❌ /poems/{id} قبل /poems/favorites
   ```

2. **Pagination موحد**: 15 عنصر لكل صفحة

3. **التنسيق موحد**: جميع APIs تُرجع نفس البنية
   ```json
   {
     "success": true,
     "message": "...",
     "meta": {...},
     "data": [...]
   }
   ```

4. **المصادر متعددة**: يمكن إضافة أكثر من ملف من نفس النوع

5. **Toggle System**: المفضلة تعمل بنظام التبديل

---

## 🎓 الخلاصة

هذا النظام يوفر:
- ✅ API كامل لإدارة المحتوى الإسلامي
- ✅ نظام مصادقة وصلاحيات محكم
- ✅ بحث متقدم مع Pagination
- ✅ نظام مفضلة وتعليقات
- ✅ دعم المصادر المتعددة
- ✅ نظام موافقة للمشاركات

جميع الـ APIs موثقة ومختبرة وجاهزة للاستخدام! 🚀
