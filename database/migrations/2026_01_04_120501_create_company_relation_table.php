<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_relation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('relation_id')->constrained('relations')->cascadeOnDelete();
            $table->string('relation_type');
            
            $table->unique(['company_id', 'relation_id', 'relation_type'], 'company_relation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_relation');
    }
};
