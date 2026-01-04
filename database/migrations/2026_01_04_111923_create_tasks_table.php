<?php

use App\Enums\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('task_number')->unique();
            $table->string('title');
            $table->string('status')->default(TaskStatus::Pending->value);
            $table->integer('estimated_hours')->nullable();
            $table->dateTime('due_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
