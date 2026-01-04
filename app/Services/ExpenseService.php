<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Company;
use App\Enums\ExpenseStatus;
use Illuminate\Support\Facades\DB;

/**
 * ExpenseService - Handles all business logic for expense operations
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles expense business logic
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on abstractions (interfaces/models)
 */
class ExpenseService
{
    /**
     * Create a new expense
     * 
     * @param Company $company
     * @param array $data
     * @return Expense
     */
    public function create(Company $company, array $data): Expense
    {
        return DB::transaction(function () use ($company, $data) {
            // Early return if no company
            if (!$company) {
                throw new \InvalidArgumentException('Company is required');
            }

            // Set company_id
            $data['company_id'] = $company->id;

            // Generate expense number if not provided
            if (!isset($data['expense_number'])) {
                $data['expense_number'] = $this->generateExpenseNumber($company);
            }

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = ExpenseStatus::Draft;
            }

            // Create expense
            $expense = Expense::create($data);

            return $expense->fresh();
        });
    }

    /**
     * Update an existing expense
     * 
     * @param Expense $expense
     * @param array $data
     * @return Expense
     */
    public function update(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data) {
            $expense->update($data);
            return $expense->fresh();
        });
    }

    /**
     * Submit expense for approval
     * 
     * @param Expense $expense
     * @return Expense
     */
    public function submit(Expense $expense): Expense
    {
        // Early return if already submitted
        if ($expense->status !== ExpenseStatus::Draft) {
            return $expense;
        }

        $expense->update([
            'status' => ExpenseStatus::Pending,
        ]);

        return $expense->fresh();
    }

    /**
     * Approve an expense
     * 
     * @param Expense $expense
     * @return Expense
     */
    public function approve(Expense $expense): Expense
    {
        // Early return if already approved
        if ($expense->status === ExpenseStatus::Approved) {
            return $expense;
        }

        $expense->update([
            'status' => ExpenseStatus::Approved,
        ]);

        return $expense->fresh();
    }

    /**
     * Reject an expense
     * 
     * @param Expense $expense
     * @return Expense
     */
    public function reject(Expense $expense): Expense
    {
        // Early return if already rejected
        if ($expense->status === ExpenseStatus::Rejected) {
            return $expense;
        }

        $expense->update([
            'status' => ExpenseStatus::Rejected,
        ]);

        return $expense->fresh();
    }

    /**
     * Mark expense as paid
     * 
     * @param Expense $expense
     * @return Expense
     */
    public function markAsPaid(Expense $expense): Expense
    {
        // Early return if already paid
        if ($expense->status === ExpenseStatus::Paid) {
            return $expense;
        }

        $expense->update([
            'status' => ExpenseStatus::Paid,
        ]);

        return $expense->fresh();
    }

    /**
     * Duplicate an expense
     * 
     * @param Expense $expense
     * @return Expense
     */
    public function duplicate(Expense $expense): Expense
    {
        return DB::transaction(function () use ($expense) {
            $newExpense = $expense->replicate();
            $newExpense->expense_number = $this->generateExpenseNumber($expense->company);
            $newExpense->status = ExpenseStatus::Draft;
            $newExpense->save();

            return $newExpense->fresh();
        });
    }

    /**
     * Generate unique expense number
     * 
     * @param Company $company
     * @return string
     */
    protected function generateExpenseNumber(Company $company): string
    {
        $prefix = 'EXP';
        $year = date('Y');
        $lastExpense = Expense::where('company_id', $company->id)
            ->where('expense_number', 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy('expense_number', 'desc')
            ->first();

        if (!$lastExpense) {
            $sequence = 1;
        } else {
            $lastNumber = intval(substr($lastExpense->expense_number, -6));
            $sequence = $lastNumber + 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }
}
