# جميع API Routes - مرجع كامل

## 📋 جدول شامل لجميع الـ Endpoints

---

## 🔐 المستخدمين (Authentication)

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| POST | `/api/register` | ❌ | تسجيل مستخدم جديد |
| POST | `/api/login` | ❌ | تسجيل الدخول |
| GET | `/api/user` | ✅ | معلومات المستخدم الحالي |

---

## 📖 القصائد (Poems)

### CRUD Operations

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| POST | `/api/AddPoem` | ✅ | إنشاء قصيدة جديدة |
| GET | `/api/poems/getall` | ❌ | عرض جميع القصائد |
| GET | `/api/poems/{poem_id}` | ❌ | عرض تفاصيل قصيدة |
| POST | `/api/poems/{poem_id}/update` | ✅ | تحديث قصيدة |
| DELETE | `/api/deletePoem/{id}` | ✅ | حذف قصيدة |

### Sources Management

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| POST | `/api/AddSourcePoem/{poem_id}` | ✅ | إضافة مصادر (PDF/صوت/فيديو) |
| DELETE | `/api/deleteSource/{source_id}` | ✅ | حذف مصدر واحد |

### Search & Favorites

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| GET | `/api/poems/search` | ❌ | البحث في القصائد |
| POST | `/api/FavoritePoem/{poem_id}` | ✅ | إضافة/إزالة من المفضلة |
| GET | `/api/poems/favorites` | ✅ | عرض القصائد المفضلة |

### Comments

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| GET | `/api/poems/{poem_id}/comments` | ❌ | عرض تعليقات قصيدة |
| POST | `/api/poems/{poem_id}/comments` | ✅ | إضافة تعليق |
| PUT | `/api/poems/comments/{comment_id}` | ✅ | تحديث تعليق (صاحبه فقط) |
| DELETE | `/api/poems/comments/{comment_id}` | ✅ | حذف تعليق (صاحبه أو Admin) |

---

## 📚 الدروس (Lessons)

### CRUD Operations

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| POST | `/api/AddLesson` | ✅ | إنشاء درس جديد (Admin فقط) |
| GET | `/api/lessons/getall` | ❌ | عرض جميع الدروس |
| GET | `/api/lessons/{lesson_id}` | ❌ | عرض تفاصيل درس |
| POST | `/api/lessons/{lesson_id}/update` | ✅ | تحديث درس |
| DELETE | `/api/deleteLesson/{id}` | ✅ | حذف درس |

### Sources Management

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| POST | `/api/AddSourceLesson/{lesson_id}` | ✅ | إضافة مصادر (PDF/صوت/فيديو) |
| DELETE | `/api/deleteLessonSource/{source_id}` | ✅ | حذف مصدر واحد |

### Search & Favorites

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| GET | `/api/lessons/search` | ❌ | البحث في الدروس |
| POST | `/api/FavoriteLesson/{lesson_id}` | ✅ | إضافة/إزالة من المفضلة |
| GET | `/api/lessons/favorites` | ✅ | عرض الدروس المفضلة |

### Comments

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| GET | `/api/lessons/{lesson_id}/comments` | ❌ | عرض تعليقات درس |
| POST | `/api/lessons/{lesson_id}/comments` | ✅ | إضافة تعليق |
| PUT | `/api/lessons/comments/{comment_id}` | ✅ | تحديث تعليق (صاحبه فقط) |
| DELETE | `/api/lessons/comments/{comment_id}` | ✅ | حذف تعليق (صاحبه أو Admin) |

---

## 📖 الأقوال (Sayings)

### CRUD Operations

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| POST | `/api/AddSaying` | ✅ | إنشاء قول جديد (Admin فقط) |
| GET | `/api/sayings/getall` | ❌ | عرض جميع الأقوال |
| GET | `/api/sayings/{saying_id}` | ❌ | عرض تفاصيل قول |
| POST | `/api/sayings/{saying_id}/update` | ✅ | تحديث قول (المالك فقط) |
| DELETE | `/api/deleteSaying/{id}` | ✅ | حذف قول (المالك أو Admin) |

### Search & Favorites

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| GET | `/api/sayings/search` | ❌ | البحث في الأقوال |
| POST | `/api/FavoriteSaying/{saying_id}` | ✅ | إضافة/إزالة من المفضلة |
| GET | `/api/sayings/favorites` | ✅ | عرض المفضلة |

### Comments

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| GET | `/api/sayings/{saying_id}/comments` | ❌ | عرض تعليقات قول |
| POST | `/api/sayings/{saying_id}/comments` | ✅ | إضافة تعليق |
| PUT | `/api/sayings/comments/{comment_id}` | ✅ | تحديث تعليق (صاحبه فقط) |
| DELETE | `/api/sayings/comments/{comment_id}` | ✅ | حذف تعليق (صاحبه أو Admin) |

