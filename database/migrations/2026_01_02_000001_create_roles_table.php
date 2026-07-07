<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();       // slug, e.g. super_admin, coordinator
            $table->string('label');                // display name
            $table->json('permissions')->nullable(); // ["*"] or ["students.view", ...]
            $table->boolean('is_staff')->default(true);  // can access the admin area
            $table->boolean('is_system')->default(false); // cannot be deleted / renamed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
