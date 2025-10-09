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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poem_id')->constrained()->onDelete('cascade');
    // المفتاح الخارجي للمستخدم: لربط التعليق بالكاتب
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    // محتوى التعليق
    $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
