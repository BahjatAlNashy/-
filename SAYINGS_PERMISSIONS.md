# 🔑 صلاحيات نظام الأقوال

## 📋 الصلاحيات الكاملة

### 1️⃣ إنشاء قول (Create)
**Endpoint:** `POST /api/AddSaying`

| المستخدم | الصلاحية | الرد |
|----------|-----------|------|
| **Admin** | ✅ يمكنه الإنشاء | 201 Created |
| **User عادي** | ❌ ممنوع | 403 Forbidden |
| **زائر (بدون token)** | ❌ ممنوع | 401 Unauthorized |

**الكود:**
```php
$user = Auth::user();

// التحقق من الصلاحيات: Admin فقط
if ($user->role !== 'admin') {
    return response()->json([
        'success' => false,
        'message' => 'غير مصرح لك بإضافة أقوال. هذه الصلاحية للـ Admin فقط.'
    ], 403);
}
```

**مثال:**
```bash
# Admin ✅
POST /api/AddSaying
Headers: Authorization: Bearer ADMIN_TOKEN
Body: { type: "saying", content: "قول جديد" }
→ 201 Created

# User عادي ❌
POST /api/AddSaying
Headers: Authorization: Bearer USER_TOKEN
Body: { type: "saying", content: "قول جديد" }
→ 403 Forbidden
{
  "success": false,
  "message": "غير مصرح لك بإضافة أقوال. هذه الصلاحية للـ Admin فقط."
}
```

---

### 2️⃣ تحديث قول (Update)
**Endpoint:** `POST /api/sayings/{id}/update`

| المستخدم | الصلاحية | الرد |
|----------|-----------|------|
| **المالك** | ✅ يمكنه التحديث | 200 OK |
| **Admin** | ❌ ممنوع | 403 Forbidden |
| **User آخر** | ❌ ممنوع | 403 Forbidden |
| **زائر (بدون token)** | ❌ ممنوع | 401 Unauthorized |

**الكود:**
```php
$user = Auth::user();

// التحقق من الصلاحيات: المالك فقط
if ($saying->user_id !== $user->id) {
    return response()->json([
        'success' => false,
        'message' => 'غير مصرح لك بالتحديث. يمكن للمالك فقط تحديث القول.'
    ], 403);
}
```

**ملاحظة مهمة:** حتى **Admin لا يمكنه تحديث** قول مستخدم آخر!

**مثال:**
```bash
# المالك ✅
POST /api/sayings/1/update
Headers: Authorization: Bearer OWNER_TOKEN
Body: { content: "قول محدث" }
→ 200 OK

# Admin ❌ (حتى لو كان admin!)
POST /api/sayings/1/update
Headers: Authorization: Bearer ADMIN_TOKEN
Body: { content: "قول محدث" }
→ 403 Forbidden
{
  "success": false,
  "message": "غير مصرح لك بالتحديث. يمكن للمالك فقط تحديث القول."
}

# User آخر ❌
POST /api/sayings/1/update
Headers: Authorization: Bearer OTHER_USER_TOKEN
Body: { content: "قول محدث" }
→ 403 Forbidden
```

---

### 3️⃣ حذف قول (Delete)
**Endpoint:** `DELETE /api/deleteSaying/{id}`

| المستخدم | الصلاحية | الرد |
|----------|-----------|------|
| **المالك** | ✅ يمكنه الحذف | 200 OK |
| **Admin** | ✅ يمكنه الحذف | 200 OK |
| **User آخر** | ❌ ممنوع | 403 Forbidden |
| **زائر (بدون token)** | ❌ ممنوع | 401 Unauthorized |

**الكود:**
```php
$user = Auth::user();

// التحقق من الصلاحيات: المالك أو Admin
if ($saying->user_id !== $user->id && $user->role !== 'admin') {
    return response()->json([
        'success' => false,
        'message' => 'غير مصرح لك بالحذف. يمكن للمالك أو Admin فقط حذف القول.'
    ], 403);
}
```

**مثال:**
```bash
# المالك ✅
DELETE /api/deleteSaying/1
Headers: Authorization: Bearer OWNER_TOKEN
→ 200 OK

# Admin ✅
DELETE /api/deleteSaying/1
Headers: Authorization: Bearer ADMIN_TOKEN
→ 200 OK

# User آخر ❌
DELETE /api/deleteSaying/1
Headers: Authorization: Bearer OTHER_USER_TOKEN
→ 403 Forbidden
{
  "success": false,
  "message": "غير مصرح لك بالحذف. يمكن للمالك أو Admin فقط حذف القول."
}
```

