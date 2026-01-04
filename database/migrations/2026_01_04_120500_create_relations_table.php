<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relations', function (Blueprint $table) {
            $table->id();
            $table->string('relation_number')->unique();
            $table->string('name');
            $table->string('slug');
            $table->string('tax_id');
            $table->string('tax_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relations');
    }
};
