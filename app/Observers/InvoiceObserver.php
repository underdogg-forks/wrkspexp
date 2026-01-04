<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\InvoiceService;

/**
 * InvoiceObserver - Observes Invoice model events
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles invoice observation logic
 * - Open/Closed: Can be extended without modification
 */
class InvoiceObserver
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Handle the Invoice "creating" event
     */
    public function creating(Invoice $invoice): void
    {
        // Generate invoice number if not set
        if (!$invoice->invoice_number && $invoice->company) {
            // Invoice number will be generated in service layer
        }
    }

    /**
     * Handle the Invoice "created" event
     */
    public function created(Invoice $invoice): void
    {
        // Log invoice creation or trigger notifications
    }

    /**
     * Handle the Invoice "updating" event
     */
    public function updating(Invoice $invoice): void
    {
        // Validate status transitions
        $this->validateStatusTransition($invoice);
    }

    /**
     * Handle the Invoice "updated" event
     */
    public function updated(Invoice $invoice): void
    {
        // Trigger events based on status changes
        if ($invoice->wasChanged('status')) {
            $this->handleStatusChange($invoice);
        }
    }

    /**
     * Handle the Invoice "deleting" event
     */
    public function deleting(Invoice $invoice): void
    {
        // Cascade delete payments and items if needed
    }

    /**
     * Handle the Invoice "deleted" event
     */
    public function deleted(Invoice $invoice): void
    {
        // Log deletion or notify relevant parties
    }

    /**
     * Validate status transition
     * 
     * @param Invoice $invoice
     * @return void
     */
    protected function validateStatusTransition(Invoice $invoice): void
    {
        // Early return if status didn't change
        if (!$invoice->isDirty('status')) {
            return;
        }

        $oldStatus = $invoice->getOriginal('status');
        $newStatus = $invoice->status;

        // Add validation logic for valid status transitions
        // For example: Can't go from Paid back to Draft
    }

    /**
     * Handle status change
     * 
     * @param Invoice $invoice
     * @return void
     */
    protected function handleStatusChange(Invoice $invoice): void
    {
        // Early return if no status change
        if (!$invoice->wasChanged('status')) {
            return;
        }

        // Trigger notifications, events, etc. based on new status
        match ($invoice->status) {
            \App\Enums\InvoiceStatus::Sent => $this->handleSent($invoice),
            \App\Enums\InvoiceStatus::Paid => $this->handlePaid($invoice),
            \App\Enums\InvoiceStatus::Overdue => $this->handleOverdue($invoice),
            default => null,
        };
    }

    /**
     * Handle invoice sent
     */
    protected function handleSent(Invoice $invoice): void
    {
        // Send email notification to client
    }

    /**
     * Handle invoice paid
     */
    protected function handlePaid(Invoice $invoice): void
    {
        // Send thank you email, update accounting
    }

    /**
     * Handle invoice overdue
     */
    protected function handleOverdue(Invoice $invoice): void
    {
        // Send overdue notification
    }
}
