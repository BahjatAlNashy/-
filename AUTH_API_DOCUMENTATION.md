# 🔐 نظام المصادقة (Authentication System)

نظام تسجيل حساب وتسجيل دخول كامل بحسابات حقيقية (Email/Password).

---

## 🎯 المميزات

- ✅ **تسجيل حساب جديد** - بـ Email و Password
- ✅ **تسجيل الدخول** - مع Token للمصادقة
- ✅ **تسجيل الخروج** - حذف Token
- ✅ **عرض الملف الشخصي** - معلومات المستخدم
- ✅ **تحديث الملف الشخصي** - الاسم، Email، Password
- ✅ **رسائل خطأ بالعربية** - واضحة ومفهومة
- ✅ **Validation كامل** - للبيانات المدخلة

---

## 📊 Endpoints

### 1. تسجيل حساب جديد (Register)

```
POST /api/register
```

#### Request Body:

```json
{
  "name": "أحمد محمد",
  "email": "ahmed@example.com",
  "password": "12345678",
  "password_confirmation": "12345678"
}
```

#### Response (Success - 201):

```json
{
  "success": true,
  "message": "تم إنشاء الحساب بنجاح",
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "email": "ahmed@example.com",
      "role": "user",
      "created_at": "2025-10-21 20:00:00"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz...",
    "token_type": "Bearer"
  }
}
```

#### Response (Error - 422):

```json
{
  "success": false,
  "message": "خطأ في البيانات المدخلة",
  "errors": {
    "email": ["البريد الإلكتروني مستخدم بالفعل"],
    "password": ["كلمة المرور يجب أن تكون 8 أحرف على الأقل"]
  }
}
```

---

### 2. تسجيل الدخول (Login)

```
POST /api/login
```

#### Request Body:

```json
{
  "email": "ahmed@example.com",
  "password": "12345678"
}
```

#### Response (Success - 200):

```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "email": "ahmed@example.com",
      "role": "user",
      "created_at": "2025-10-21 20:00:00"
    },
    "token": "2|zyxwvutsrqponmlkjihgfedcba...",
    "token_type": "Bearer"
  }
}
```

#### Response (Error - 401):

```json
{
  "success": false,
  "message": "البريد الإلكتروني أو كلمة المرور غير صحيحة"
}
```

---

### 3. تسجيل الخروج (Logout)

```
POST /api/logout
```

#### Headers:

```
Authorization: Bearer {token}
```

#### Response (Success - 200):

```json
{
  "success": true,
  "message": "تم تسجيل الخروج بنجاح"
}
```

---

### 4. عرض الملف الشخصي (Profile)

```
GET /api/profile
```

#### Headers:

```
Authorization: Bearer {token}
```

#### Response (Success - 200):

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "role": "user",
    "created_at": "2025-10-21 20:00:00",
    "updated_at": "2025-10-21 20:00:00"
  }
}
```

---

### 5. تحديث الملف الشخصي (Update Profile)

```
PUT /api/profile
```

#### Headers:

```
Authorization: Bearer {token}
```

#### Request Body (جميع الحقول اختيارية):

```json
{
  "name": "أحمد علي",
  "email": "ahmed.ali@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

#### Response (Success - 200):

```json
{
  "success": true,
  "message": "تم تحديث المعلومات بنجاح",
  "data": {
    "id": 1,
    "name": "أحمد علي",
    "email": "ahmed.ali@example.com",
    "role": "user"
  }
}
```

---

## 🧪 أمثلة الاستخدام

### مثال 1: تسجيل حساب جديد

**Request:**
```bash
POST http://localhost:8000/api/register
Content-Type: application/json

{
  "name": "محمد أحمد",
  "email": "mohammed@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
- حساب جديد
- Token للمصادقة
- معلومات المستخدم

---

### مثال 2: تسجيل الدخول

**Request:**
```bash
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "mohammed@example.com",
  "password": "password123"
}
```

**Response:**
- Token جديد
- معلومات المستخدم

---

### مثال 3: عرض الملف الشخصي

**Request:**
```bash
GET http://localhost:8000/api/profile
Authorization: Bearer YOUR_TOKEN
```

**Response:**
- جميع معلومات المستخدم

---

### مثال 4: تحديث الاسم فقط

**Request:**
```bash
PUT http://localhost:8000/api/profile
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "name": "محمد علي"
}
```

**Response:**
- معلومات محدثة

---

### مثال 5: تغيير كلمة المرور

**Request:**
```bash
PUT http://localhost:8000/api/profile
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "password": "newpassword456",
  "password_confirmation": "newpassword456"
}
```

**Response:**
- كلمة مرور محدثة

---

### مثال 6: تسجيل الخروج

**Request:**
```bash
POST http://localhost:8000/api/logout
Authorization: Bearer YOUR_TOKEN
```

**Response:**
- تم حذف Token

---

## 💻 استخدام في React

### 1. تسجيل حساب جديد

```jsx
import { useState } from 'react'
import axios from 'axios'

