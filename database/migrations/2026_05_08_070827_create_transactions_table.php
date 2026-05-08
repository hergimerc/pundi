<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('note_id')->nullable()->constrained('notes')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->datetime('transacted_at');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['account_id', 'transacted_at']);
            $table->index(['category_id', 'transacted_at']);
            $table->index(['type', 'transacted_at']);
            $table->index('transacted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
