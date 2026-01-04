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
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('project_number');
            $table->string('name');
            $table->string('status')->default(ProjectStatus::Active->value);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            
            $table->unique(['company_id', 'project_number']);
            $table->index('company_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
