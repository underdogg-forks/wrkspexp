<?php

use App\Enums\ExpenseStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('relations')->cascadeOnDelete();
            $table->foreignId('expense_category_id')->constrained()->cascadeOnDelete();
            $table->string('expense_number')->unique();
            $table->string('title');
            $table->string('status')->default(ExpenseStatus::Draft->value);
            $table->decimal('amount', 10, 2);
            $table->dateTime('incurred_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
