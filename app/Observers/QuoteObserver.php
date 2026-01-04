<?php

namespace App\Observers;

use App\Models\Quote;
use App\Services\QuoteService;

/**
 * QuoteObserver - Observes Quote model events
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles quote observation logic
 * - Open/Closed: Can be extended without modification
 */
class QuoteObserver
{
    protected QuoteService $quoteService;

    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    /**
     * Handle the Quote "creating" event
     */
    public function creating(Quote $quote): void
    {
        // Quote number will be generated in service layer
    }

    /**
     * Handle the Quote "created" event
     */
    public function created(Quote $quote): void
    {
        // Log quote creation or trigger notifications
    }

    /**
     * Handle the Quote "updating" event
     */
    public function updating(Quote $quote): void
    {
        // Check if quote has expired
        if ($quote->expires_at && $quote->expires_at->isPast() && $quote->status === \App\Enums\QuoteStatus::Sent) {
            $quote->status = \App\Enums\QuoteStatus::Expired;
        }
    }

    /**
     * Handle the Quote "updated" event
     */
    public function updated(Quote $quote): void
    {
        // Trigger events based on status changes
        if ($quote->wasChanged('status')) {
            $this->handleStatusChange($quote);
        }
    }

    /**
     * Handle the Quote "deleted" event
     */
    public function deleted(Quote $quote): void
    {
        // Log deletion or notify relevant parties
    }

    /**
     * Handle status change
     * 
     * @param Quote $quote
     * @return void
     */
    protected function handleStatusChange(Quote $quote): void
    {
        // Early return if no status change
        if (!$quote->wasChanged('status')) {
            return;
        }

        // Trigger notifications, events, etc. based on new status
        match ($quote->status) {
            \App\Enums\QuoteStatus::Sent => $this->handleSent($quote),
            \App\Enums\QuoteStatus::Accepted => $this->handleAccepted($quote),
            \App\Enums\QuoteStatus::Declined => $this->handleDeclined($quote),
            \App\Enums\QuoteStatus::Expired => $this->handleExpired($quote),
            default => null,
        };
    }

    protected function handleSent(Quote $quote): void
    {
        // Send quote to client
    }

    protected function handleAccepted(Quote $quote): void
    {
        // Send acceptance confirmation
    }

    protected function handleDeclined(Quote $quote): void
    {
        // Send decline notification
    }

    protected function handleExpired(Quote $quote): void
    {
        // Send expiry notification
    }
}
