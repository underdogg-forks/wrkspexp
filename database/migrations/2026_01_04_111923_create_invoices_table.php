<?php

use App\Enums\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->dateTime('issued_at');
            $table->dateTime('due_at')->nullable();
            $table->string('status')->default(InvoiceStatus::Draft->value);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            
            $table->index('company_id');
            $table->index('issued_at');
            $table->index('due_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
