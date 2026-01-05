<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\PaymentService;

/**
 * PaymentObserver - Observes Payment model events
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles payment observation logic
 * - Open/Closed: Can be extended without modification
 */
class PaymentObserver
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle the Payment "creating" event
     */
    public function creating(Payment $payment): void
    {
        // Payment number will be generated in service layer
    }

    /**
     * Handle the Payment "created" event
     */
    public function created(Payment $payment): void
    {
        // Log payment creation or trigger notifications
        // Update invoice status is handled in service layer
    }

    /**
     * Handle the Payment "updated" event
     */
    public function updated(Payment $payment): void
    {
        // Trigger events based on changes
        if ($payment->wasChanged('amount')) {
            $this->handleAmountChange($payment);
        }
    }

    /**
     * Handle the Payment "deleted" event
     */
    public function deleted(Payment $payment): void
    {
        // Log deletion or notify relevant parties
        // Invoice status update is handled in service layer
    }

    /**
     * Handle payment amount change
     * 
     * @param Payment $payment
     * @return void
     */
    protected function handleAmountChange(Payment $payment): void
    {
        // Recalculate invoice totals and update status
        // This is handled in the service layer
    }
}