function RegisterPage() {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  })
  const [errors, setErrors] = useState({})

  const handleRegister = async (e) => {
    e.preventDefault()
    setErrors({})

    try {
      const response = await axios.post('http://localhost:8000/api/register', formData)
      
      if (response.data.success) {
        // حفظ Token
        localStorage.setItem('token', response.data.data.token)
        localStorage.setItem('user', JSON.stringify(response.data.data.user))
        
        alert('تم إنشاء الحساب بنجاح!')
        // الانتقال للصفحة الرئيسية
        window.location.href = '/'
      }
    } catch (error) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors)
      } else {
        alert(error.response?.data?.message || 'حدث خطأ')
      }
    }
  }

  return (
    <div className="max-w-md mx-auto p-6">
      <h1 className="text-2xl font-bold mb-6">إنشاء حساب جديد</h1>
      
      <form onSubmit={handleRegister} className="space-y-4">
        {/* Name */}
        <div>
          <label className="block mb-2">الاسم</label>
          <input
            type="text"
            value={formData.name}
            onChange={(e) => setFormData({...formData, name: e.target.value})}
            className="w-full p-2 border rounded"
          />
          {errors.name && <p className="text-red-500 text-sm mt-1">{errors.name[0]}</p>}
        </div>

        {/* Email */}
        <div>
          <label className="block mb-2">البريد الإلكتروني</label>
          <input
            type="email"
            value={formData.email}
            onChange={(e) => setFormData({...formData, email: e.target.value})}
            className="w-full p-2 border rounded"
          />
          {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email[0]}</p>}
        </div>

        {/* Password */}
        <div>
          <label className="block mb-2">كلمة المرور</label>
          <input
            type="password"
            value={formData.password}
            onChange={(e) => setFormData({...formData, password: e.target.value})}
            className="w-full p-2 border rounded"
          />
          {errors.password && <p className="text-red-500 text-sm mt-1">{errors.password[0]}</p>}
        </div>

        {/* Password Confirmation */}
        <div>
          <label className="block mb-2">تأكيد كلمة المرور</label>
          <input
            type="password"
            value={formData.password_confirmation}
            onChange={(e) => setFormData({...formData, password_confirmation: e.target.value})}
            className="w-full p-2 border rounded"
          />
        </div>

        <button 
          type="submit"
          className="w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700"
        >
          إنشاء حساب
        </button>
      </form>
    </div>
  )
}

export default RegisterPage
```

---

### 2. تسجيل الدخول

```jsx
import { useState } from 'react'
import axios from 'axios'

function LoginPage() {
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  })
  const [error, setError] = useState('')

  const handleLogin = async (e) => {
    e.preventDefault()
    setError('')

    try {
      const response = await axios.post('http://localhost:8000/api/login', formData)
      
      if (response.data.success) {
        // حفظ Token
        localStorage.setItem('token', response.data.data.token)
        localStorage.setItem('user', JSON.stringify(response.data.data.user))
        
        alert('تم تسجيل الدخول بنجاح!')
        window.location.href = '/'
      }
    } catch (error) {
      setError(error.response?.data?.message || 'حدث خطأ')
    }
  }

  return (
    <div className="max-w-md mx-auto p-6">
      <h1 className="text-2xl font-bold mb-6">تسجيل الدخول</h1>
      
      {error && (
        <div className="bg-red-100 text-red-700 p-3 rounded mb-4">
          {error}
        </div>
      )}

      <form onSubmit={handleLogin} className="space-y-4">
        <div>
          <label className="block mb-2">البريد الإلكتروني</label>
          <input
            type="email"
            value={formData.email}
            onChange={(e) => setFormData({...formData, email: e.target.value})}
            className="w-full p-2 border rounded"
            required
          />
        </div>

        <div>
          <label className="block mb-2">كلمة المرور</label>
          <input
            type="password"
            value={formData.password}
            onChange={(e) => setFormData({...formData, password: e.target.value})}
            className="w-full p-2 border rounded"
            required
          />
        </div>

        <button 
          type="submit"
          className="w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700"
        >
          دخول
        </button>
      </form>
    </div>
  )
}

export default LoginPage
```

---

### 3. Axios Interceptor (لإضافة Token تلقائياً)

```jsx
// src/api/axios.js
import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api'
})

// إضافة Token تلقائياً لكل request
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// معالجة 401 (Unauthorized)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // حذف Token وإعادة التوجيه لصفحة الدخول
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
```

---

### 4. استخدام API مع Interceptor

```jsx
import api from './api/axios'

// تسجيل الخروج
const handleLogout = async () => {
  try {
    await api.post('/logout')
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    window.location.href = '/login'
  } catch (error) {
    console.error(error)
  }
}

// عرض الملف الشخصي
const fetchProfile = async () => {
  try {
    const response = await api.get('/profile')
    console.log(response.data.data)
  } catch (error) {
    console.error(error)
  }
}

// تحديث الملف الشخصي
const updateProfile = async (data) => {
  try {
    const response = await api.put('/profile', data)
    alert(response.data.message)
  } catch (error) {
    console.error(error)
  }
}
```

---

## 🔒 الأمان

### 1. Password Hashing
- ✅ كلمات المرور مشفرة بـ `bcrypt`
- ✅ لا يتم تخزين كلمات المرور الأصلية

### 2. Token Authentication
- ✅ Laravel Sanctum
- ✅ Token فريد لكل جلسة
- ✅ يمكن حذف Token عند الخروج

### 3. Validation
- ✅ Email يجب أن يكون فريد
- ✅ Password 8 أحرف على الأقل
- ✅ Password Confirmation

---

## ✅ الخلاصة

- ✅ **نظام كامل** - تسجيل، دخول، خروج، ملف شخصي
- ✅ **حسابات حقيقية** - Email/Password
- ✅ **آمن** - Password hashing + Token authentication
- ✅ **رسائل عربية** - واضحة ومفهومة
- ✅ **جاهز للاستخدام** - مع أمثلة React

---

**نظام المصادقة جاهز! 🎉**
