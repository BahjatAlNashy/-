# 📰 API المستجدات (News/Updates)

عرض ديناميكي لجميع الإضافات الجديدة والتعديلات في النظام.

---

## 🎯 المميزات

- ✅ **الإضافات الجديدة** - كل ما تم إضافته حديثاً
- ✅ **التعديلات** - كل ما تم تحديثه
- ✅ **فترة ديناميكية** - يمكن تحديد عدد الأيام
- ✅ **فلترة حسب النوع** - قصائد، دروس، أقوال، صور، أنشطة
- ✅ **Pagination** - 20 عنصر/صفحة
- ✅ **خصوصية ذكية** - عام للزوار، عام+خاص للمسجلين
- ✅ **حالة العنصر** - جديد أو محدث

---

## 📊 Endpoints

### 1. عرض المستجدات

```
GET /api/news
```

#### Parameters:

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `days` | integer | No | 7 | عدد الأيام للرجوع (7، 14، 30) |
| `page` | integer | No | 1 | رقم الصفحة |
| `type` | string | No | all | فلترة: `poems`, `lessons`, `sayings`, `images`, `activities` |

#### Headers:
```
Authorization: Bearer {token}  // اختياري
```

#### Response:

```json
{
  "success": true,
  "message": "تم جلب المستجدات بنجاح",
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 52,
    "from": 1,
    "to": 20,
    "period_days": 7,
    "start_date": "2025-10-14"
  },
  "statistics": {
    "poems_count": 15,
    "lessons_count": 12,
    "sayings_count": 18,
    "images_count": 7,
    "activities_count": 10,
    "total_count": 62,
    "new_count": 45,
    "updated_count": 17
  },
  "filter": {
    "type": null,
    "available_types": ["poems", "lessons", "sayings", "images", "activities"]
  },
  "data": [
    {
      "id": 1,
      "type": "poem",
      "type_ar": "قصيدة",
      "title": "قصيدة الحب",
      "description": "وصف القصيدة...",
      "is_private": false,
      "author_name": "أحمد",
      "status": "updated",
      "status_ar": "محدث",
      "created_at": "2025-10-15 10:00:00",
      "updated_at": "2025-10-21 14:30:00",
      "created_at_human": "منذ 6 أيام",
      "updated_at_human": "منذ ساعتين"
    },
    {
      "id": 5,
      "type": "lesson",
      "type_ar": "درس",
      "title": "درس في النحو",
      "description": "شرح مفصل...",
      "is_private": true,
      "author_name": "محمد",
      "status": "new",
      "status_ar": "جديد",
      "created_at": "2025-10-21 12:00:00",
      "updated_at": "2025-10-21 12:00:00",
      "created_at_human": "منذ 4 ساعات",
      "updated_at_human": "منذ 4 ساعات"
    }
  ]
}
```

---

### 2. إحصائيات المستجدات

```
GET /api/news/statistics
```

#### Parameters:

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `days` | integer | No | 7 | عدد الأيام |

#### Response:

```json
{
  "success": true,
  "data": {
    "period_days": 7,
    "start_date": "2025-10-14",
    "poems_count": 15,
    "lessons_count": 12,
    "sayings_count": 18,
    "images_count": 7,
    "activities_count": 10,
    "total_count": 62
  }
}
```

---

## 🧪 أمثلة الاستخدام

### مثال 1: جميع المستجدات (آخر 7 أيام)

**Request:**
```bash
GET http://localhost:8000/api/news
```

**Response:**
- جميع الإضافات والتعديلات من آخر 7 أيام
- 20 عنصر في الصفحة الأولى

---

### مثال 2: القصائد فقط

**Request:**
```bash
GET http://localhost:8000/api/news?type=poems
```

**Response:**
- القصائد الجديدة والمحدثة فقط

---

### مثال 3: الدروس من آخر 14 يوم

**Request:**
```bash
GET http://localhost:8000/api/news?type=lessons&days=14
```

**Response:**
- الدروس الجديدة والمحدثة من آخر 14 يوم

---

### مثال 4: الأنشطة (الصفحة 2)

**Request:**
```bash
GET http://localhost:8000/api/news?type=activities&page=2
```

**Response:**
- الأنشطة من العنصر 21 إلى 40

---

### مثال 5: الصور من آخر شهر

**Request:**
```bash
GET http://localhost:8000/api/news?type=images&days=30
```

**Response:**
- الصور الجديدة والمحدثة من آخر 30 يوم

---

### مثال 6: مع token (للمسجلين)

