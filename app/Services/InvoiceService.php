<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Company;
use App\Enums\InvoiceStatus;
use Illuminate\Support\Facades\DB;

/**
 * InvoiceService - Handles all business logic for invoice operations
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles invoice business logic
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on abstractions (interfaces/models)
 */
class InvoiceService
{
    /**
     * Create a new invoice with items
     * 
     * @param Company $company
     * @param array $data
     * @return Invoice
     */
    public function create(Company $company, array $data): Invoice
    {
        return DB::transaction(function () use ($company, $data) {
            // Early return if no company
            if (!$company) {
                throw new \InvalidArgumentException('Company is required');
            }

            // Validate tenant scoping: ensure client belongs to the company
            if (isset($data['client_id'])) {
                $client = \App\Models\Client::find($data['client_id']);
                if (!$client || $client->company_id !== $company->id) {
                    throw new \InvalidArgumentException('Client does not belong to the specified company');
                }
                $data['company_id'] = $client->company_id;
            }

            // Generate invoice number if not provided
            if (!isset($data['invoice_number'])) {
                $data['invoice_number'] = $this->generateInvoiceNumber($company);
            }

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = InvoiceStatus::Draft;
            }

            // Create invoice
            $invoice = Invoice::create($data);

            // Attach items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                $this->syncItems($invoice, $data['items']);
            }

            return $invoice->fresh();
        });
    }

    /**
     * Update an existing invoice
     * 
     * @param Invoice $invoice
     * @param array $data
     * @return Invoice
     */
    public function update(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            // Update invoice
            $invoice->update($data);

            // Sync items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                $this->syncItems($invoice, $data['items']);
            }

            return $invoice->fresh();
        });
    }

    /**
     * Mark invoice as sent
     * 
     * @param Invoice $invoice
     * @return Invoice
     */
    public function markAsSent(Invoice $invoice): Invoice
    {
        // Early return if already sent
        if ($invoice->status === InvoiceStatus::Sent) {
            return $invoice;
        }

        $invoice->update([
            'status' => InvoiceStatus::Sent,
            'issued_at' => now(),
        ]);

        return $invoice->fresh();
    }

    /**
     * Mark invoice as paid
     * 
     * @param Invoice $invoice
     * @return Invoice
     */
    public function markAsPaid(Invoice $invoice): Invoice
    {
        // Early return if already paid
        if ($invoice->status === InvoiceStatus::Paid) {
            return $invoice;
        }

        $invoice->update([
            'status' => InvoiceStatus::Paid,
        ]);

        return $invoice->fresh();
    }

    /**
     * Duplicate an invoice
     * 
     * @param Invoice $invoice
     * @return Invoice
     */
    public function duplicate(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $newInvoice = $invoice->replicate();
            $newInvoice->invoice_number = $this->generateInvoiceNumber($invoice->company);
            $newInvoice->status = InvoiceStatus::Draft;
            $newInvoice->issued_at = null;
            $newInvoice->save();

            // Duplicate items
            foreach ($invoice->items as $item) {
                $newItem = $item->replicate();
                $newItem->itemable_id = $newInvoice->id;
                $newItem->save();
            }

            return $newInvoice->fresh();
        });
    }

    /**
     * Calculate invoice totals
     * 
     * @param Invoice $invoice
     * @return array
     */
    public function calculateTotals(Invoice $invoice): array
    {
        $subtotal = $invoice->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $tax = $subtotal * ($invoice->tax_rate / 100);
        $total = $subtotal + $tax;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    /**
     * Generate unique invoice number
     * 
     * @param Company $company
     * @return string
     */
    protected function generateInvoiceNumber(Company $company): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $lastInvoice = $company->invoices()
            ->where('invoice_number', 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if (!$lastInvoice) {
            $sequence = 1;
        } else {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -6));
            $sequence = $lastNumber + 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }

    /**
     * Sync invoice items
     * 
     * @param Invoice $invoice
     * @param array $items
     * @return void
     */
    protected function syncItems(Invoice $invoice, array $items): void
    {
        // Delete existing items
        $invoice->items()->delete();

        // Create new items
        foreach ($items as $itemData) {
            $invoice->items()->create($itemData);
        }
    }
}
