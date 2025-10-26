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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // عنوان الصورة
            $table->text('description')->nullable(); // وصف الصورة
            $table->string('image_path'); // مسار الصورة
            $table->string('image_url'); // رابط الصورة الكامل
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // من رفعها
            $table->boolean('is_private')->default(false); // خاصة أم عامة
            $table->timestamps();
        });

        // جدول المفضلة للصور
        Schema::create('favorite_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('image_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'image_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_images');
        Schema::dropIfExists('images');
    }
};
