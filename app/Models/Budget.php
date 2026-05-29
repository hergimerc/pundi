<?php

namespace App\Models;

use App\Enums\TransactionType;
use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    /** @use HasFactory<BudgetFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'amount',
        'month',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'month' => 'integer',
            'year' => 'integer',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'budget_category');
    }

    public function spentAmount(): float
    {
        $categoryIds = [];

        foreach ($this->categories as $category) {
            $categoryIds[] = $category->id;
            array_push($categoryIds, ...$category->allDescendantIds());
        }

        if (empty($categoryIds)) {
            return 0.0;
        }

        return (float) Transaction::query()
            ->where('type', TransactionType::Expense)
            ->whereIn('category_id', array_unique($categoryIds))
            ->whereYear('transacted_at', $this->year)
            ->whereMonth('transacted_at', $this->month)
            ->sum('amount');
    }

    public function remainingAmount(): float
    {
        return (float) $this->amount - $this->spentAmount();
    }

    public function progressPercent(): float
    {
        if ((float) $this->amount <= 0) {
            return 0.0;
        }

        return min(100.0, ($this->spentAmount() / (float) $this->amount) * 100);
    }
}
