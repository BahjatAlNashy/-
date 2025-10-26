# 📚 توثيق API الكامل - نظام القصائد والدروس

مرحباً بك في التوثيق الكامل لنظام إدارة القصائد والدروس.

---

## 🚀 البداية السريعة

### 1. جدول جميع الـ Routes
📋 **[ALL_ROUTES.md](./ALL_ROUTES.md)** - ابدأ من هنا!  
جدول شامل لجميع الـ 34 endpoint مع الشرح

### 2. ملف Routes المنظم
📄 **[routes/api.php](./routes/api.php)** - الملف الفعلي منظم ومرتب

---

## 📖 التوثيق المفصل

### القصائد (Poems)
📝 **[POEM_API_DOCS.md](./POEM_API_DOCS.md)**
- إنشاء وتحديث وحذف القصائد
- إدارة المصادر (PDF, Audio, Video)
- البحث والمفضلة
- التعليقات

### الدروس (Lessons)
📝 **[LESSON_API_DOCS.md](./LESSON_API_DOCS.md)**
- إنشاء وتحديث وحذف الدروس
- إدارة المصادر (PDF, Audio, Video)
- البحث والمفضلة
- التعليقات

### الأقوال (Sayings)
📖 **[SAYINGS_QUICK_START.md](./SAYINGS_QUICK_START.md)**
- نظام مبسط: أقوال مأثورة + أوراد/أذكار
- حقل type للتمييز
- content فقط (بدون تعقيد)
- CRUD كامل، بحث، pagination، مفضلة
- صلاحيات: إنشاء (Admin فقط)، تحديث (المالك فقط)، حذف (المالك أو Admin)

### مشاركات الزوار (Posts)
📝 **[POSTS_QUICK_START.md](./POSTS_QUICK_START.md)**
- نظام بسيط لمشاركات المستخدمين
- content نصي فقط
- CRUD كامل، بحث، pagination
- **نظام موافقة Admin:** المشاركات لا تظهر إلا بعد موافقة الإدارة
- صلاحيات: إنشاء (مستخدم مسجل)، موافقة (Admin)، تحديث (المالك)، حذف (المالك/Admin)
- **بدون تعليقات، بدون مفضلة** (نظام بسيط)

### التعليقات (Comments)
💬 **[COMMENTS_API_DOCS.md](./COMMENTS_API_DOCS.md)**
- CRUD كامل للتعليقات
- صلاحيات التحديث والحذف
- تعليقات القصائد، الدروس، والأقوال

---

## 🔐 الأمان والخصوصية

### قواعد الخصوصية
🔒 **[PRIVACY_RULES.md](./PRIVACY_RULES.md)**
- كيف يعمل نظام `is_private`
- من يمكنه رؤية المحتوى الخاص
- أمثلة وسيناريوهات

### الصلاحيات
- **Public (12 endpoint):** لا تحتاج تسجيل دخول
- **Protected (22 endpoint):** تحتاج Bearer Token

---

## 📤 رفع الملفات

### دليل الملفات المتعددة
⚠️ **[HOW_TO_UPLOAD_MULTIPLE_FILES.md](./HOW_TO_UPLOAD_MULTIPLE_FILES.md)** - مهم جداً!

**المشكلة الشائعة:** يتم استقبال ملف واحد فقط عند رفع عدة ملفات PDF

**الحل:**
```
في Postman:
Key: pdf_source[]  | Type: File | [ملف 1]
Key: pdf_source[]  | Type: File | [ملف 2]  ← صف جديد بنفس الاسم
Key: pdf_source[]  | Type: File | [ملف 3]  ← صف جديد بنفس الاسم
```

---

## 📊 ملخص النظام

### الميزات الرئيسية
✅ CRUD كامل للقصائد، الدروس، والأقوال  
✅ دعم ملفات متعددة (PDF, Audio, Video)  
✅ نظام البحث المتقدم  
✅ نظام المفضلة  
✅ نظام التعليقات الكامل  
✅ نظام الخصوصية (عام/خاص)  
✅ Authentication مع Sanctum  

### الإحصائيات
- **55** Endpoint
- **20** Public
- **35** Protected
- **4** أنواع محتوى (قصائد + دروس + أقوال + مشاركات)
- **3** أنواع ملفات (PDF + صوت + فيديو)

---

## 🔑 Authentication

### تسجيل مستخدم جديد
```bash
POST /api/register
Body: {
  "name": "اسم المستخدم",
  "email": "user@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

### تسجيل الدخول
```bash
POST /api/login
Body: {
  "email": "user@example.com",
  "password": "password"
}

