<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('remindable_type');
            $table->unsignedBigInteger('remindable_id');
            $table->string('kind', 30);
            $table->date('sent_on');
            $table->timestamps();

            $table->unique(
                ['user_id', 'remindable_type', 'remindable_id', 'kind', 'sent_on'],
                'reminder_once_per_day'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
