<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // start & end timer
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();

            // durasi dalam menit (disimpan setelah stop)
            $table->integer('duration_minutes')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
