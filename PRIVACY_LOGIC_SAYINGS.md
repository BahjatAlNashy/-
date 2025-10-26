# 🔐 منطق الخصوصية في نظام الأقوال

## 📋 القاعدة الأساسية

كل قول له حقل `is_private`:
- **`false`** = عام (الجميع يراه)
- **`true`** = خاص (المسجلين فقط)

---

## 🎯 كيف يعمل في كل Endpoint؟

### 1️⃣ عرض الكل (index)
**Endpoint:** `GET /api/sayings/getall`

| الحالة | النتيجة |
|--------|---------|
| **بدون Token** | يرجع الأقوال العامة فقط |
| **مع Token** | يرجع الكل (عامة + خاصة) |

**الكود:**
```php
// دعم optional token
if ($request->bearerToken()) {
    $userFromToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
    if ($userFromToken) {
        Auth::login($userFromToken->tokenable);
    }
}

// منطق الخصوصية
if (!Auth::check()) {
    $query->where('is_private', false);
}
```

**مثال:**
```bash
# زائر (بدون Token) - يرى العامة فقط
GET /api/sayings/getall
→ Returns: [قول1 (عام), قول2 (عام), قول3 (عام)]

# مستخدم مسجل (مع Token) - يرى الكل
GET /api/sayings/getall
Headers: Authorization: Bearer TOKEN
→ Returns: [قول1 (عام), قول2 (عام), قول3 (عام), قول4 (خاص), قول5 (خاص)]
```

---

### 2️⃣ البحث (search)
**Endpoint:** `GET /api/sayings/search?keyword=xxx`

| الحالة | النتيجة |
|--------|---------|
| **بدون Token** | يبحث في العامة فقط |
| **مع Token** | يبحث في الكل (عامة + خاصة) |

**الكود:**
```php
// دعم optional token
if ($request->bearerToken()) {
    $userFromToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
    if ($userFromToken) {
        Auth::login($userFromToken->tokenable);
    }
}

// منطق الخصوصية
if (!Auth::check()) {
    $query->where('is_private', false);
}
```

**مثال:**
```bash
# زائر - يبحث في العامة فقط
GET /api/sayings/search?keyword=حكمة
→ Returns: [قول عام يحتوي "حكمة"]

# مستخدم مسجل - يبحث في الكل
GET /api/sayings/search?keyword=حكمة
Headers: Authorization: Bearer TOKEN
→ Returns: [قول عام يحتوي "حكمة", قول خاص يحتوي "حكمة"]
```

---

### 3️⃣ عرض قول واحد (show)
**Endpoint:** `GET /api/sayings/{id}`

| نوع القول | بدون Token | مع Token |
|-----------|------------|----------|
| **عام** | ✅ يعرض | ✅ يعرض |
| **خاص** | ❌ 403 Forbidden | ✅ يعرض |

**الكود:**
```php
// دعم optional token
if ($request->bearerToken()) {
    $userFromToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
    if ($userFromToken) {
        Auth::login($userFromToken->tokenable);
    }
}

$user = Auth::user();
$saying = Saying::find($id);

// منطق الخصوصية
if ($saying->is_private && !$user) {
    return response()->json([
        'success' => false,
        'message' => 'هذا القول خاص، يجب تسجيل الدخول للوصول إليه.'
    ], 403);
}
```

**مثال:**
```bash
# قول عام - الكل يراه
GET /api/sayings/1
→ 200 OK (بيانات القول)

# قول خاص - زائر
GET /api/sayings/5
→ 403 Forbidden

# قول خاص - مستخدم مسجل
GET /api/sayings/5
Headers: Authorization: Bearer TOKEN
→ 200 OK (بيانات القول)
```

---

### 4️⃣ المفضلة (favorites)
**Endpoint:** `GET /api/sayings/favorites`

| الحالة | النتيجة |
|--------|---------|
| **بدون Token** | ❌ يتطلب Auth |
| **مع Token** | ✅ يرجع المفضلة فقط |

**ملاحظة:** هذا Endpoint محمي بـ `->middleware('auth:sanctum')`

---

## 📊 ملخص الحماية

### Endpoints عامة (تدعم optional token):
```
GET /api/sayings/getall        → عامة فقط للزوار، الكل للمسجلين
GET /api/sayings/search        → عامة فقط للزوار، الكل للمسجلين
GET /api/sayings/{id}          → عامة للزوار، الكل للمسجلين (403 للخاص)
GET /api/sayings/{id}/comments → عامة للجميع
```

### Endpoints محمية (تتطلب token):
```
POST   /api/AddSaying                ← auth:sanctum
POST   /api/sayings/{id}/update      ← auth:sanctum
DELETE /api/deleteSaying/{id}        ← auth:sanctum
POST   /api/FavoriteSaying/{id}      ← auth:sanctum
GET    /api/sayings/favorites        ← auth:sanctum
POST   /api/sayings/{id}/comments    ← auth:sanctum
PUT    /api/sayings/comments/{id}    ← auth:sanctum
DELETE /api/sayings/comments/{id}    ← auth:sanctum
```

---

## ✅ أفضل الممارسات

### للمطورين:
1. ✅ استخدم `is_private: false` كـ default
2. ✅ دائماً تحقق من `Auth::check()` في القوائم
3. ✅ دائماً تحقق من `is_private` في عرض واحد
4. ✅ استخدم optional token support في الـ public endpoints

### للمستخدمين:
1. ✅ **بدون Token**: تصفح الأقوال العامة فقط
2. ✅ **مع Token**: تصفح جميع الأقوال + إضافة/تعديل/حذف

---

## 💡 سيناريوهات عملية

### سيناريو 1: زائر يتصفح
```bash
# يرى العامة فقط
GET /api/sayings/getall
→ [قول1 (عام), قول2 (عام)]

# يبحث في العامة فقط
GET /api/sayings/search?keyword=حكمة
→ [قول1 (عام)]

# يحاول الوصول لقول خاص
GET /api/sayings/10
→ 403 Forbidden
```

### سيناريو 2: مستخدم مسجل
```bash
# يرى الكل
GET /api/sayings/getall
Headers: Authorization: Bearer TOKEN
→ [قول1 (عام), قول2 (عام), قول3 (خاص), قول4 (خاص)]

# يبحث في الكل
GET /api/sayings/search?keyword=حكمة
Headers: Authorization: Bearer TOKEN
→ [قول1 (عام), قول3 (خاص)]

# يصل للقول الخاص
GET /api/sayings/10
Headers: Authorization: Bearer TOKEN
→ 200 OK
```

### سيناريو 3: صاحب القول
```bash
# ينشئ قول خاص
POST /api/AddSaying
Headers: Authorization: Bearer TOKEN
Body: {
  type: saying,
  content: قول خاص جداً,
  is_private: true
}
→ 201 Created

# فقط المستخدمين المسجلين الآخرين يرونه
# الزوار لن يروه أبداً
```

---

**كل شيء يعمل بشكل آمن! 🔐**
