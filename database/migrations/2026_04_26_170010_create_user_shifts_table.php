<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('tanggal');
            $table->timestamps();

            $table->unique(['user_id', 'tanggal']);
            $table->index(['shift_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_shifts');
    }
};

