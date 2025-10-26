#!/bin/bash

# 🚀 أمثلة جاهزة للتنفيذ - Islamic Content System API
# استخدم هذا الملف لاختبار جميع APIs بسرعة

# ============================================================================
# إعدادات أساسية
# ============================================================================

BASE_URL="http://localhost:8000/api"
USER_TOKEN=""
ADMIN_TOKEN=""

# ألوان للعرض
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# دالة لطباعة العنوان
print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

# دالة لطباعة النجاح
print_success() {
    echo -e "${GREEN}✅ $1${NC}\n"
}

# دالة لطباعة الخطأ
print_error() {
    echo -e "${RED}❌ $1${NC}\n"
}

# دالة لطباعة معلومة
print_info() {
    echo -e "${YELLOW}ℹ️  $1${NC}\n"
}

# ============================================================================
# 1. المصادقة (Authentication)
# ============================================================================

test_authentication() {
    print_header "1. اختبار المصادقة"

    # 1.1 التسجيل
    print_info "1.1 تسجيل مستخدم جديد"
    REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/register" \
      -H "Content-Type: application/json" \
      -d '{
        "name": "أحمد محمد",
        "email": "ahmed'$(date +%s)'@test.com",
        "password": "password123",
        "password_confirmation": "password123"
      }')
    
    echo "$REGISTER_RESPONSE" | jq '.'
    USER_TOKEN=$(echo "$REGISTER_RESPONSE" | jq -r '.data.token')
    print_success "Token المستخدم: $USER_TOKEN"

    # 1.2 تسجيل الدخول كـ Admin
    print_info "1.2 تسجيل الدخول كـ Admin"
    LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
      -H "Content-Type: application/json" \
      -d '{
        "email": "admin@test.com",
        "password": "password"
      }')
    
    echo "$LOGIN_RESPONSE" | jq '.'
    ADMIN_TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token')
    print_success "Token الأدمن: $ADMIN_TOKEN"

    # 1.3 الحصول على بيانات المستخدم
    print_info "1.3 الحصول على بيانات المستخدم"
    curl -s -X GET "$BASE_URL/user" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'
}

# ============================================================================
# 2. القصائد - عرض وبحث
# ============================================================================

test_poems_viewing() {
    print_header "2. القصائد - عرض وبحث"

    # 2.1 عرض جميع القصائد بدون Token
    print_info "2.1 عرض القصائد بدون Token (عامة فقط)"
    curl -s -X GET "$BASE_URL/poems/getall?page=1" | jq '.'

    # 2.2 عرض جميع القصائد مع Token
    print_info "2.2 عرض القصائد مع Token (الكل)"
    curl -s -X GET "$BASE_URL/poems/getall?page=1" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'

    # 2.3 البحث بالكلمة المفتاحية
    print_info "2.3 البحث بكلمة 'محمد'"
    curl -s -X GET "$BASE_URL/poems/search?keyword=محمد" | jq '.'

    # 2.4 البحث حسب السنة
    print_info "2.4 البحث حسب سنة 2024"
    curl -s -X GET "$BASE_URL/poems/search?year=2024" | jq '.'

    # 2.5 البحث حسب نوع المصدر
    print_info "2.5 البحث عن قصائد بها ملفات صوتية"
    curl -s -X GET "$BASE_URL/poems/search?source_type=audio" | jq '.'

    # 2.6 البحث المركب
    print_info "2.6 بحث مركب (كلمة + سنة + نوع مصدر)"
    curl -s -X GET "$BASE_URL/poems/search?keyword=البردة&year=2024&source_type=pdf" | jq '.'

    # 2.7 عرض تفاصيل قصيدة
    print_info "2.7 عرض تفاصيل قصيدة رقم 1"
    curl -s -X GET "$BASE_URL/poems/1" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'
}

# ============================================================================
# 3. القصائد - إدارة (CRUD)
# ============================================================================

