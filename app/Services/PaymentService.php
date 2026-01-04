<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Company;
use App\Enums\PaymentMethod;
use Illuminate\Support\Facades\DB;

/**
 * PaymentService - Handles all business logic for payment operations
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles payment business logic
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on abstractions (interfaces/models)
 */
class PaymentService
{
    /**
     * Create a new payment
     * 
     * @param Company $company
     * @param array $data
     * @return Payment
     */
    public function create(Company $company, array $data): Payment
    {
        return DB::transaction(function () use ($company, $data) {
            // Early return if no company
            if (!$company) {
                throw new \InvalidArgumentException('Company is required');
            }

            // Validate tenant scoping: ensure invoice belongs to the company
            if (isset($data['invoice_id'])) {
                $invoice = Invoice::find($data['invoice_id']);
                if (!$invoice || $invoice->company_id !== $company->id) {
                    throw new \InvalidArgumentException('Invoice does not belong to the specified company');
                }
            }

            // Generate payment number if not provided
            if (!isset($data['payment_number'])) {
                $data['payment_number'] = $this->generatePaymentNumber($company);
            }

            // Set default paid_at if not provided
            if (!isset($data['paid_at'])) {
                $data['paid_at'] = now();
            }

            // Create payment
            $payment = Payment::create($data);

            // Update invoice status if needed
            if (isset($data['invoice_id'])) {
                $this->updateInvoiceStatus($payment->invoice);
            }

            return $payment->fresh();
        });
    }

    /**
     * Update an existing payment
     * 
     * @param Payment $payment
     * @param array $data
     * @return Payment
     */
    public function update(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            $payment->update($data);

            // Update invoice status if needed
            $this->updateInvoiceStatus($payment->invoice);

            return $payment->fresh();
        });
    }

    /**
     * Delete a payment and update invoice status
     * 
     * @param Payment $payment
     * @return bool
     */
    public function delete(Payment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            $invoice = $payment->invoice;
            $deleted = $payment->delete();

            if ($deleted) {
                $this->updateInvoiceStatus($invoice);
            }

            return $deleted;
        });
    }

    /**
     * Calculate total payments for an invoice
     * 
     * @param Invoice $invoice
     * @return float
     */
    public function calculateTotalPayments(Invoice $invoice): float
    {
        return $invoice->payments()->sum('amount') ?? 0.0;
    }

    /**
     * Calculate remaining balance for an invoice
     * 
     * @param Invoice $invoice
     * @return float
     */
    public function calculateRemainingBalance(Invoice $invoice): float
    {
        $totalPayments = $this->calculateTotalPayments($invoice);
        return max(0, $invoice->total - $totalPayments);
    }

    /**
     * Check if invoice is fully paid
     * 
     * @param Invoice $invoice
     * @return bool
     */
    public function isFullyPaid(Invoice $invoice): bool
    {
        $remainingBalance = $this->calculateRemainingBalance($invoice);
        return $remainingBalance <= 0.01; // Account for floating point precision
    }

    /**
     * Check if invoice is partially paid
     * 
     * @param Invoice $invoice
     * @return bool
     */
    public function isPartiallyPaid(Invoice $invoice): bool
    {
        $totalPayments = $this->calculateTotalPayments($invoice);
        return $totalPayments > 0 && !$this->isFullyPaid($invoice);
    }

    /**
     * Update invoice status based on payments
     * 
     * @param Invoice $invoice
     * @return void
     */
    protected function updateInvoiceStatus(Invoice $invoice): void
    {
        if ($this->isFullyPaid($invoice)) {
            $invoiceService = app(InvoiceService::class);
            $invoiceService->markAsPaid($invoice);
        }
    }

    /**
     * Generate unique payment number
     * 
     * @param Company $company
     * @return string
     */
    protected function generatePaymentNumber(Company $company): string
    {
        $prefix = 'PAY';
        $year = date('Y');
        $lastPayment = Payment::whereHas('invoice', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->where('payment_number', 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy('payment_number', 'desc')
            ->first();

        if (!$lastPayment) {
            $sequence = 1;
        } else {
            $lastNumber = intval(substr($lastPayment->payment_number, -6));
            $sequence = $lastNumber + 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }
}
