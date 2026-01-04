<?php

namespace Tests\Feature\Services;

use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceService $invoiceService;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceService = app(InvoiceService::class);
        $this->company = Company::factory()->create();
    }

    /**
     * @test
     * Arrange: Company exists and invoice data is provided
     * Act: Create invoice via service
     * Assert: Invoice is created with correct data
     */
    public function it_creates_an_invoice_with_generated_number(): void
    {
        // Arrange
        $data = [
            'client_id' => 1,
            'total' => 1000.00,
            'tax_rate' => 10,
        ];

        // Act
        $invoice = $this->invoiceService->create($this->company, $data);

        // Assert
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertNotEmpty($invoice->invoice_number);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
        $this->assertEquals(InvoiceStatus::Draft, $invoice->status);
        $this->assertEquals(1000.00, $invoice->total);
    }

    /**
     * @test
     * Arrange: Company and invoice data with custom invoice number
     * Act: Create invoice via service
     * Assert: Invoice uses custom invoice number
     */
    public function it_creates_an_invoice_with_custom_invoice_number(): void
    {
        // Arrange
        $data = [
            'invoice_number' => 'CUSTOM-001',
            'client_id' => 1,
            'total' => 500.00,
            'tax_rate' => 5,
        ];

        // Act
        $invoice = $this->invoiceService->create($this->company, $data);

        // Assert
        $this->assertEquals('CUSTOM-001', $invoice->invoice_number);
    }

    /**
     * @test
     * Arrange: Invoice exists with items
     * Act: Update invoice via service
     * Assert: Invoice is updated with new data
     */
    public function it_updates_an_existing_invoice(): void
    {
        // Arrange
        $invoice = Invoice::factory()->for($this->company)->create([
            'total' => 1000.00,
        ]);
        $data = [
            'total' => 1500.00,
            'tax_rate' => 15,
        ];

        // Act
        $updatedInvoice = $this->invoiceService->update($invoice, $data);

        // Assert
        $this->assertEquals(1500.00, $updatedInvoice->total);
        $this->assertEquals(15, $updatedInvoice->tax_rate);
    }

    /**
     * @test
     * Arrange: Draft invoice exists
     * Act: Mark invoice as sent via service
     * Assert: Invoice status is Sent and issued_at is set
     */
    public function it_marks_invoice_as_sent(): void
    {
        // Arrange
        $invoice = Invoice::factory()->for($this->company)->create([
            'status' => InvoiceStatus::Draft,
            'issued_at' => null,
        ]);

        // Act
        $sentInvoice = $this->invoiceService->markAsSent($invoice);

        // Assert
        $this->assertEquals(InvoiceStatus::Sent, $sentInvoice->status);
        $this->assertNotNull($sentInvoice->issued_at);
    }

    /**
     * @test
     * Arrange: Invoice already sent
     * Act: Mark invoice as sent again
     * Assert: Returns same invoice without changes (early return)
     */
    public function it_returns_early_when_invoice_already_sent(): void
    {
        // Arrange
        $issuedAt = now()->subDays(5);
        $invoice = Invoice::factory()->for($this->company)->create([
            'status' => InvoiceStatus::Sent,
            'issued_at' => $issuedAt,
        ]);

        // Act
        $result = $this->invoiceService->markAsSent($invoice);

        // Assert
        $this->assertEquals(InvoiceStatus::Sent, $result->status);
        $this->assertEquals($issuedAt->toDateTimeString(), $result->issued_at->toDateTimeString());
    }

    /**
     * @test
     * Arrange: Sent invoice exists
     * Act: Mark invoice as paid via service
     * Assert: Invoice status is Paid
     */
    public function it_marks_invoice_as_paid(): void
    {
        // Arrange
        $invoice = Invoice::factory()->for($this->company)->create([
            'status' => InvoiceStatus::Sent,
        ]);

        // Act
        $paidInvoice = $this->invoiceService->markAsPaid($invoice);

        // Assert
        $this->assertEquals(InvoiceStatus::Paid, $paidInvoice->status);
    }

    /**
     * @test
     * Arrange: Invoice already paid
     * Act: Mark invoice as paid again
     * Assert: Returns same invoice without changes (early return)
     */
    public function it_returns_early_when_invoice_already_paid(): void
    {
        // Arrange
        $invoice = Invoice::factory()->for($this->company)->create([
            'status' => InvoiceStatus::Paid,
        ]);

        // Act
        $result = $this->invoiceService->markAsPaid($invoice);

        // Assert
        $this->assertEquals(InvoiceStatus::Paid, $result->status);
    }

    /**
     * @test
     * Arrange: Invoice with items exists
     * Act: Duplicate invoice via service
     * Assert: New invoice created with new number and draft status
     */
    public function it_duplicates_an_invoice_with_new_number(): void
    {
        // Arrange
        $invoice = Invoice::factory()->for($this->company)->create([
            'invoice_number' => 'INV-2026-000001',
            'status' => InvoiceStatus::Paid,
            'total' => 2000.00,
        ]);

        // Act
        $duplicatedInvoice = $this->invoiceService->duplicate($invoice);

        // Assert
        $this->assertNotEquals($invoice->id, $duplicatedInvoice->id);
        $this->assertNotEquals($invoice->invoice_number, $duplicatedInvoice->invoice_number);
        $this->assertEquals(InvoiceStatus::Draft, $duplicatedInvoice->status);
        $this->assertEquals(2000.00, $duplicatedInvoice->total);
        $this->assertNull($duplicatedInvoice->issued_at);
    }

    /**
     * @test
     * Arrange: Invoice without items
     * Act: Calculate totals via service
     * Assert: Correct subtotal, tax, and total calculated
     */
    public function it_calculates_invoice_totals_correctly(): void
    {
        // Arrange
        $invoice = Invoice::factory()->for($this->company)->create([
            'tax_rate' => 10,
        ]);
        
        // Mock items for calculation
        $invoice->items()->create(['quantity' => 2, 'unit_price' => 50.00]);
        $invoice->items()->create(['quantity' => 3, 'unit_price' => 100.00]);

        // Act
        $totals = $this->invoiceService->calculateTotals($invoice);

        // Assert
        $this->assertEquals(400.00, $totals['subtotal']); // (2*50) + (3*100)
        $this->assertEquals(40.00, $totals['tax']); // 400 * 10%
        $this->assertEquals(440.00, $totals['total']);
    }

    /**
     * @test
     * Arrange: Multiple invoices exist for company
     * Act: Create new invoice
     * Assert: Invoice number increments correctly
     */
    public function it_generates_sequential_invoice_numbers(): void
    {
        // Arrange
        Invoice::factory()->for($this->company)->create([
            'invoice_number' => 'INV-2026-000001',
        ]);
        Invoice::factory()->for($this->company)->create([
            'invoice_number' => 'INV-2026-000002',
        ]);

        // Act
        $invoice = $this->invoiceService->create($this->company, [
            'client_id' => 1,
            'total' => 100.00,
            'tax_rate' => 0,
        ]);

        // Assert
        $this->assertEquals('INV-2026-000003', $invoice->invoice_number);
    }

    /**
     * @test
     * Arrange: No company provided
     * Act: Attempt to create invoice
     * Assert: Exception thrown (early return validation)
     */
    public function it_throws_exception_when_creating_invoice_without_company(): void
    {
        // Arrange
        $data = [
            'client_id' => 1,
            'total' => 100.00,
        ];

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Company is required');

        // Act
        $this->invoiceService->create(null, $data);
    }
}