test_poems_management() {
    print_header "3. القصائد - إدارة"

    # 3.1 إضافة قصيدة (Admin)
    print_info "3.1 إضافة قصيدة جديدة (Admin)"
    ADD_RESPONSE=$(curl -s -X POST "$BASE_URL/AddPoem" \
      -H "Authorization: Bearer $ADMIN_TOKEN" \
      -F "title=قصيدة تجريبية $(date +%s)" \
      -F "description=هذه قصيدة للاختبار" \
      -F "saying_date=2024-03-15" \
      -F "is_private=false")
    
    echo "$ADD_RESPONSE" | jq '.'
    NEW_POEM_ID=$(echo "$ADD_RESPONSE" | jq -r '.data.poem.id')
    print_success "تم إنشاء قصيدة برقم: $NEW_POEM_ID"

    # 3.2 تحديث القصيدة
    print_info "3.2 تحديث القصيدة"
    curl -s -X POST "$BASE_URL/poems/$NEW_POEM_ID/update" \
      -H "Authorization: Bearer $ADMIN_TOKEN" \
      -F "title=قصيدة محدثة $(date +%s)" \
      -F "description=وصف محدث" \
      -F "saying_date=2024-03-20" | jq '.'

    # 3.3 حذف القصيدة
    print_info "3.3 حذف القصيدة"
    curl -s -X DELETE "$BASE_URL/deletePoem/$NEW_POEM_ID" \
      -H "Authorization: Bearer $ADMIN_TOKEN" | jq '.'
}

# ============================================================================
# 4. المفضلة
# ============================================================================

test_favorites() {
    print_header "4. المفضلة"

    # 4.1 إضافة قصيدة للمفضلة
    print_info "4.1 إضافة قصيدة 1 للمفضلة"
    curl -s -X POST "$BASE_URL/FavoritePoem/1" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'

    # 4.2 إضافة قصيدة أخرى
    print_info "4.2 إضافة قصيدة 2 للمفضلة"
    curl -s -X POST "$BASE_URL/FavoritePoem/2" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'

    # 4.3 عرض المفضلة
    print_info "4.3 عرض القصائد المفضلة"
    curl -s -X GET "$BASE_URL/poems/favorites" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'

    # 4.4 إزالة من المفضلة
    print_info "4.4 إزالة قصيدة 1 من المفضلة"
    curl -s -X POST "$BASE_URL/FavoritePoem/1" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'

    # 4.5 عرض المفضلة مرة أخرى
    print_info "4.5 عرض المفضلة بعد الإزالة"
    curl -s -X GET "$BASE_URL/poems/favorites" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'
}

# ============================================================================
# 5. التعليقات
# ============================================================================

test_comments() {
    print_header "5. التعليقات"

    # 5.1 عرض تعليقات قصيدة
    print_info "5.1 عرض تعليقات قصيدة 1"
    curl -s -X GET "$BASE_URL/poems/1/comments" | jq '.'

    # 5.2 إضافة تعليق
    print_info "5.2 إضافة تعليق جديد"
    ADD_COMMENT=$(curl -s -X POST "$BASE_URL/poems/1/comments" \
      -H "Authorization: Bearer $USER_TOKEN" \
      -H "Content-Type: application/json" \
      -d '{"content": "قصيدة رائعة جداً، بارك الله فيكم"}')
    
    echo "$ADD_COMMENT" | jq '.'
    COMMENT_ID=$(echo "$ADD_COMMENT" | jq -r '.data.comment.id')
    print_success "تم إضافة تعليق برقم: $COMMENT_ID"

    # 5.3 تحديث التعليق
    print_info "5.3 تحديث التعليق"
    curl -s -X PUT "$BASE_URL/poems/comments/$COMMENT_ID" \
      -H "Authorization: Bearer $USER_TOKEN" \
      -H "Content-Type: application/json" \
      -d '{"content": "تعليق محدث - ما شاء الله"}' | jq '.'

    # 5.4 عرض التعليقات مرة أخرى
    print_info "5.4 عرض التعليقات بعد التحديث"
    curl -s -X GET "$BASE_URL/poems/1/comments" | jq '.'

    # 5.5 حذف التعليق
    print_info "5.5 حذف التعليق"
    curl -s -X DELETE "$BASE_URL/poems/comments/$COMMENT_ID" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'
}

# ============================================================================
# 6. الدروس
# ============================================================================