**Request:**
```bash
GET http://localhost:8000/api/news
Authorization: Bearer YOUR_TOKEN
```

**Response:**
- يرى العام + الخاص

---

## 📋 أنواع المستجدات

| Type | Type AR | Description |
|------|---------|-------------|
| `poem` | قصيدة | قصيدة |
| `lesson` | درس | درس |
| `saying` | قول/دعاء | قول أو دعاء |
| `image` | صورة | صورة |
| `activity` | نشاط | نشاط |

---

## 🏷️ حالة العنصر (Status)

| Status | Status AR | Description |
|--------|-----------|-------------|
| `new` | جديد | تم إضافته خلال الفترة المحددة |
| `updated` | محدث | تم تحديثه خلال الفترة المحددة |

---

## 🔒 منطق الخصوصية

### للزوار (بدون token):
```
✅ يرون: العام فقط (is_private = false)
❌ لا يرون: الخاص (is_private = true)
```

### للمسجلين (مع token):
```
✅ يرون: العام + الخاص
```

---

## 💻 استخدام في React

```jsx
import { useState, useEffect } from 'react'
import axios from 'axios'

function NewsPage() {
  const [news, setNews] = useState([])
  const [meta, setMeta] = useState(null)
  const [statistics, setStatistics] = useState(null)
  const [days, setDays] = useState(7)
  const [type, setType] = useState(null)
  const [page, setPage] = useState(1)

  useEffect(() => {
    fetchNews()
  }, [days, type, page])

  const fetchNews = async () => {
    try {
      const params = new URLSearchParams()
      if (days) params.append('days', days)
      if (type) params.append('type', type)
      if (page) params.append('page', page)

      const response = await axios.get(
        `http://localhost:8000/api/news?${params}`,
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        }
      )
      
      if (response.data.success) {
        setNews(response.data.data)
        setMeta(response.data.meta)
        setStatistics(response.data.statistics)
      }
    } catch (error) {
      console.error('Error:', error)
    }
  }

  return (
    <div className="max-w-6xl mx-auto p-6">
      {/* Header */}
      <h1 className="text-3xl font-bold mb-6">📰 المستجدات</h1>

      {/* Filters */}
      <div className="mb-6 space-y-4">
        {/* Period Filter */}
        <div className="flex gap-2">
          <button 
            onClick={() => setDays(7)}
            className={`px-4 py-2 rounded ${days === 7 ? 'bg-blue-600 text-white' : 'bg-gray-200'}`}
          >
            آخر 7 أيام
          </button>
          <button 
            onClick={() => setDays(14)}
            className={`px-4 py-2 rounded ${days === 14 ? 'bg-blue-600 text-white' : 'bg-gray-200'}`}
          >
            آخر 14 يوم
          </button>
          <button 
            onClick={() => setDays(30)}
            className={`px-4 py-2 rounded ${days === 30 ? 'bg-blue-600 text-white' : 'bg-gray-200'}`}
          >
            آخر شهر
          </button>
        </div>

        {/* Type Filter */}
        <div className="flex gap-2">
          <button 
            onClick={() => setType(null)}
            className={`px-4 py-2 rounded ${!type ? 'bg-green-600 text-white' : 'bg-gray-200'}`}
          >
            الكل
          </button>
          <button 
            onClick={() => setType('poems')}
            className={`px-4 py-2 rounded ${type === 'poems' ? 'bg-green-600 text-white' : 'bg-gray-200'}`}
          >
            قصائد
          </button>
          <button 
            onClick={() => setType('lessons')}
            className={`px-4 py-2 rounded ${type === 'lessons' ? 'bg-green-600 text-white' : 'bg-gray-200'}`}
          >
            دروس
          </button>
          <button 
            onClick={() => setType('sayings')}
            className={`px-4 py-2 rounded ${type === 'sayings' ? 'bg-green-600 text-white' : 'bg-gray-200'}`}
          >
            أقوال
          </button>
          <button 
            onClick={() => setType('images')}
            className={`px-4 py-2 rounded ${type === 'images' ? 'bg-green-600 text-white' : 'bg-gray-200'}`}
          >
            صور
          </button>
          <button 
            onClick={() => setType('activities')}
            className={`px-4 py-2 rounded ${type === 'activities' ? 'bg-green-600 text-white' : 'bg-gray-200'}`}
          >
            أنشطة
          </button>
        </div>
      </div>

      {/* Statistics */}
      {statistics && (
        <div className="grid grid-cols-6 gap-4 mb-6">
          <div className="bg-blue-100 p-4 rounded">
            <p className="text-sm text-gray-600">القصائد</p>
            <p className="text-2xl font-bold">{statistics.poems_count}</p>
          </div>
          <div className="bg-green-100 p-4 rounded">
            <p className="text-sm text-gray-600">الدروس</p>
            <p className="text-2xl font-bold">{statistics.lessons_count}</p>
          </div>
          <div className="bg-yellow-100 p-4 rounded">
            <p className="text-sm text-gray-600">الأقوال</p>
            <p className="text-2xl font-bold">{statistics.sayings_count}</p>
          </div>
          <div className="bg-purple-100 p-4 rounded">
            <p className="text-sm text-gray-600">الصور</p>
            <p className="text-2xl font-bold">{statistics.images_count}</p>
          </div>
          <div className="bg-pink-100 p-4 rounded">
            <p className="text-sm text-gray-600">الأنشطة</p>
            <p className="text-2xl font-bold">{statistics.activities_count}</p>
          </div>
          <div className="bg-gray-100 p-4 rounded">
            <p className="text-sm text-gray-600">الإجمالي</p>
            <p className="text-2xl font-bold">{statistics.total_count}</p>
          </div>
        </div>
      )}

      {/* News List */}
      <div className="space-y-4">
        {news.map(item => (
          <div key={`${item.type}-${item.id}`} className="bg-white p-6 rounded-lg shadow">
            {/* Type & Status Badges */}
            <div className="flex gap-2 mb-2">
              <span className={`inline-block px-3 py-1 rounded text-sm ${
                item.type === 'poem' ? 'bg-blue-100 text-blue-800' :
                item.type === 'lesson' ? 'bg-green-100 text-green-800' :
                item.type === 'saying' ? 'bg-yellow-100 text-yellow-800' :
                item.type === 'image' ? 'bg-purple-100 text-purple-800' :
                'bg-pink-100 text-pink-800'
              }`}>
                {item.type_ar}
              </span>

              <span className={`inline-block px-3 py-1 rounded text-sm ${
                item.status === 'new' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'
              }`}>
                {item.status_ar}
              </span>

              {item.is_private && (
                <span className="inline-block px-3 py-1 rounded text-sm bg-red-100 text-red-800">
                  🔒 خاص
                </span>
              )}
            </div>

            {/* Title */}
            <h2 className="text-xl font-bold mb-2">{item.title}</h2>

            {/* Description */}
            {item.description && (
              <p className="text-gray-600 mb-2">{item.description}</p>
            )}

            {/* Image */}
            {item.image_url && (
              <img src={item.image_url} alt={item.title} className="w-full h-48 object-cover rounded mb-2" />
            )}

            {/* Footer */}
            <div className="flex justify-between items-center text-sm text-gray-500">
              <span>👤 {item.author_name}</span>
              <div className="flex gap-4">
                <span>📅 {item.created_at_human}</span>
                {item.status === 'updated' && (
                  <span>🔄 {item.updated_at_human}</span>
                )}
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Pagination */}
      {meta && (
        <div className="mt-8 flex justify-between items-center">
          <p className="text-sm text-gray-600">
            عرض {meta.from} - {meta.to} من أصل {meta.total}
          </p>

          <div className="flex gap-2">
            <button
              onClick={() => setPage(page - 1)}
              disabled={page === 1}
              className="px-4 py-2 bg-blue-500 text-white rounded disabled:opacity-50"
            >
              ← السابق
            </button>

            <span className="px-4 py-2 bg-gray-100 rounded">
              {meta.current_page} / {meta.last_page}
            </span>

            <button
              onClick={() => setPage(page + 1)}
              disabled={page === meta.last_page}
              className="px-4 py-2 bg-blue-500 text-white rounded disabled:opacity-50"
            >
              التالي →
            </button>
          </div>
        </div>
      )}
    </div>
  )
}

export default NewsPage
```

---

## ✅ الخلاصة

- ✅ **لا يوجد جدول منفصل** - البيانات تُجلب من الجداول الأصلية
- ✅ **الإضافات + التعديلات** - يعرض كل ما هو جديد أو محدث
- ✅ **فلترة مرنة** - حسب النوع والفترة الزمنية
- ✅ **Pagination** - 20 عنصر/صفحة
- ✅ **حالة واضحة** - جديد أو محدث
- ✅ **خصوصية ذكية** - عام للزوار، عام+خاص للمسجلين

---

**API المستجدات جاهز! 🎉**
