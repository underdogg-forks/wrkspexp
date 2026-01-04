<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->morphs('notable');
            $table->string('type'); // 'note' or 'description'
            $table->text('content');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            
            $table->index(['notable_type', 'notable_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
