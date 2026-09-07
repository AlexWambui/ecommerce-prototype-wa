<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Concerns\HasUuid;

class Expense extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'recurrence_end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Expense categories constants
    const CATEGORY_RENT = 'rent';
    const CATEGORY_UTILITIES = 'utilities';
    const CATEGORY_SALARIES = 'salaries';
    const CATEGORY_MARKETING = 'marketing';
    const CATEGORY_SUPPLIES = 'supplies';
    const CATEGORY_MAINTENANCE = 'maintenance';
    const CATEGORY_TRANSPORT = 'transport';
    const CATEGORY_INSURANCE = 'insurance';
    const CATEGORY_TAXES = 'taxes';
    const CATEGORY_SOFTWARE = 'software';
    const CATEGORY_OTHER = 'other';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID = 'paid';
    const STATUS_REJECTED = 'rejected';

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    // Helper methods
    public static function getTotalExpenses($startDate, $endDate): float
    {
        return static::whereBetween('expense_date', [$startDate, $endDate])
            ->where('status', self::STATUS_PAID)
            ->sum('amount');
    }

    public static function getExpensesByCategory($startDate, $endDate): array
    {
        return static::whereBetween('expense_date', [$startDate, $endDate])
            ->where('status', self::STATUS_PAID)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();
    }

    public static function getMonthlyExpenses($year): array
    {
        $monthly = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthly[] = static::whereYear('expense_date', $year)
                ->whereMonth('expense_date', $i)
                ->where('status', self::STATUS_PAID)
                ->sum('amount');
        }
        return $monthly;
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Ksh ' . number_format($this->amount, 2);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_APPROVED => 'blue',
            self::STATUS_PAID => 'green',
            self::STATUS_REJECTED => 'red',
            default => 'gray',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            self::CATEGORY_RENT => 'Rent',
            self::CATEGORY_UTILITIES => 'Utilities',
            self::CATEGORY_SALARIES => 'Salaries',
            self::CATEGORY_MARKETING => 'Marketing',
            self::CATEGORY_SUPPLIES => 'Office Supplies',
            self::CATEGORY_MAINTENANCE => 'Maintenance',
            self::CATEGORY_TRANSPORT => 'Transport',
            self::CATEGORY_INSURANCE => 'Insurance',
            self::CATEGORY_TAXES => 'Taxes',
            self::CATEGORY_SOFTWARE => 'Software',
            default => 'Other',
        };
    }
}