# 📄 دليل استخدام Pagination في جميع الأنظمة

## 🎯 كيفية الاستخدام

جميع endpoints البحث تدعم pagination تلقائياً عبر query parameter `page`.

---

## 📋 الأنظمة المدعومة

### 1. القصائد (Poems)
```
GET /api/poems/search?page=1
GET /api/poems/search?keyword=حب&page=2
GET /api/poems/search?year=2024&page=3
```

### 2. الدروس (Lessons)
```
GET /api/lessons/search?page=1
GET /api/lessons/search?keyword=فقه&page=2
```

### 3. الأقوال (Sayings)
```
GET /api/sayings/search?page=1
GET /api/sayings/search?type=saying&page=2
GET /api/sayings/search?keyword=حكمة&type=saying&page=3
```

### 4. المشاركات (Posts)
```
GET /api/posts/search?page=1
GET /api/posts/search?keyword=تجربة&page=2
```

### 5. الصور (Images)
```
GET /api/images/search?page=1
GET /api/images/search?keyword=منظر&page=2
```

---

## 📊 Response Structure

جميع الـ endpoints ترجع نفس البنية:

```json
{
  "success": true,
  "message": "تم جلب البيانات بنجاح",
  "meta": {
    "current_page": 2,        // الصفحة الحالية
    "last_page": 5,           // آخر صفحة
    "per_page": 15,           // عدد العناصر في الصفحة
    "total": 73,              // إجمالي العناصر
    "from": 16,               // رقم العنصر الأول في الصفحة
    "to": 30                  // رقم العنصر الأخير في الصفحة
  },
  "data": [
    // ... البيانات
  ]
}
```

---

## 🧪 أمثلة عملية

### مثال 1: عرض الصفحة الأولى من القصائد
```bash
curl "http://localhost:8000/api/poems/search"
# أو
curl "http://localhost:8000/api/poems/search?page=1"
```

**Response:**
```json
{
  "success": true,
  "message": "تم جلب القصائد بنجاح",
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73,
    "from": 1,
    "to": 15
  },
  "data": [
    // 15 قصيدة
  ]
}
```

---

### مثال 2: الانتقال للصفحة الثانية
```bash
curl "http://localhost:8000/api/poems/search?page=2"
```

**Response:**
```json
{
  "success": true,
  "message": "تم جلب القصائد بنجاح",
  "meta": {
    "current_page": 2,
    "last_page": 5,
    "per_page": 15,
    "total": 73,
    "from": 16,
    "to": 30
  },
  "data": [
    // 15 قصيدة أخرى
  ]
}
```

---

### مثال 3: البحث مع Pagination
```bash
curl "http://localhost:8000/api/poems/search?keyword=حب&page=1"
```

**Response:**
```json
{
  "success": true,
  "message": "تم جلب القصائد بنجاح",
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 15,
    "total": 23,
    "from": 1,
    "to": 15
  },
  "data": [
    // 15 قصيدة تحتوي على كلمة "حب"
  ]
}
```

---

### مثال 4: الصفحة الأخيرة
```bash
curl "http://localhost:8000/api/poems/search?keyword=حب&page=2"
```

**Response:**
```json
{
  "success": true,
  "message": "تم جلب القصائد بنجاح",
  "meta": {
    "current_page": 2,
    "last_page": 2,
    "per_page": 15,
    "total": 23,
    "from": 16,
    "to": 23        // آخر 8 عناصر فقط
  },
  "data": [
    // 8 قصائد (الباقي)
  ]
}
```

---

## 🎨 استخدام في Frontend

### React / JavaScript Example

```javascript
const [currentPage, setCurrentPage] = useState(1);
const [poems, setPoems] = useState([]);
const [meta, setMeta] = useState({});

const fetchPoems = async (page = 1) => {
  const response = await fetch(
    `http://localhost:8000/api/poems/search?page=${page}`,
    {
      headers: {
        'Authorization': `Bearer ${token}` // optional
      }
    }
  );
  
  const data = await response.json();
  
  setPoems(data.data);
  setMeta(data.meta);
  setCurrentPage(data.meta.current_page);
};

