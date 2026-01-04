<?php

use App\Enums\ProjectStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('project_number')->unique();
            $table->string('name');
            $table->string('status')->default(ProjectStatus::Active->value);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
