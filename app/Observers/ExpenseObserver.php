<?php

namespace App\Observers;

use App\Models\Expense;
use App\Services\ExpenseService;

/**
 * ExpenseObserver - Observes Expense model events
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles expense observation logic
 * - Open/Closed: Can be extended without modification
 */
class ExpenseObserver
{
    protected ExpenseService $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    /**
     * Handle the Expense "creating" event
     */
    public function creating(Expense $expense): void
    {
        // Expense number will be generated in service layer
    }

    /**
     * Handle the Expense "created" event
     */
    public function created(Expense $expense): void
    {
        // Log expense creation or trigger notifications
    }

    /**
     * Handle the Expense "updating" event
     */
    public function updating(Expense $expense): void
    {
        // Validate status transitions
        $this->validateStatusTransition($expense);
    }

    /**
     * Handle the Expense "updated" event
     */
    public function updated(Expense $expense): void
    {
        // Trigger events based on status changes
        if ($expense->wasChanged('status')) {
            $this->handleStatusChange($expense);
        }
    }

    /**
     * Handle the Expense "deleting" event
     */
    public function deleting(Expense $expense): void
    {
        // Prevent deletion of approved or paid expenses
    }

    /**
     * Handle the Expense "deleted" event
     */
    public function deleted(Expense $expense): void
    {
        // Log deletion or notify relevant parties
    }

    /**
     * Validate status transition
     * 
     * @param Expense $expense
     * @return void
     */
    protected function validateStatusTransition(Expense $expense): void
    {
        // Early return if status didn't change
        if (!$expense->isDirty('status')) {
            return;
        }

        $oldStatus = $expense->getOriginal('status');
        $newStatus = $expense->status;

        // Add validation logic for valid status transitions
        // For example: Can't edit a paid expense
    }

    /**
     * Handle status change
     * 
     * @param Expense $expense
     * @return void
     */
    protected function handleStatusChange(Expense $expense): void
    {
        // Early return if no status change
        if (!$expense->wasChanged('status')) {
            return;
        }

        // Trigger notifications, events, etc. based on new status
        match ($expense->status) {
            \App\Enums\ExpenseStatus::Pending => $this->handlePending($expense),
            \App\Enums\ExpenseStatus::Approved => $this->handleApproved($expense),
            \App\Enums\ExpenseStatus::Rejected => $this->handleRejected($expense),
            \App\Enums\ExpenseStatus::Paid => $this->handlePaid($expense),
            default => null,
        };
    }

    /**
     * Handle expense pending approval
     */
    protected function handlePending(Expense $expense): void
    {
        // Send notification to approvers
    }

    /**
     * Handle expense approved
     */
    protected function handleApproved(Expense $expense): void
    {
        // Send approval notification
    }

    /**
     * Handle expense rejected
     */
    protected function handleRejected(Expense $expense): void
    {
        // Send rejection notification
    }

    /**
     * Handle expense paid
     */
    protected function handlePaid(Expense $expense): void
    {
        // Send payment confirmation
    }
}