Response: {
  "token": "YOUR_ACCESS_TOKEN",
  "user": {...}
}
```

### استخدام Token
```
Headers:
Authorization: Bearer YOUR_ACCESS_TOKEN
```

---

## 🎯 الاستخدام الشائع

### 1. عرض جميع القصائد
```bash
GET /api/poems/getall
```

### 2. البحث في الدروس
```bash
GET /api/lessons/search?keyword=برمجة&year=2024
```

### 3. إضافة قصيدة للمفضلة
```bash
POST /api/FavoritePoem/5
Headers: Authorization: Bearer TOKEN
```

### 4. إضافة تعليق
```bash
POST /api/lessons/3/comments
Headers: Authorization: Bearer TOKEN
Body: {"content": "تعليق مفيد"}
```

### 5. إنشاء درس جديد (مع ملفات متعددة)
```bash
POST /api/AddLesson
Headers: Authorization: Bearer TOKEN
Body: form-data
  - title: "درس جديد"
  - description: "شرح الدرس"
  - is_private: false
  - pdf_source[]: [ملف 1]
  - pdf_source[]: [ملف 2]
  - audio_source[]: [ملف صوتي]
```

---

## 🌐 Base URL

```
Development: http://localhost:8000/api
Production: https://your-domain.com/api
```

---

## 🛠️ التقنيات المستخدمة

- **Laravel** - Backend Framework
- **Sanctum** - API Authentication
- **MySQL** - Database
- **Storage** - File Management

---

## 📁 هيكل الملفات

```
routes/
  └── api.php                           # جميع الـ Routes

app/Http/Controllers/
  ├── UserController.php                # المستخدمين
  ├── PoemController.php                # القصائد
  ├── LessonController.php              # الدروس
  └── CommentController.php             # التعليقات

app/Models/
  ├── User.php
  ├── Poem.php
  ├── Lesson.php
  ├── Comment.php
  └── Source.php

Documentation/
  ├── API_README.md                     # هذا الملف
  ├── ALL_ROUTES.md                     # جدول شامل
  ├── POEM_API_DOCS.md                  # توثيق القصائد
  ├── LESSON_API_DOCS.md                # توثيق الدروس
  ├── COMMENTS_API_DOCS.md              # توثيق التعليقات
  ├── PRIVACY_RULES.md                  # قواعد الخصوصية
  ├── HOW_TO_UPLOAD_MULTIPLE_FILES.md   # دليل رفع الملفات
  └── API_ENDPOINTS_SUMMARY.md          # الملخص
```

---

## 🐛 استكشاف الأخطاء

### مشكلة 1: Unauthenticated
**السبب:** لم يتم إرسال Token  
**الحل:** أضف `Authorization: Bearer TOKEN` في Headers

### مشكلة 2: يستقبل ملف واحد فقط
**السبب:** طريقة خاطئة في Postman  
**الحل:** راجع `HOW_TO_UPLOAD_MULTIPLE_FILES.md`

### مشكلة 3: 403 Forbidden على محتوى خاص
**السبب:** محتوى خاص والمستخدم غير مسجل  
**الحل:** سجل الدخول أولاً

### مشكلة 4: Validation Error
**السبب:** بيانات مفقودة أو غير صحيحة  
**الحل:** راجع التوثيق للحقول المطلوبة

---

## 📞 الدعم

للأسئلة أو المشاكل:
1. راجع التوثيق المناسب أولاً
2. تحقق من الأمثلة في الملفات
3. راجع قسم استكشاف الأخطاء

---

## ✅ Checklist للبداية

- [ ] قرأت `ALL_ROUTES.md`
- [ ] جربت تسجيل الدخول (`/api/login`)
- [ ] فهمت كيفية استخدام Bearer Token
- [ ] قرأت `HOW_TO_UPLOAD_MULTIPLE_FILES.md`
- [ ] فهمت قواعد الخصوصية في `PRIVACY_RULES.md`
- [ ] جربت إنشاء قصيدة/درس
- [ ] جربت البحث والمفضلة
- [ ] جربت التعليقات

---

## 🎉 ملاحظات نهائية

- جميع الـ endpoints تدعم JSON responses
- الملفات المتعددة مدعومة في كل العمليات
- نظام الصلاحيات محكم ومختبر
- التوثيق محدث ومطابق للكود

**استمتع بالتطوير! 🚀**
