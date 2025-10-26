# 📄 Comments Pagination Documentation

جميع endpoints التعليقات في النظام تدعم Pagination بشكل موحد.

---

## 📊 Response Structure

جميع endpoints التعليقات ترجع نفس البنية:

```json
{
  "success": true,
  "message": "تم جلب التعليقات بنجاح",
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73,
    "from": 1,
    "to": 15
  },
  "data": [
    {
      "id": 1,
      "content": "تعليق رائع!",
      "created_at": "2024-10-21T10:30:00.000000Z",
      "updated_at": "2024-10-21T10:30:00.000000Z",
      "user": {
        "id": 1,
        "name": "أحمد"
      }
    }
  ]
}
```

---

## 🎯 Endpoints

### 1. تعليقات القصائد

#### عرض جميع التعليقات على قصيدة
```
GET /api/poems/{poem_id}/comments?page=1
```

**مثال:**
```bash
GET http://localhost:8000/api/poems/1/comments
GET http://localhost:8000/api/poems/1/comments?page=2
```

---

### 2. تعليقات الدروس

#### عرض جميع التعليقات على درس
```
GET /api/lessons/{lesson_id}/comments?page=1
```

**مثال:**
```bash
GET http://localhost:8000/api/lessons/1/comments
GET http://localhost:8000/api/lessons/1/comments?page=2
```

---

### 3. تعليقات الأقوال

#### عرض جميع التعليقات على قول
```
GET /api/sayings/{saying_id}/comments?page=1
```

**مثال:**
```bash
GET http://localhost:8000/api/sayings/1/comments
GET http://localhost:8000/api/sayings/1/comments?page=2
```

---

## 📋 Meta Object

### الحقول:

| الحقل | الوصف | مثال |
|-------|-------|------|
| `current_page` | رقم الصفحة الحالية | `1` |
| `last_page` | رقم آخر صفحة | `5` |
| `per_page` | عدد العناصر في كل صفحة | `15` |
| `total` | إجمالي عدد التعليقات | `73` |
| `from` | رقم أول عنصر في الصفحة | `1` |
| `to` | رقم آخر عنصر في الصفحة | `15` |

---

## 🧪 أمثلة الاستخدام

### مثال 1: جلب الصفحة الأولى من تعليقات قصيدة

**Request:**
```bash
GET http://localhost:8000/api/poems/1/comments
```

**Response:**
```json
{
  "success": true,
  "message": "تم جلب التعليقات بنجاح",
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42,
    "from": 1,
    "to": 15
  },
  "data": [
    {
      "id": 42,
      "content": "تعليق حديث",
      "created_at": "2024-10-21T14:00:00.000000Z",
      "user": {
        "id": 5,
        "name": "فاطمة"
      }
    },
    // ... 14 تعليق آخر
  ]
}
```

---

### مثال 2: جلب الصفحة الثانية

**Request:**
```bash
GET http://localhost:8000/api/poems/1/comments?page=2
```

**Response:**
```json
{
  "success": true,
  "message": "تم جلب التعليقات بنجاح",
  "meta": {
    "current_page": 2,
    "last_page": 3,
    "per_page": 15,
    "total": 42,
    "from": 16,
    "to": 30
  },
  "data": [
    // 15 تعليق من 16 إلى 30
  ]
}
```

---

### مثال 3: آخر صفحة

**Request:**
```bash
GET http://localhost:8000/api/poems/1/comments?page=3
```

**Response:**
```json
{
  "success": true,
  "message": "تم جلب التعليقات بنجاح",
  "meta": {
    "current_page": 3,
    "last_page": 3,
    "per_page": 15,
    "total": 42,
    "from": 31,
    "to": 42
  },
  "data": [
    // 12 تعليق من 31 إلى 42
  ]
}
```

---

## 💻 استخدام في React/Vue

### React Example:

```javascript
import { useState, useEffect } from 'react'
import axios from 'axios'

function CommentsList({ poemId }) {
  const [comments, setComments] = useState([])
  const [meta, setMeta] = useState(null)
  const [loading, setLoading] = useState(true)
  const [currentPage, setCurrentPage] = useState(1)

  useEffect(() => {
    fetchComments(currentPage)
  }, [currentPage])

  const fetchComments = async (page) => {
    try {
      setLoading(true)
      const response = await axios.get(
        `http://localhost:8000/api/poems/${poemId}/comments?page=${page}`
      )
      
      if (response.data.success) {
        setComments(response.data.data)
        setMeta(response.data.meta)
      }
    } catch (error) {
      console.error('Error:', error)
    } finally {
      setLoading(false)
    }
  }

  if (loading) return <div>Loading...</div>

  return (
    <div>
      {/* Comments List */}
      {comments.map(comment => (
        <div key={comment.id} className="comment">
          <p><strong>{comment.user.name}</strong></p>
          <p>{comment.content}</p>
        </div>
      ))}

      {/* Pagination */}
      {meta && (
        <div className="pagination">
          <button 
            onClick={() => setCurrentPage(prev => prev - 1)}
            disabled={currentPage === 1}
          >
            السابق
          </button>
          
          <span>
            الصفحة {meta.current_page} من {meta.last_page}
          </span>
          
          <button 
            onClick={() => setCurrentPage(prev => prev + 1)}
            disabled={currentPage === meta.last_page}
          >
            التالي
          </button>
        </div>
      )}

      {/* Info */}
      {meta && (
        <p className="text-sm text-gray-500">
          عرض {meta.from} - {meta.to} من أصل {meta.total} تعليق
        </p>
      )}
    </div>
  )
}

