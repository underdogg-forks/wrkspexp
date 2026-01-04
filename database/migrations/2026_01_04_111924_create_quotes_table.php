<?php

use App\Enums\QuoteStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('quote_number')->unique();
            $table->dateTime('issued_at');
            $table->dateTime('expires_at')->nullable();
            $table->string('status')->default(QuoteStatus::Draft->value);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
