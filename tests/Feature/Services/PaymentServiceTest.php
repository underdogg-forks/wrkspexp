<?php

namespace Tests\Feature\Services;

use App\Enums\PaymentMethod;
use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;
    protected Company $company;
    protected Client $client;
    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = app(PaymentService::class);
        $this->company = Company::factory()->create();
        $this->client = Client::factory()->create(['company_id' => $this->company->id]);
        $this->invoice = Invoice::factory()->create([
            'client_id' => $this->client->id,
            'company_id' => $this->company->id,
            'total' => 1000.00,
            'status' => InvoiceStatus::Sent,
        ]);
    }

    /**
     * @test
     * Arrange: Company exists and payment data is provided
     * Act: Create payment via service
     * Assert: Payment is created with correct data
     */
    public function it_creates_a_payment_with_generated_number(): void
    {
        // Arrange
        $data = [
            'invoice_id' => $this->invoice->id,
            'amount' => 500.00,
            'payment_method' => PaymentMethod::Cash->value,
        ];

        // Act
        $payment = $this->paymentService->create($this->company, $data);

        // Assert
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotEmpty($payment->payment_number);
        $this->assertStringStartsWith('PAY-', $payment->payment_number);
        $this->assertEquals(500.00, $payment->amount);
        $this->assertEquals(PaymentMethod::Cash, $payment->payment_method);
        $this->assertNotNull($payment->paid_at);
    }

    /**
     * @test
     * Arrange: Company and payment data with custom payment number
     * Act: Create payment via service
     * Assert: Payment uses custom payment number
     */
    public function it_creates_a_payment_with_custom_payment_number(): void
    {
        // Arrange
        $data = [
            'payment_number' => 'CUSTOM-PAY-001',
            'invoice_id' => $this->invoice->id,
            'amount' => 250.00,
            'payment_method' => PaymentMethod::BankTransfer->value,
        ];

        // Act
        $payment = $this->paymentService->create($this->company, $data);

        // Assert
        $this->assertEquals('CUSTOM-PAY-001', $payment->payment_number);
    }

    /**
     * @test
     * Arrange: Payment data with invoice from different company
     * Act: Attempt to create payment
     * Assert: Exception is thrown for tenant validation
     */
    public function it_validates_tenant_scoping_on_create(): void
    {
        // Arrange
        $otherCompany = Company::factory()->create();
        $otherClient = Client::factory()->create(['company_id' => $otherCompany->id]);
        $otherInvoice = Invoice::factory()->create([
            'client_id' => $otherClient->id,
            'company_id' => $otherCompany->id,
        ]);
        
        $data = [
            'invoice_id' => $otherInvoice->id,
            'amount' => 500.00,
            'payment_method' => PaymentMethod::Cash->value,
        ];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invoice does not belong to the specified company');
        $this->paymentService->create($this->company, $data);
    }

    /**
     * @test
     * Arrange: Payment exists
     * Act: Update payment via service
     * Assert: Payment is updated with new data
     */
    public function it_updates_a_payment(): void
    {
        // Arrange
        $payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 500.00,
        ]);
        $data = ['amount' => 750.00];

        // Act
        $updatedPayment = $this->paymentService->update($payment, $data);

        // Assert
        $this->assertEquals(750.00, $updatedPayment->amount);
    }

    /**
     * @test
     * Arrange: Payment exists
     * Act: Delete payment
     * Assert: Payment is deleted successfully
     */
    public function it_deletes_a_payment(): void
    {
        // Arrange
        $payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 500.00,
        ]);

        // Act
        $deleted = $this->paymentService->delete($payment);

        // Assert
        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    /**
     * @test
     * Arrange: Invoice with multiple payments
     * Act: Calculate total payments
     * Assert: Returns correct sum
     */
    public function it_calculates_total_payments(): void
    {
        // Arrange
        Payment::factory()->create(['invoice_id' => $this->invoice->id, 'amount' => 300.00]);
        Payment::factory()->create(['invoice_id' => $this->invoice->id, 'amount' => 200.00]);
        Payment::factory()->create(['invoice_id' => $this->invoice->id, 'amount' => 150.00]);

        // Act
        $totalPayments = $this->paymentService->calculateTotalPayments($this->invoice);

        // Assert
        $this->assertEquals(650.00, $totalPayments);
    }

    /**
     * @test
     * Arrange: Invoice with no payments
     * Act: Calculate total payments
     * Assert: Returns 0.0
     */
    public function it_returns_zero_for_invoice_with_no_payments(): void
    {
        // Arrange
        // No payments created

        // Act
        $totalPayments = $this->paymentService->calculateTotalPayments($this->invoice);

        // Assert
        $this->assertEquals(0.0, $totalPayments);
    }

    /**
     * @test
     * Arrange: Invoice with partial payment
     * Act: Calculate remaining balance
     * Assert: Returns correct remaining amount
     */
    public function it_calculates_remaining_balance(): void
    {
        // Arrange
        Payment::factory()->create(['invoice_id' => $this->invoice->id, 'amount' => 400.00]);

        // Act
        $remainingBalance = $this->paymentService->calculateRemainingBalance($this->invoice);

        // Assert
        $this->assertEquals(600.00, $remainingBalance);
    }

    /**
     * @test
     * Arrange: Invoice with full payment
     * Act: Check if fully paid
     * Assert: Returns true
     */
    public function it_checks_if_invoice_is_fully_paid(): void
    {
        // Arrange
        Payment::factory()->create(['invoice_id' => $this->invoice->id, 'amount' => 1000.00]);

        // Act
        $isFullyPaid = $this->paymentService->isFullyPaid($this->invoice);

        // Assert
        $this->assertTrue($isFullyPaid);
    }

    /**
     * @test
     * Arrange: Invoice with no payment
     * Act: Check if fully paid
     * Assert: Returns false
     */
    public function it_returns_false_if_invoice_not_fully_paid(): void
    {
        // Arrange
        Payment::factory()->create(['invoice_id' => $this->invoice->id, 'amount' => 500.00]);

        // Act
        $isFullyPaid = $this->paymentService->isFullyPaid($this->invoice);

        // Assert
        $this->assertFalse($isFullyPaid);
    }

    /**
     * @test
     * Arrange: Invoice with partial payment
     * Act: Check if partially paid
     * Assert: Returns true
     */
    public function it_checks_if_invoice_is_partially_paid(): void
    {
        // Arrange
        Payment::factory()->create(['invoice_id' => $this->invoice->id, 'amount' => 400.00]);

        // Act
        $isPartiallyPaid = $this->paymentService->isPartiallyPaid($this->invoice);

        // Assert
        $this->assertTrue($isPartiallyPaid);
    }

    /**
     * @test
     * Arrange: Invoice with no payment
     * Act: Check if partially paid
     * Assert: Returns false
     */
    public function it_returns_false_for_partially_paid_when_no_payments(): void
    {
        // Arrange
        // No payments created

        // Act
        $isPartiallyPaid = $this->paymentService->isPartiallyPaid($this->invoice);

        // Assert
        $this->assertFalse($isPartiallyPaid);
    }

    /**
     * @test
     * Arrange: Invoice with full payment
     * Act: Check if partially paid
     * Assert: Returns false (it's fully paid, not partially)
     */
    public function it_returns_false_for_partially_paid_when_fully_paid(): void
    {
        // Arrange
        Payment::factory()->create(['invoice_id' => $this->invoice->id, 'amount' => 1000.00]);

        // Act
        $isPartiallyPaid = $this->paymentService->isPartiallyPaid($this->invoice);

        // Assert
        $this->assertFalse($isPartiallyPaid);
    }

    /**
     * @test
     * Arrange: No company provided
     * Act: Attempt to create payment
     * Assert: Exception is thrown
     */
    public function it_throws_exception_if_no_company_provided(): void
    {
        // Arrange
        $data = [
            'invoice_id' => $this->invoice->id,
            'amount' => 500.00,
            'payment_method' => PaymentMethod::Cash->value,
        ];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Company is required');
        $this->paymentService->create(null, $data);
    }

    /**
     * @test
     * Arrange: Multiple payments exist for same company
     * Act: Create new payment
     * Assert: Payment number sequence increments correctly
     */
    public function it_generates_sequential_payment_numbers(): void
    {
        // Arrange
        $data = [
            'invoice_id' => $this->invoice->id,
            'amount' => 100.00,
            'payment_method' => PaymentMethod::Cash->value,
        ];

        // Act
        $payment1 = $this->paymentService->create($this->company, $data);
        $payment2 = $this->paymentService->create($this->company, $data);
        $payment3 = $this->paymentService->create($this->company, $data);

        // Assert
        $this->assertStringContainsString('PAY-', $payment1->payment_number);
        $this->assertStringContainsString('PAY-', $payment2->payment_number);
        $this->assertStringContainsString('PAY-', $payment3->payment_number);
        $this->assertNotEquals($payment1->payment_number, $payment2->payment_number);
        $this->assertNotEquals($payment2->payment_number, $payment3->payment_number);
    }
}
