<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->foreignId('category_id')->constrained('financial_categories')->cascadeOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained('financial_expenses')->nullOnDelete();
            $table->foreignId('income_id')->nullable()->constrained('financial_incomes')->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('amount', 10, 2);
            $table->string('status');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('description')->nullable();
            $table->string('payment_method')->nullable();
            $table->date('due_date')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamp('due_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type', 'year', 'month', 'expense_id'], 'fin_trans_expense_unique');
            $table->unique(['user_id', 'type', 'year', 'month', 'income_id'], 'fin_trans_income_unique');
            $table->index(['user_id', 'year', 'month']);
            $table->index(['user_id', 'type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};