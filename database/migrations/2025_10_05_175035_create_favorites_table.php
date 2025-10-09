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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
             // المفتاح الأول: لربط السجل بالمستخدم (من جدول users)
            $table->foreignId('user_id')
                  ->constrained() // يفترض أن اسم الجدول هو users
                  ->onDelete('cascade'); // إذا حُذف المستخدم، تُحذف تفضيلاته
            
            // المفتاح الثاني: لربط السجل بالقصيدة (من جدول poems)
            $table->foreignId('poem_id')
                  ->constrained() // يفترض أن اسم الجدول هو poems
                  ->onDelete('cascade'); // إذا حُذفت القصيدة، يُحذف سجل التفضيل
            
            // منع تكرار التفضيل: لا يمكن للمستخدم تفضيل نفس القصيدة مرتين
            $table->unique(['user_id', 'poem_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
