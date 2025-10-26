<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sayings', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['saying', 'supplication']); // saying = قول مأثور, supplication = ورد/ذكر
            $table->text('content'); // المحتوى الرئيسي
            $table->boolean('is_private')->default(false);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index('type');
            $table->index('is_private');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sayings');
    }
};
