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
        // Add Role to Users
        if (!Schema::hasColumn('users', 'role')) {
             Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['user', 'admin'])->default('user')->after('password');
            });
        }

        // Add Progress to Tasks
        if (!Schema::hasColumn('tasks', 'progress')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->integer('progress')->default(0)->after('priority'); 
            });
        }

        // Create Activity Logs Table
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subject_type')->nullable(); // Polymorphic-ish
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('action'); // e.g. "created task", "updated status"
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('progress');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
