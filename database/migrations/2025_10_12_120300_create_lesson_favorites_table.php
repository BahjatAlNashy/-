<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lesson_favorites', function (Blueprint $table) {
            $table->id();
            // المفتاح الأول: لربط السجل بالمستخدم (من جدول users)
            $table->foreignId('user_id')
                  ->constrained() // يفترض أن اسم الجدول هو users
                  ->onDelete('cascade'); // إذا حُذف المستخدم، تُحذف تفضيلاته
            
            // المفتاح الثاني: لربط السجل بالدرس (من جدول lessons)
            $table->foreignId('lesson_id')
                  ->constrained() // يفترض أن اسم الجدول هو lessons
                  ->onDelete('cascade'); // إذا حُذف الدرس، يُحذف سجل التفضيل
            
            // منع تكرار التفضيل: لا يمكن للمستخدم تفضيل نفس الدرس مرتين
            $table->unique(['user_id', 'lesson_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_favorites');
    }
};
