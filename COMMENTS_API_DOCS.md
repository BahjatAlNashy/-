# توثيق API للتعليقات (Comments)

نظام كامل لإدارة التعليقات على القصائد والدروس مع دعم CRUD operations.

---

## 📝 تعليقات القصائد (Poem Comments)

### 1. عرض جميع تعليقات قصيدة (Read All)
**Endpoint:** `GET /api/poems/{poem_id}/comments`  
**Authentication:** Optional (Public)

#### Response:
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "content": "تعليق رائع على القصيدة",
        "created_at": "2024-10-12T10:30:00.000000Z",
        "updated_at": "2024-10-12T10:30:00.000000Z",
        "user": {
          "id": 5,
          "name": "أحمد"
        }
      }
    ],
    "per_page": 10,
    "total": 25
  }
}
```

#### ملاحظات:
- متاح للجميع (لا يتطلب مصادقة)
- مرتب بالأحدث أولاً
- يدعم Pagination (10 تعليقات لكل صفحة)

---

### 2. إضافة تعليق على قصيدة (Create)
**Endpoint:** `POST /api/poems/{poem_id}/comments`  
**Authentication:** Required (Bearer Token)  
**Content-Type:** `application/json`

#### Request Body:
```json
{
  "content": "تعليق جديد على القصيدة"
}
```

#### Validation:
- `content`: required, string, min:5, max:500

#### Response:
```json
{
  "success": true,
  "message": "تم إضافة التعليق بنجاح.",
  "data": {
    "id": 1,
    "content": "تعليق جديد على القصيدة",
    "poem_id": 5,
    "user_id": 3,
    "created_at": "2024-10-12T10:30:00.000000Z",
    "updated_at": "2024-10-12T10:30:00.000000Z",
    "user": {
      "id": 3,
      "name": "محمد"
    }
  }
}
```

---

### 3. تحديث تعليق على قصيدة (Update)
**Endpoint:** `PUT /api/poems/comments/{comment_id}`  
**Authentication:** Required (Bearer Token - Comment Owner Only)  
**Content-Type:** `application/json`

#### Request Body:
```json
{
  "content": "تعليق محدث"
}
```

#### Validation:
- `content`: required, string, min:5, max:500

#### Authorization:
- **فقط صاحب التعليق** يمكنه التحديث (Admin لا يمكنه التعديل)

#### Response:
```json
{
  "success": true,
  "message": "تم تحديث التعليق بنجاح.",
  "data": {
    "id": 1,
    "content": "تعليق محدث",
    "created_at": "2024-10-12T10:30:00.000000Z",
    "updated_at": "2024-10-12T11:00:00.000000Z",
    "user": {
      "id": 3,
      "name": "محمد"
    }
  }
}
```

#### Error Response (403):
```json
{
  "success": false,
  "message": "غير مصرح لك بتعديل هذا التعليق."
}
```

---

### 4. حذف تعليق على قصيدة (Delete)
**Endpoint:** `DELETE /api/poems/comments/{comment_id}`  
**Authentication:** Required (Bearer Token - Comment Owner or Admin)

#### Authorization:
- صاحب التعليق أو **Admin** يمكنهما الحذف

#### Response:
```json
{
  "success": true,
  "message": "تم حذف التعليق بنجاح."
}
```

#### Error Response (403):
```json
{
  "success": false,
  "message": "غير مصرح لك بحذف هذا التعليق."
}
```

---

## 📚 تعليقات الدروس (Lesson Comments)

جميع العمليات **مطابقة تماماً** لتعليقات القصائد، مع تغيير المسارات فقط:

### 1. عرض جميع تعليقات درس (Read All)
**Endpoint:** `GET /api/lessons/{lesson_id}/comments`  
**Authentication:** Optional (Public)

---

### 2. إضافة تعليق على درس (Create)
**Endpoint:** `POST /api/lessons/{lesson_id}/comments`  
**Authentication:** Required (Bearer Token)

#### Request Body:
```json
{
  "content": "تعليق جديد على الدرس"
}
```

---

### 3. تحديث تعليق على درس (Update)
**Endpoint:** `PUT /api/lessons/comments/{comment_id}`  
**Authentication:** Required (Bearer Token - Comment Owner Only)

#### Request Body:
```json
{
  "content": "تعليق محدث"
}
```

#### Authorization:
- **فقط صاحب التعليق** يمكنه التحديث (Admin لا يمكنه التعديل)

---

### 4. حذف تعليق على درس (Delete)
**Endpoint:** `DELETE /api/lessons/comments/{comment_id}`  
**Authentication:** Required (Bearer Token - Comment Owner or Admin)

---

## 📊 ملخص Endpoints

### تعليقات القصائد:
```
GET    /api/poems/{poem_id}/comments           # عرض الكل (Public)
POST   /api/poems/{poem_id}/comments           # إضافة (Auth)
PUT    /api/poems/comments/{comment_id}        # تحديث (Owner/Admin)
DELETE /api/poems/comments/{comment_id}        # حذف (Owner/Admin)
```

### تعليقات الدروس:
```
GET    /api/lessons/{lesson_id}/comments       # عرض الكل (Public)
POST   /api/lessons/{lesson_id}/comments       # إضافة (Auth)
PUT    /api/lessons/comments/{comment_id}      # تحديث (Owner/Admin)
DELETE /api/lessons/comments/{comment_id}      # حذف (Owner/Admin)
```

---

## 🔐 الصلاحيات (Authorization)

| العملية | Admin | Comment Owner | Other Users | Public |
|---------|-------|---------------|-------------|--------|
| **Read** | ✅ | ✅ | ✅ | ✅ |
| **Create** | ✅ | ✅ | ✅ | ❌ |
| **Update** | ❌ | ✅ | ❌ | ❌ |
| **Delete** | ✅ | ✅ | ❌ | ❌ |

### قواعد الصلاحيات:
- **Read (عرض):** متاح للجميع بدون تسجيل دخول
- **Create (إضافة):** يتطلب تسجيل دخول
- **Update (تحديث):** **فقط صاحب التعليق** (Admin لا يمكنه التعديل)
- **Delete (حذف):** صاحب التعليق أو Admin

---

## 📝 Validation Rules

### Content (المحتوى):
```
required   - إلزامي
string     - نص
min: 5     - الحد الأدنى 5 أحرف
max: 500   - الحد الأقصى 500 حرف
```

---

## 🔄 Database Structure

### comments Table:
```sql
id           - Primary Key
poem_id      - Foreign Key (nullable)
lesson_id    - Foreign Key (nullable)
user_id      - Foreign Key (required)
content      - Text (required)
created_at   - Timestamp
updated_at   - Timestamp
```

### Relationships:
- `comment.user` - belongsTo (User)
- `comment.poem` - belongsTo (Poem)
- `comment.lesson` - belongsTo (Lesson)

---

## 💡 أمثلة عملية

### مثال 1: إضافة تعليق على قصيدة
```bash
curl -X POST https://your-domain.com/api/poems/5/comments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content": "قصيدة رائعة جداً"}'
```

### مثال 2: تحديث تعليق
```bash
curl -X PUT https://your-domain.com/api/poems/comments/12 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content": "تعليق محدث بعد التفكير"}'
```

### مثال 3: حذف تعليق
```bash
curl -X DELETE https://your-domain.com/api/lessons/comments/8 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### مثال 4: عرض تعليقات درس
```bash
curl -X GET https://your-domain.com/api/lessons/3/comments
```