test_lessons() {
    print_header "6. الدروس"

    # 6.1 عرض جميع الدروس
    print_info "6.1 عرض جميع الدروس"
    curl -s -X GET "$BASE_URL/lessons/getall?page=1" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'

    # 6.2 البحث في الدروس
    print_info "6.2 البحث في الدروس"
    curl -s -X GET "$BASE_URL/lessons/search?keyword=فقه" | jq '.'

    # 6.3 عرض تفاصيل درس
    print_info "6.3 عرض تفاصيل درس"
    curl -s -X GET "$BASE_URL/lessons/1" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'

    # 6.4 إضافة للمفضلة
    print_info "6.4 إضافة درس للمفضلة"
    curl -s -X POST "$BASE_URL/FavoriteLesson/1" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'

    # 6.5 عرض الدروس المفضلة
    print_info "6.5 عرض الدروس المفضلة"
    curl -s -X GET "$BASE_URL/lessons/favorites" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'
}

# ============================================================================
# 7. الأقوال والأذكار
# ============================================================================

test_sayings() {
    print_header "7. الأقوال والأذكار"

    # 7.1 عرض جميع الأقوال
    print_info "7.1 عرض جميع الأقوال"
    curl -s -X GET "$BASE_URL/sayings/getall" | jq '.'

    # 7.2 عرض الأقوال المأثورة فقط
    print_info "7.2 عرض الأقوال المأثورة فقط"
    curl -s -X GET "$BASE_URL/sayings/getall?type=saying" | jq '.'

    # 7.3 عرض الأذكار فقط
    print_info "7.3 عرض الأذكار فقط"
    curl -s -X GET "$BASE_URL/sayings/getall?type=supplication" | jq '.'

    # 7.4 البحث في الأقوال
    print_info "7.4 البحث عن 'الصلاة'"
    curl -s -X GET "$BASE_URL/sayings/search?keyword=الصلاة" | jq '.'

    # 7.5 إضافة قول جديد (Admin)
    print_info "7.5 إضافة قول جديد"
    ADD_SAYING=$(curl -s -X POST "$BASE_URL/AddSaying" \
      -H "Authorization: Bearer $ADMIN_TOKEN" \
      -H "Content-Type: application/json" \
      -d '{
        "content": "إنما الأعمال بالنيات",
        "type": "saying",
        "source": "صحيح البخاري"
      }')
    
    echo "$ADD_SAYING" | jq '.'
    SAYING_ID=$(echo "$ADD_SAYING" | jq -r '.data.saying.id')
    print_success "تم إضافة قول برقم: $SAYING_ID"

    # 7.6 إضافة للمفضلة
    print_info "7.6 إضافة القول للمفضلة"
    curl -s -X POST "$BASE_URL/FavoriteSaying/$SAYING_ID" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'

    # 7.7 عرض الأقوال المفضلة
    print_info "7.7 عرض الأقوال المفضلة"
    curl -s -X GET "$BASE_URL/sayings/favorites" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'
}

# ============================================================================
# 8. المشاركات
# ============================================================================

test_posts() {
    print_header "8. المشاركات"

    # 8.1 إنشاء مشاركة
    print_info "8.1 إنشاء مشاركة جديدة"
    CREATE_POST=$(curl -s -X POST "$BASE_URL/posts" \
      -H "Authorization: Bearer $USER_TOKEN" \
      -H "Content-Type: application/json" \
      -d '{
        "title": "مشاركتي الأولى",
        "content": "هذا محتوى المشاركة الطويل الذي يحتوي على معلومات مفيدة..."
      }')
    
    echo "$CREATE_POST" | jq '.'
    POST_ID=$(echo "$CREATE_POST" | jq -r '.data.post.id')
    print_success "تم إنشاء مشاركة برقم: $POST_ID (Status: pending)"

    # 8.2 عرض المشاركات العامة
    print_info "8.2 عرض المشاركات المعتمدة (لا تظهر pending)"
    curl -s -X GET "$BASE_URL/posts" | jq '.'

    # 8.3 عرض مشاركاتي
    print_info "8.3 عرض مشاركاتي (تظهر pending)"
    curl -s -X GET "$BASE_URL/posts/my-posts" \
      -H "Authorization: Bearer $USER_TOKEN" | jq '.'

    # 8.4 عرض المشاركات المعلقة (Admin)
    print_info "8.4 عرض المشاركات المعلقة (Admin)"
    curl -s -X GET "$BASE_URL/posts/pending" \
      -H "Authorization: Bearer $ADMIN_TOKEN" | jq '.'

    # 8.5 الموافقة على المشاركة (Admin)
    print_info "8.5 الموافقة على المشاركة"
    curl -s -X POST "$BASE_URL/posts/$POST_ID/approve" \
      -H "Authorization: Bearer $ADMIN_TOKEN" | jq '.'

    # 8.6 عرض المشاركات العامة مرة أخرى
    print_info "8.6 عرض المشاركات (تظهر الآن)"
    curl -s -X GET "$BASE_URL/posts" | jq '.'

    # 8.7 البحث في المشاركات
    print_info "8.7 البحث في المشاركات"
    curl -s -X GET "$BASE_URL/posts/search?keyword=مشاركتي" | jq '.'
}

