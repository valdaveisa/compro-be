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
        Schema::table('users', function (Blueprint $table) {
            // changing enum to string to support more roles
            // doing raw statement to avoid doctrine requirement issues with enum
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) DEFAULT 'member'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         // We won't strictly revert to enum to avoid data loss if 'pm' exists, 
         // but ideally we would. For now, leaving as string is safer.
         // Or revert to default string.
    }
};