---

## 🚨 Error Responses

### 401 Unauthorized (لم يسجل الدخول):
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden (غير مصرح):
```json
{
  "success": false,
  "message": "غير مصرح لك بتعديل هذا التعليق."
}
```

### 404 Not Found (غير موجود):
```json
{
  "message": "No query results for model..."
}
```

### 422 Validation Error:
```json
{
  "message": "The content field is required.",
  "errors": {
    "content": [
      "The content field is required."
    ]
  }
}
```

---

## 📱 JavaScript/React Example

```javascript
// إضافة تعليق
async function addComment(poemId, content, token) {
  const response = await fetch(`/api/poems/${poemId}/comments`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ content })
  });
  
  return await response.json();
}

// تحديث تعليق
async function updateComment(commentId, content, token) {
  const response = await fetch(`/api/poems/comments/${commentId}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ content })
  });
  
  return await response.json();
}

// حذف تعليق
async function deleteComment(commentId, token) {
  const response = await fetch(`/api/poems/comments/${commentId}`, {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  return await response.json();
}

// عرض تعليقات
async function getComments(lessonId, page = 1) {
  const response = await fetch(`/api/lessons/${lessonId}/comments?page=${page}`);
  return await response.json();
}
```

---

## ✅ Features Summary

- ✅ **CRUD كامل** - إنشاء، قراءة، تحديث، حذف
- ✅ **دعم القصائد والدروس** - نفس الوظائف للاثنين
- ✅ **Pagination** - 10 تعليقات لكل صفحة
- ✅ **Authorization** - فقط المالك أو Admin للتعديل/الحذف
- ✅ **Validation** - التحقق من المحتوى (5-500 حرف)
- ✅ **User Info** - إرجاع معلومات المستخدم مع كل تعليق
- ✅ **Timestamps** - تتبع وقت الإنشاء والتحديث
- ✅ **Public Read** - يمكن للجميع قراءة التعليقات
