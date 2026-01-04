<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communicatables', function (Blueprint $table) {
            $table->id();
            $table->morphs('communicatable');
            $table->string('type');
            $table->string('value');
            $table->boolean('is_primary')->default(false);
            
            $table->index(['communicatable_type', 'communicatable_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communicatables');
    }
};