---

## 📊 جدول الصلاحيات الشامل

| العملية | Admin | المالك | User آخر | زائر |
|---------|-------|--------|----------|------|
| **إنشاء** | ✅ | ❌ | ❌ | ❌ |
| **عرض الكل** | ✅ (الكل) | ✅ (الكل) | ✅ (الكل) | ✅ (العامة فقط) |
| **عرض واحد (عام)** | ✅ | ✅ | ✅ | ✅ |
| **عرض واحد (خاص)** | ✅ | ✅ | ✅ | ❌ |
| **تحديث** | ❌ | ✅ | ❌ | ❌ |
| **حذف** | ✅ | ✅ | ❌ | ❌ |
| **بحث** | ✅ (الكل) | ✅ (الكل) | ✅ (الكل) | ✅ (العامة فقط) |
| **مفضلة** | ✅ | ✅ | ✅ | ❌ |
| **تعليقات** | ✅ | ✅ | ✅ | ✅ (عرض فقط) |

---

## 🎯 سيناريوهات عملية

### سيناريو 1: Admin ينشئ قول
```bash
# 1. Admin ينشئ قول
POST /api/AddSaying
Headers: Authorization: Bearer ADMIN_TOKEN
Body: {
  "type": "saying",
  "content": "الحكمة ضالة المؤمن",
  "is_private": false
}
→ ✅ 201 Created
القول أصبح مملوك للـ Admin (user_id = admin_id)

# 2. Admin يحاول تحديث قوله
POST /api/sayings/1/update
Headers: Authorization: Bearer ADMIN_TOKEN
Body: { "content": "الحكمة ضالة المؤمن - محدث" }
→ ✅ 200 OK (لأنه المالك)

# 3. Admin يحاول حذف قوله
DELETE /api/deleteSaying/1
Headers: Authorization: Bearer ADMIN_TOKEN
→ ✅ 200 OK (لأنه المالك)
```

### سيناريو 2: User عادي يحاول الإنشاء
```bash
# 1. User عادي يحاول الإنشاء
POST /api/AddSaying
Headers: Authorization: Bearer USER_TOKEN
Body: {
  "type": "saying",
  "content": "قول جديد"
}
→ ❌ 403 Forbidden
{
  "success": false,
  "message": "غير مصرح لك بإضافة أقوال. هذه الصلاحية للـ Admin فقط."
}
```

### سيناريو 3: Admin ينشئ قول، ثم Admin آخر يحاول التحديث
```bash
# 1. Admin1 ينشئ قول
POST /api/AddSaying
Headers: Authorization: Bearer ADMIN1_TOKEN
Body: { "type": "saying", "content": "قول من Admin1" }
→ ✅ 201 Created (user_id = admin1_id)

# 2. Admin2 يحاول تحديث قول Admin1
POST /api/sayings/1/update
Headers: Authorization: Bearer ADMIN2_TOKEN
Body: { "content": "محاولة تحديث" }
→ ❌ 403 Forbidden
{
  "success": false,
  "message": "غير مصرح لك بالتحديث. يمكن للمالك فقط تحديث القول."
}

# 3. لكن Admin2 يمكنه حذف قول Admin1
DELETE /api/deleteSaying/1
Headers: Authorization: Bearer ADMIN2_TOKEN
→ ✅ 200 OK (لأنه Admin)
```

---

## ⚠️ ملاحظات مهمة

### 1. التحديث = المالك فقط
- حتى Admin لا يمكنه تحديث قول مستخدم آخر
- فقط من أنشأ القول يمكنه تحديثه

### 2. الحذف = المالك أو Admin
- المالك يمكنه حذف قوله
- Admin يمكنه حذف أي قول (للإدارة)

### 3. الإنشاء = Admin فقط
- لضمان جودة المحتوى
- المستخدمين العاديين لا يمكنهم الإنشاء

---

## 🔒 الفرق بين القصائد والدروس والأقوال

| الميزة | القصائد/الدروس | الأقوال |
|--------|----------------|---------|
| **الإنشاء** | Admin فقط | Admin فقط |
| **التحديث** | المالك أو Admin | المالك فقط |
| **الحذف** | المالك أو Admin | المالك أو Admin |

**الفرق الرئيسي:** في الأقوال، **التحديث للمالك فقط** (حتى Admin ممنوع)

---

**كل شيء محمي بشكل صحيح! 🔐**
