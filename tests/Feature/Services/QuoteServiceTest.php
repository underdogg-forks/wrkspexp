<?php

namespace Tests\Feature\Services;

use App\Enums\QuoteStatus;
use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Company;
use App\Models\Quote;
use App\Models\Invoice;
use App\Services\QuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteServiceTest extends TestCase
{
    use RefreshDatabase;

    protected QuoteService $quoteService;
    protected Company $company;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->quoteService = app(QuoteService::class);
        $this->company = Company::factory()->create();
        $this->client = Client::factory()->create(['company_id' => $this->company->id]);
    }

    /**
     * @test
     * Arrange: Company exists and quote data is provided
     * Act: Create quote via service
     * Assert: Quote is created with correct data
     */
    public function it_creates_a_quote_with_generated_number(): void
    {
        // Arrange
        $data = [
            'client_id' => $this->client->id,
            'subtotal' => 1000.00,
            'tax' => 210.00,
            'total' => 1210.00,
        ];

        // Act
        $quote = $this->quoteService->create($this->company, $data);

        // Assert
        $this->assertInstanceOf(Quote::class, $quote);
        $this->assertNotEmpty($quote->quote_number);
        $this->assertStringStartsWith('QUO-', $quote->quote_number);
        $this->assertEquals(QuoteStatus::Draft, $quote->status);
        $this->assertEquals(1000.00, $quote->subtotal);
        $this->assertEquals(210.00, $quote->tax);
        $this->assertEquals(1210.00, $quote->total);
        $this->assertNotNull($quote->issued_at);
    }

    /**
     * @test
     * Arrange: Company and quote data with custom quote number
     * Act: Create quote via service
     * Assert: Quote uses custom quote number
     */
    public function it_creates_a_quote_with_custom_quote_number(): void
    {
        // Arrange
        $data = [
            'quote_number' => 'CUSTOM-QUO-001',
            'client_id' => $this->client->id,
            'subtotal' => 500.00,
            'tax' => 105.00,
            'total' => 605.00,
        ];

        // Act
        $quote = $this->quoteService->create($this->company, $data);

        // Assert
        $this->assertEquals('CUSTOM-QUO-001', $quote->quote_number);
    }

    /**
     * @test
     * Arrange: Quote data with client from different company
     * Act: Attempt to create quote
     * Assert: Exception is thrown for tenant validation
     */
    public function it_validates_tenant_scoping_on_create(): void
    {
        // Arrange
        $otherCompany = Company::factory()->create();
        $otherClient = Client::factory()->create(['company_id' => $otherCompany->id]);
        
        $data = [
            'client_id' => $otherClient->id,
            'subtotal' => 500.00,
            'tax' => 105.00,
            'total' => 605.00,
        ];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Client does not belong to the specified company');
        $this->quoteService->create($this->company, $data);
    }

    /**
     * @test
     * Arrange: Quote exists
     * Act: Update quote via service
     * Assert: Quote is updated with new data
     */
    public function it_updates_a_quote(): void
    {
        // Arrange
        $quote = Quote::factory()->create([
            'client_id' => $this->client->id,
            'subtotal' => 1000.00,
            'total' => 1210.00,
        ]);
        $data = [
            'subtotal' => 1500.00,
            'total' => 1815.00,
        ];

        // Act
        $updatedQuote = $this->quoteService->update($quote, $data);

        // Assert
        $this->assertEquals(1500.00, $updatedQuote->subtotal);
        $this->assertEquals(1815.00, $updatedQuote->total);
    }

    /**
     * @test
     * Arrange: Quote with draft status exists
     * Act: Send quote
     * Assert: Quote status is updated to sent
     */
    public function it_sends_a_quote(): void
    {
        // Arrange
        $quote = Quote::factory()->create([
            'client_id' => $this->client->id,
            'status' => QuoteStatus::Draft,
        ]);

        // Act
        $sentQuote = $this->quoteService->send($quote);

        // Assert
        $this->assertEquals(QuoteStatus::Sent, $sentQuote->status);
        $this->assertNotNull($sentQuote->issued_at);
    }

    /**
     * @test
     * Arrange: Quote already sent
     * Act: Send quote again
     * Assert: Quote remains unchanged (early return)
     */
    public function it_returns_early_if_quote_already_sent(): void
    {
        // Arrange
        $quote = Quote::factory()->create([
            'client_id' => $this->client->id,
            'status' => QuoteStatus::Sent,
        ]);

        // Act
        $sentQuote = $this->quoteService->send($quote);

        // Assert
        $this->assertEquals(QuoteStatus::Sent, $sentQuote->status);
    }

    /**
     * @test
     * Arrange: Quote with sent status exists
     * Act: Accept quote
     * Assert: Quote status is updated to accepted
     */
    public function it_accepts_a_quote(): void
    {
        // Arrange
        $quote = Quote::factory()->create([
            'client_id' => $this->client->id,
            'status' => QuoteStatus::Sent,
        ]);

        // Act
        $acceptedQuote = $this->quoteService->accept($quote);

        // Assert
        $this->assertEquals(QuoteStatus::Accepted, $acceptedQuote->status);
    }

    /**
     * @test
     * Arrange: Quote already accepted
     * Act: Accept quote again
     * Assert: Quote remains unchanged (early return)
     */
    public function it_returns_early_if_quote_already_accepted(): void
    {
        // Arrange
        $quote = Quote::factory()->create([
            'client_id' => $this->client->id,
            'status' => QuoteStatus::Accepted,
        ]);

        // Act
        $acceptedQuote = $this->quoteService->accept($quote);

        // Assert
        $this->assertEquals(QuoteStatus::Accepted, $acceptedQuote->status);
    }

    /**
     * @test
     * Arrange: Quote with sent status exists
     * Act: Decline quote
     * Assert: Quote status is updated to declined
     */
    public function it_declines_a_quote(): void
    {
        // Arrange
        $quote = Quote::factory()->create([
            'client_id' => $this->client->id,
            'status' => QuoteStatus::Sent,
        ]);

        // Act
        $declinedQuote = $this->quoteService->decline($quote);

        // Assert
        $this->assertEquals(QuoteStatus::Declined, $declinedQuote->status);
    }

    /**
     * @test
     * Arrange: Quote already declined
     * Act: Decline quote again
     * Assert: Quote remains unchanged (early return)
     */
    public function it_returns_early_if_quote_already_declined(): void
    {
        // Arrange
        $quote = Quote::factory()->create([
            'client_id' => $this->client->id,
            'status' => QuoteStatus::Declined,
        ]);

        // Act
        $declinedQuote = $this->quoteService->decline($quote);

        // Assert
        $this->assertEquals(QuoteStatus::Declined, $declinedQuote->status);
    }

    /**
     * @test
     * Arrange: Quote with sent status exists
     * Act: Mark quote as expired
     * Assert: Quote status is updated to expired
     */
    public function it_marks_quote_as_expired(): void
    {
        // Arrange
        $quote = Quote::factory()->create([
            'client_id' => $this->client->id,
            'status' => QuoteStatus::Sent,
        ]);

        // Act
        $expiredQuote = $this->quoteService->markAsExpired($quote);

        // Assert
        $this->assertEquals(QuoteStatus::Expired, $expiredQuote->status);
    }

    /**
     * @test
     * Arrange: Quote already expired
     * Act: Mark quote as expired again
     * Assert: Quote remains unchanged (early return)
     */
    public function it_returns_early_if_quote_already_expired(): void
    {
        // Arrange
        $quote = Quote::factory()->create([
            'client_id' => $this->client->id,
            'status' => QuoteStatus::Expired,
        ]);

        // Act
        $expiredQuote = $this->quoteService->markAsExpired($quote);

        // Assert
        $this->assertEquals(QuoteStatus::Expired, $expiredQuote->status);
    }

    /**
     * @test
     * Arrange: Quote exists
     * Act: Duplicate quote
     * Assert: New quote is created with same data but new number and draft status
     */
    public function it_duplicates_a_quote(): void
    {
        // Arrange
        $originalQuote = Quote::factory()->create([
            'client_id' => $this->client->id,
            'subtotal' => 1000.00,
            'tax' => 210.00,
            'total' => 1210.00,
            'status' => QuoteStatus::Accepted,
        ]);

        // Act
        $duplicatedQuote = $this->quoteService->duplicate($originalQuote);

        // Assert
        $this->assertNotEquals($originalQuote->id, $duplicatedQuote->id);
        $this->assertNotEquals($originalQuote->quote_number, $duplicatedQuote->quote_number);
        $this->assertEquals(1000.00, $duplicatedQuote->subtotal);
        $this->assertEquals(210.00, $duplicatedQuote->tax);
        $this->assertEquals(1210.00, $duplicatedQuote->total);
        $this->assertEquals(QuoteStatus::Draft, $duplicatedQuote->status);
        $this->assertNotNull($duplicatedQuote->issued_at);
        $this->assertNotNull($duplicatedQuote->expires_at);
    }

    /**
     * @test
     * Arrange: Accepted quote exists
     * Act: Convert quote to invoice
     * Assert: Invoice is created with quote data and quote is marked as accepted
     */
    public function it_converts_quote_to_invoice(): void
    {
        // Arrange
        $quote = Quote::factory()->create([
            'client_id' => $this->client->id,
            'subtotal' => 1000.00,
            'tax' => 210.00,
            'total' => 1210.00,
            'status' => QuoteStatus::Sent,
        ]);

        // Act
        $invoice = $this->quoteService->convertToInvoice($quote);

        // Assert
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($quote->client_id, $invoice->client_id);
        $this->assertEquals($this->company->id, $invoice->company_id);
        $this->assertEquals(1000.00, $invoice->subtotal);
        $this->assertEquals(210.00, $invoice->tax);
        $this->assertEquals(1210.00, $invoice->total);
        $this->assertEquals(InvoiceStatus::Draft, $invoice->status);
        
        // Verify quote is marked as accepted
        $quote->refresh();
        $this->assertEquals(QuoteStatus::Accepted, $quote->status);
    }

    /**
     * @test
     * Arrange: No company provided
     * Act: Attempt to create quote
     * Assert: Exception is thrown
     */
    public function it_throws_exception_if_no_company_provided(): void
    {
        // Arrange
        $data = [
            'client_id' => $this->client->id,
            'subtotal' => 1000.00,
            'tax' => 210.00,
            'total' => 1210.00,
        ];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Company is required');
        $this->quoteService->create(null, $data);
    }

    /**
     * @test
     * Arrange: Multiple quotes exist for same company
     * Act: Create new quote
     * Assert: Quote number sequence increments correctly
     */
    public function it_generates_sequential_quote_numbers(): void
    {
        // Arrange
        $data = [
            'client_id' => $this->client->id,
            'subtotal' => 1000.00,
            'tax' => 210.00,
            'total' => 1210.00,
        ];

        // Act
        $quote1 = $this->quoteService->create($this->company, $data);
        $quote2 = $this->quoteService->create($this->company, $data);
        $quote3 = $this->quoteService->create($this->company, $data);

        // Assert
        $this->assertStringContainsString('QUO-', $quote1->quote_number);
        $this->assertStringContainsString('QUO-', $quote2->quote_number);
        $this->assertStringContainsString('QUO-', $quote3->quote_number);
        $this->assertNotEquals($quote1->quote_number, $quote2->quote_number);
        $this->assertNotEquals($quote2->quote_number, $quote3->quote_number);
    }
}
