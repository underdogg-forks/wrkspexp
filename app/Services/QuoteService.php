<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\Invoice;
use App\Models\Company;
use App\Enums\QuoteStatus;
use App\Enums\InvoiceStatus;
use Illuminate\Support\Facades\DB;

/**
 * QuoteService - Handles all business logic for quote operations
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles quote business logic
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on abstractions (interfaces/models)
 */
class QuoteService
{
    /**
     * Create a new quote
     * 
     * @param Company $company
     * @param array $data
     * @return Quote
     */
    public function create(Company $company, array $data): Quote
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
            }

            // Generate quote number if not provided
            if (!isset($data['quote_number'])) {
                $data['quote_number'] = $this->generateQuoteNumber($company);
            }

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = QuoteStatus::Draft;
            }

            // Set default issued_at if not provided
            if (!isset($data['issued_at'])) {
                $data['issued_at'] = now();
            }

            // Create quote
            $quote = Quote::create($data);

            // Attach items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                $this->syncItems($quote, $data['items']);
            }

            return $quote->fresh();
        });
    }

    /**
     * Update an existing quote
     * 
     * @param Quote $quote
     * @param array $data
     * @return Quote
     */
    public function update(Quote $quote, array $data): Quote
    {
        return DB::transaction(function () use ($quote, $data) {
            $quote->update($data);

            // Sync items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                $this->syncItems($quote, $data['items']);
            }

            return $quote->fresh();
        });
    }

    /**
     * Mark quote as sent
     * 
     * @param Quote $quote
     * @return Quote
     */
    public function send(Quote $quote): Quote
    {
        // Early return if already sent
        if ($quote->status !== QuoteStatus::Draft) {
            return $quote;
        }

        $quote->update([
            'status' => QuoteStatus::Sent,
            'issued_at' => now(),
        ]);

        return $quote->fresh();
    }

    /**
     * Mark quote as accepted
     * 
     * @param Quote $quote
     * @return Quote
     */
    public function accept(Quote $quote): Quote
    {
        // Early return if already accepted
        if ($quote->status === QuoteStatus::Accepted) {
            return $quote;
        }

        $quote->update([
            'status' => QuoteStatus::Accepted,
        ]);

        return $quote->fresh();
    }

    /**
     * Mark quote as declined
     * 
     * @param Quote $quote
     * @return Quote
     */
    public function decline(Quote $quote): Quote
    {
        // Early return if already declined
        if ($quote->status === QuoteStatus::Declined) {
            return $quote;
        }

        $quote->update([
            'status' => QuoteStatus::Declined,
        ]);

        return $quote->fresh();
    }

    /**
     * Mark quote as expired
     * 
     * @param Quote $quote
     * @return Quote
     */
    public function markAsExpired(Quote $quote): Quote
    {
        // Early return if already expired
        if ($quote->status === QuoteStatus::Expired) {
            return $quote;
        }

        $quote->update([
            'status' => QuoteStatus::Expired,
        ]);

        return $quote->fresh();
    }

    /**
     * Convert quote to invoice
     * 
     * @param Quote $quote
     * @return Invoice
     */
    public function convertToInvoice(Quote $quote): Invoice
    {
        return DB::transaction(function () use ($quote) {
            // Get the company through the client relationship
            $client = $quote->client;
            $company = $client->company;

            // Create invoice from quote data
            $invoiceService = app(InvoiceService::class);
            $invoice = $invoiceService->create($company, [
                'client_id' => $quote->client_id,
                'company_id' => $company->id,
                'issued_at' => now(),
                'due_at' => now()->addDays(30),
                'status' => InvoiceStatus::Draft,
                'subtotal' => $quote->subtotal,
                'tax' => $quote->tax,
                'total' => $quote->total,
            ]);

            // Copy items from quote to invoice
            foreach ($quote->items as $item) {
                $invoice->items()->create([
                    'product_id' => $item->product_id,
                    'task_id' => $item->task_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ]);
            }

            // Mark quote as accepted
            $this->accept($quote);

            return $invoice->fresh();
        });
    }

    /**
     * Duplicate a quote
     * 
     * @param Quote $quote
     * @return Quote
     */
    public function duplicate(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote) {
            $client = $quote->client;
            $company = $client->company;
            
            $newQuote = $quote->replicate();
            $newQuote->quote_number = $this->generateQuoteNumber($company);
            $newQuote->status = QuoteStatus::Draft;
            $newQuote->issued_at = now();
            $newQuote->expires_at = now()->addDays(30);
            $newQuote->save();

            // Duplicate items
            foreach ($quote->items as $item) {
                $newItem = $item->replicate();
                $newItem->itemable_id = $newQuote->id;
                $newItem->save();
            }

            return $newQuote->fresh();
        });
    }

    /**
     * Calculate quote totals
     * 
     * @param Quote $quote
     * @return array
     */
    public function calculateTotals(Quote $quote): array
    {
        $subtotal = $quote->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $taxRate = $quote->tax_rate ?? 0;
        $tax = $subtotal * ($taxRate / 100);
        $total = $subtotal + $tax;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    /**
     * Generate unique quote number
     * 
     * @param Company $company
     * @return string
     */
    protected function generateQuoteNumber(Company $company): string
    {
        $prefix = 'QUO';
        $year = date('Y');
        $lastQuote = Quote::whereHas('client', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->where('quote_number', 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy('quote_number', 'desc')
            ->first();

        if (!$lastQuote) {
            $sequence = 1;
        } else {
            $lastNumber = intval(substr($lastQuote->quote_number, -6));
            $sequence = $lastNumber + 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }

    /**
     * Sync quote items
     * 
     * @param Quote $quote
     * @param array $items
     * @return void
     */
    protected function syncItems(Quote $quote, array $items): void
    {
        // Delete existing items
        $quote->items()->delete();

        // Create new items
        foreach ($items as $itemData) {
            $quote->items()->create($itemData);
        }
    }
}