// عرض الصفحة التالية
const nextPage = () => {
  if (meta.current_page < meta.last_page) {
    fetchPoems(currentPage + 1);
  }
};

// عرض الصفحة السابقة
const prevPage = () => {
  if (meta.current_page > 1) {
    fetchPoems(currentPage - 1);
  }
};

// الانتقال لصفحة محددة
const goToPage = (pageNumber) => {
  fetchPoems(pageNumber);
};
```

---

### Vue.js Example

```vue
<template>
  <div>
    <!-- عرض البيانات -->
    <div v-for="poem in poems" :key="poem.id">
      {{ poem.title }}
    </div>

    <!-- أزرار التنقل -->
    <div class="pagination">
      <button @click="prevPage" :disabled="meta.current_page === 1">
        السابق
      </button>
      
      <span>صفحة {{ meta.current_page }} من {{ meta.last_page }}</span>
      
      <button @click="nextPage" :disabled="meta.current_page === meta.last_page">
        التالي
      </button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      poems: [],
      meta: {}
    }
  },
  methods: {
    async fetchPoems(page = 1) {
      const response = await fetch(
        `http://localhost:8000/api/poems/search?page=${page}`
      );
      const data = await response.json();
      
      this.poems = data.data;
      this.meta = data.meta;
    },
    nextPage() {
      if (this.meta.current_page < this.meta.last_page) {
        this.fetchPoems(this.meta.current_page + 1);
      }
    },
    prevPage() {
      if (this.meta.current_page > 1) {
        this.fetchPoems(this.meta.current_page - 1);
      }
    }
  },
  mounted() {
    this.fetchPoems();
  }
}
</script>
```

---

## 📊 ملخص الإعدادات

| النظام | Endpoint | Items per Page | Sorting |
|--------|----------|----------------|---------|
| القصائد | `/api/poems/search` | 15 | `saying_date DESC` |
| الدروس | `/api/lessons/search` | 15 | `saying_date DESC` |
| الأقوال | `/api/sayings/search` | 15 | `created_at DESC` |
| المشاركات | `/api/posts/search` | 15 | `created_at DESC` |
| الصور | `/api/images/search` | 15 | `created_at DESC` |

---

## 🔍 Filters + Pagination

يمكنك الجمع بين الفلاتر و Pagination:

### القصائد:
```bash
# البحث بالكلمة + السنة + الصفحة
GET /api/poems/search?keyword=حب&year=2024&page=2

# البحث بنوع المصدر + الصفحة
GET /api/poems/search?source_type=audio&page=3
```

### الدروس:
```bash
# البحث بالكلمة + الشهر + الصفحة
GET /api/lessons/search?keyword=فقه&month=5&page=2
```

### الأقوال:
```bash
# البحث بالنوع + الكلمة + الصفحة
GET /api/sayings/search?type=saying&keyword=حكمة&page=2
```

### المشاركات:
```bash
# البحث بالكلمة + الصفحة
GET /api/posts/search?keyword=تجربة&page=2
```

### الصور:
```bash
# البحث بالكلمة + الصفحة
GET /api/images/search?keyword=منظر&page=2
```

---

## ✅ الميزات

- ✅ **15 عنصر** في كل صفحة
- ✅ **Automatic Pagination** عبر `?page=N`
- ✅ **Meta Information** كاملة في كل response
- ✅ **Compatible** مع جميع الفلاتر
- ✅ **Consistent Structure** في جميع الأنظمة

---

## 🎯 نصائح

1. **الصفحة الافتراضية:** إذا لم ترسل `page`، سيتم عرض الصفحة الأولى تلقائياً
2. **صفحة غير موجودة:** إذا طلبت `page=999` وكان آخر صفحة هو 5، سيرجع array فارغ
3. **عدد العناصر:** يمكنك تغيير `15` في الكود إلى أي رقم تريده
4. **الترتيب:** جميع النتائج مرتبة من الأحدث للأقدم

---

**Pagination جاهز في جميع الأنظمة! 🎉**