# ============================================================================
# 9. اختبار الصلاحيات
# ============================================================================

test_permissions() {
    print_header "9. اختبار الصلاحيات"

    # 9.1 User يحاول إضافة قصيدة (فشل)
    print_info "9.1 User يحاول إضافة قصيدة (يجب أن يفشل)"
    curl -s -X POST "$BASE_URL/AddPoem" \
      -H "Authorization: Bearer $USER_TOKEN" \
      -F "title=محاولة فاشلة" | jq '.'

    # 9.2 Admin يضيف قصيدة (نجاح)
    print_info "9.2 Admin يضيف قصيدة (يجب أن ينجح)"
    curl -s -X POST "$BASE_URL/AddPoem" \
      -H "Authorization: Bearer $ADMIN_TOKEN" \
      -F "title=قصيدة من الأدمن $(date +%s)" \
      -F "description=نجحت" | jq '.'

    # 9.3 محاولة الوصول بدون Token
    print_info "9.3 محاولة الوصول للمفضلة بدون Token (فشل)"
    curl -s -X GET "$BASE_URL/poems/favorites" | jq '.'
}

# ============================================================================
# 10. اختبار Pagination
# ============================================================================

test_pagination() {
    print_header "10. اختبار Pagination"

    # 10.1 الصفحة الأولى
    print_info "10.1 الصفحة الأولى"
    curl -s -X GET "$BASE_URL/poems/getall?page=1" | jq '.meta'

    # 10.2 الصفحة الثانية
    print_info "10.2 الصفحة الثانية"
    curl -s -X GET "$BASE_URL/poems/getall?page=2" | jq '.meta'

    # 10.3 صفحة غير موجودة
    print_info "10.3 صفحة غير موجودة (999)"
    curl -s -X GET "$BASE_URL/poems/getall?page=999" | jq '.'
}

# ============================================================================
# القائمة الرئيسية
# ============================================================================

show_menu() {
    echo -e "\n${BLUE}╔════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║   اختبار نظام المحتوى الإسلامي       ║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════╝${NC}\n"
    echo "1.  اختبار المصادقة"
    echo "2.  اختبار القصائد - عرض وبحث"
    echo "3.  اختبار القصائد - إدارة"
    echo "4.  اختبار المفضلة"
    echo "5.  اختبار التعليقات"
    echo "6.  اختبار الدروس"
    echo "7.  اختبار الأقوال والأذكار"
    echo "8.  اختبار المشاركات"
    echo "9.  اختبار الصلاحيات"
    echo "10. اختبار Pagination"
    echo "11. تشغيل جميع الاختبارات"
    echo "0.  خروج"
    echo ""
}

# ============================================================================
# تشغيل البرنامج
# ============================================================================

main() {
    while true; do
        show_menu
        read -p "اختر رقم الاختبار: " choice
        
        case $choice in
            1) test_authentication ;;
            2) test_poems_viewing ;;
            3) test_poems_management ;;
            4) test_favorites ;;
            5) test_comments ;;
            6) test_lessons ;;
            7) test_sayings ;;
            8) test_posts ;;
            9) test_permissions ;;
            10) test_pagination ;;
            11)
                test_authentication
                test_poems_viewing
                test_poems_management
                test_favorites
                test_comments
                test_lessons
                test_sayings
                test_posts
                test_permissions
                test_pagination
                print_success "اكتملت جميع الاختبارات!"
                ;;
            0)
                print_info "وداعاً!"
                exit 0
                ;;
            *)
                print_error "اختيار غير صحيح!"
                ;;
        esac
        
        read -p "اضغط Enter للمتابعة..."
    done
}

# تشغيل البرنامج
main
