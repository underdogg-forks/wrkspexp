<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relation_id')->constrained('relations')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('client_number')->unique();
            
            $table->unique(['relation_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