export default CommentsList
```

---

### Vue Example:

```vue
<template>
  <div>
    <!-- Comments List -->
    <div v-for="comment in comments" :key="comment.id" class="comment">
      <p><strong>{{ comment.user.name }}</strong></p>
      <p>{{ comment.content }}</p>
    </div>

    <!-- Pagination -->
    <div v-if="meta" class="pagination">
      <button 
        @click="currentPage--" 
        :disabled="currentPage === 1"
      >
        السابق
      </button>
      
      <span>الصفحة {{ meta.current_page }} من {{ meta.last_page }}</span>
      
      <button 
        @click="currentPage++" 
        :disabled="currentPage === meta.last_page"
      >
        التالي
      </button>
    </div>

    <!-- Info -->
    <p v-if="meta" class="text-sm text-gray-500">
      عرض {{ meta.from }} - {{ meta.to }} من أصل {{ meta.total }} تعليق
    </p>
  </div>
</template>

<script>
export default {
  props: ['poemId'],
  data() {
    return {
      comments: [],
      meta: null,
      currentPage: 1
    }
  },
  watch: {
    currentPage() {
      this.fetchComments()
    }
  },
  mounted() {
    this.fetchComments()
  },
  methods: {
    async fetchComments() {
      try {
        const response = await axios.get(
          `http://localhost:8000/api/poems/${this.poemId}/comments?page=${this.currentPage}`
        )
        
        if (response.data.success) {
          this.comments = response.data.data
          this.meta = response.data.meta
        }
      } catch (error) {
        console.error('Error:', error)
      }
    }
  }
}
</script>
```

---

## 🎨 Pagination Component (Reusable)

```jsx
function Pagination({ meta, onPageChange }) {
  if (!meta) return null

  const { current_page, last_page, from, to, total } = meta

  return (
    <div className="flex items-center justify-between mt-6">
      {/* Info */}
      <p className="text-sm text-gray-600">
        عرض <span className="font-medium">{from}</span> - 
        <span className="font-medium">{to}</span> من أصل 
        <span className="font-medium">{total}</span> تعليق
      </p>

      {/* Buttons */}
      <div className="flex gap-2">
        <button
          onClick={() => onPageChange(current_page - 1)}
          disabled={current_page === 1}
          className="px-4 py-2 bg-blue-500 text-white rounded disabled:opacity-50"
        >
          ← السابق
        </button>

        <span className="px-4 py-2 bg-gray-100 rounded">
          {current_page} / {last_page}
        </span>

        <button
          onClick={() => onPageChange(current_page + 1)}
          disabled={current_page === last_page}
          className="px-4 py-2 bg-blue-500 text-white rounded disabled:opacity-50"
        >
          التالي →
        </button>
      </div>
    </div>
  )
}

// الاستخدام:
<Pagination 
  meta={meta} 
  onPageChange={(page) => setCurrentPage(page)} 
/>
```

---

## 📊 ملخص الـ Endpoints

| النوع | Endpoint | Pagination | Per Page |
|-------|----------|------------|----------|
| القصائد | `GET /api/poems/{id}/comments` | ✅ | 15 |
| الدروس | `GET /api/lessons/{id}/comments` | ✅ | 15 |
| الأقوال | `GET /api/sayings/{id}/comments` | ✅ | 15 |

---

## ⚙️ تخصيص عدد العناصر

إذا أردت تغيير عدد التعليقات في كل صفحة، عدّل `paginate(15)` في:

- `CommentController::index()` - السطر 53
- `CommentController::indexLessonComments()` - السطر 167
- `CommentController::indexSayingComments()` - السطر 281

---

## ✅ الخلاصة

- ✅ جميع التعليقات تدعم Pagination
- ✅ Response موحد مع `meta` object
- ✅ 15 تعليق في كل صفحة
- ✅ مرتبة من الأحدث للأقدم (`latest()`)
- ✅ تحميل اسم المستخدم فقط (`with('user:id,name')`)

---

**Pagination جاهز لجميع التعليقات! 🎉**