---

## 📝 مشاركات الزوار (Posts)

### CRUD Operations

| Method | Endpoint | Auth | الوصف |
|--------|----------|------|-------|
| GET | `/api/posts` | ❌ | عرض المشاركات الموافق عليها |
| GET | `/api/posts/search` | ❌ | البحث في المشاركات الموافق عليها |
| GET | `/api/posts/my-posts` | ✅ | مشاركاتي (موافق + غير موافق) |
| GET | `/api/posts/pending` | ✅ | المشاركات في انتظار الموافقة (Admin) |
| GET | `/api/posts/{id}` | ❌ | عرض مشاركة واحدة |
| POST | `/api/posts` | ✅ | إنشاء مشاركة (مستخدم مسجل) |
| POST | `/api/posts/{id}/update` | ✅ | تحديث مشاركة (المالك فقط) |
| POST | `/api/posts/{id}/approve` | ✅ | الموافقة/رفض مشاركة (Admin فقط) |
| DELETE | `/api/posts/{id}` | ✅ | حذف مشاركة (المالك أو Admin) |

**ملاحظات:**
- **نظام الموافقة:** المشاركات لا تظهر إلا بعد موافقة Admin
- **بدون تعليقات** و**بدون مفضلة** (نظام بسيط)

---

## 📊 إحصائيات

- **إجمالي Endpoints:** 55
- **Public (بدون Auth):** 20
- **Protected (تتطلب Auth):** 35

---

## 🔑 رموز التوضيح

| الرمز | المعنى |
|------|--------|
| ✅ | يتطلب تسجيل دخول (Bearer Token) |
| ❌ | لا يتطلب تسجيل دخول (Public) |

---

## 📖 ملاحظات مهمة

### قواعد الخصوصية:
- **زائر (غير مسجل):** يرى المحتوى العام فقط (`is_private = false`)
- **مسجل:** يرى كل شيء (عام + خاص)

### صلاحيات التعليقات:
- **إضافة:** أي مستخدم مسجل
- **تحديث:** صاحب التعليق فقط
- **حذف:** صاحب التعليق أو Admin

### رفع الملفات المتعددة:
- استخدم `pdf_source[]`, `audio_source[]`, `video_source[]`
- راجع `HOW_TO_UPLOAD_MULTIPLE_FILES.md`

---

## 🔗 الترتيب المنطقي للعمليات

### للقصائد والدروس:

```
1. Create (POST /api/AddPoem أو AddLesson)
2. Read All (GET /api/poems/getall أو lessons/getall)
3. Read One (GET /api/poems/{id} أو lessons/{id})
4. Update (POST /api/poems/{id}/update)
5. Delete (DELETE /api/deletePoem/{id})

6. إضافة مصادر (POST /api/AddSourcePoem/{id})
7. حذف مصدر (DELETE /api/deleteSource/{id})

8. البحث (GET /api/poems/search)
9. المفضلة (POST/GET /api/poems/favorites)

10. التعليقات (GET/POST/PUT/DELETE /api/poems/{id}/comments)
```

---

## 🌐 Base URL

```
Development: http://localhost:8000/api
Production: https://your-domain.com/api
```

---

## 📝 أمثلة سريعة

### تسجيل دخول:
```bash
POST /api/login
Body: {"email": "user@example.com", "password": "password"}
```

### إنشاء قصيدة:
```bash
POST /api/AddPoem
Headers: Authorization: Bearer TOKEN
Body: form-data (title, description, files...)
```

### البحث:
```bash
GET /api/poems/search?keyword=حب&year=2024
```

### إضافة للمفضلة:
```bash
POST /api/FavoritePoem/5
Headers: Authorization: Bearer TOKEN
```

### إضافة تعليق:
```bash
POST /api/poems/5/comments
Headers: Authorization: Bearer TOKEN
Body: {"content": "تعليق رائع"}
```

---

## 📚 التوثيق الكامل

راجع الملفات التالية للتفاصيل:
- `POEM_API_DOCS.md` - توثيق القصائد
- `LESSON_API_DOCS.md` - توثيق الدروس
- `COMMENTS_API_DOCS.md` - توثيق التعليقات
- `PRIVACY_RULES.md` - قواعد الخصوصية
- `HOW_TO_UPLOAD_MULTIPLE_FILES.md` - رفع ملفات متعددة
- `API_ENDPOINTS_SUMMARY.md` - ملخص شامل
