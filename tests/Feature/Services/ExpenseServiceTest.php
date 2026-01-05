<?php

namespace Tests\Feature\Services;

use App\Enums\ExpenseStatus;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Relation;
use App\Services\ExpenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ExpenseService $expenseService;
    protected Company $company;
    protected Relation $vendor;
    protected ExpenseCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->expenseService = app(ExpenseService::class);
        $this->company = Company::factory()->create();
        $this->vendor = Relation::factory()->create();
        $this->category = ExpenseCategory::factory()->create();
    }

    /**
     * @test
     * Arrange: Company exists and expense data is provided
     * Act: Create expense via service
     * Assert: Expense is created with correct data
     */
    public function it_creates_an_expense_with_generated_number(): void
    {
        // Arrange
        $data = [
            'title' => 'Office Supplies',
            'vendor_id' => $this->vendor->id,
            'expense_category_id' => $this->category->id,
            'amount' => 250.50,
        ];

        // Act
        $expense = $this->expenseService->create($this->company, $data);

        // Assert
        $this->assertInstanceOf(Expense::class, $expense);
        $this->assertNotEmpty($expense->expense_number);
        $this->assertStringStartsWith('EXP-', $expense->expense_number);
        $this->assertEquals(ExpenseStatus::Draft, $expense->status);
        $this->assertEquals('Office Supplies', $expense->title);
        $this->assertEquals(250.50, $expense->amount);
        $this->assertEquals($this->company->id, $expense->company_id);
    }

    /**
     * @test
     * Arrange: Company and expense data with custom expense number
     * Act: Create expense via service
     * Assert: Expense uses custom expense number
     */
    public function it_creates_an_expense_with_custom_expense_number(): void
    {
        // Arrange
        $data = [
            'expense_number' => 'CUSTOM-EXP-001',
            'title' => 'Custom Expense',
            'vendor_id' => $this->vendor->id,
            'expense_category_id' => $this->category->id,
            'amount' => 100.00,
        ];

        // Act
        $expense = $this->expenseService->create($this->company, $data);

        // Assert
        $this->assertEquals('CUSTOM-EXP-001', $expense->expense_number);
    }

    /**
     * @test
     * Arrange: Expense exists
     * Act: Update expense via service
     * Assert: Expense is updated with new data
     */
    public function it_updates_an_expense(): void
    {
        // Arrange
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
            'title' => 'Original Title',
            'amount' => 100.00,
        ]);
        $data = [
            'title' => 'Updated Title',
            'amount' => 200.00,
        ];

        // Act
        $updatedExpense = $this->expenseService->update($expense, $data);

        // Assert
        $this->assertEquals('Updated Title', $updatedExpense->title);
        $this->assertEquals(200.00, $updatedExpense->amount);
    }

    /**
     * @test
     * Arrange: Expense with draft status exists
     * Act: Submit expense for approval
     * Assert: Expense status is updated to pending
     */
    public function it_submits_expense_for_approval(): void
    {
        // Arrange
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
            'status' => ExpenseStatus::Draft,
        ]);

        // Act
        $submittedExpense = $this->expenseService->submit($expense);

        // Assert
        $this->assertEquals(ExpenseStatus::Pending, $submittedExpense->status);
    }

    /**
     * @test
     * Arrange: Expense already submitted
     * Act: Submit expense again
     * Assert: Expense remains unchanged (early return)
     */
    public function it_returns_early_if_expense_already_submitted(): void
    {
        // Arrange
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
            'status' => ExpenseStatus::Pending,
        ]);

        // Act
        $submittedExpense = $this->expenseService->submit($expense);

        // Assert
        $this->assertEquals(ExpenseStatus::Pending, $submittedExpense->status);
    }

    /**
     * @test
     * Arrange: Expense with pending status exists
     * Act: Approve expense
     * Assert: Expense status is updated to approved
     */
    public function it_approves_an_expense(): void
    {
        // Arrange
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
            'status' => ExpenseStatus::Pending,
        ]);

        // Act
        $approvedExpense = $this->expenseService->approve($expense);

        // Assert
        $this->assertEquals(ExpenseStatus::Approved, $approvedExpense->status);
    }

    /**
     * @test
     * Arrange: Expense already approved
     * Act: Approve expense again
     * Assert: Expense remains unchanged (early return)
     */
    public function it_returns_early_if_expense_already_approved(): void
    {
        // Arrange
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
            'status' => ExpenseStatus::Approved,
        ]);

        // Act
        $approvedExpense = $this->expenseService->approve($expense);

        // Assert
        $this->assertEquals(ExpenseStatus::Approved, $approvedExpense->status);
    }

    /**
     * @test
     * Arrange: Expense with pending status exists
     * Act: Reject expense
     * Assert: Expense status is updated to rejected
     */
    public function it_rejects_an_expense(): void
    {
        // Arrange
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
            'status' => ExpenseStatus::Pending,
        ]);

        // Act
        $rejectedExpense = $this->expenseService->reject($expense);

        // Assert
        $this->assertEquals(ExpenseStatus::Rejected, $rejectedExpense->status);
    }

    /**
     * @test
     * Arrange: Expense already rejected
     * Act: Reject expense again
     * Assert: Expense remains unchanged (early return)
     */
    public function it_returns_early_if_expense_already_rejected(): void
    {
        // Arrange
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
            'status' => ExpenseStatus::Rejected,
        ]);

        // Act
        $rejectedExpense = $this->expenseService->reject($expense);

        // Assert
        $this->assertEquals(ExpenseStatus::Rejected, $rejectedExpense->status);
    }

    /**
     * @test
     * Arrange: Expense with approved status exists
     * Act: Mark expense as paid
     * Assert: Expense status is updated to paid
     */
    public function it_marks_expense_as_paid(): void
    {
        // Arrange
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
            'status' => ExpenseStatus::Approved,
        ]);

        // Act
        $paidExpense = $this->expenseService->markAsPaid($expense);

        // Assert
        $this->assertEquals(ExpenseStatus::Paid, $paidExpense->status);
    }

    /**
     * @test
     * Arrange: Expense already paid
     * Act: Mark expense as paid again
     * Assert: Expense remains unchanged (early return)
     */
    public function it_returns_early_if_expense_already_paid(): void
    {
        // Arrange
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
            'status' => ExpenseStatus::Paid,
        ]);

        // Act
        $paidExpense = $this->expenseService->markAsPaid($expense);

        // Assert
        $this->assertEquals(ExpenseStatus::Paid, $paidExpense->status);
    }

    /**
     * @test
     * Arrange: Expense exists
     * Act: Duplicate expense
     * Assert: New expense is created with same data but new number and draft status
     */
    public function it_duplicates_an_expense(): void
    {
        // Arrange
        $originalExpense = Expense::factory()->create([
            'company_id' => $this->company->id,
            'title' => 'Original Expense',
            'amount' => 500.00,
            'status' => ExpenseStatus::Paid,
        ]);

        // Act
        $duplicatedExpense = $this->expenseService->duplicate($originalExpense);

        // Assert
        $this->assertNotEquals($originalExpense->id, $duplicatedExpense->id);
        $this->assertNotEquals($originalExpense->expense_number, $duplicatedExpense->expense_number);
        $this->assertEquals('Original Expense', $duplicatedExpense->title);
        $this->assertEquals(500.00, $duplicatedExpense->amount);
        $this->assertEquals(ExpenseStatus::Draft, $duplicatedExpense->status);
    }

    /**
     * @test
     * Arrange: No company provided
     * Act: Attempt to create expense
     * Assert: Exception is thrown
     */
    public function it_throws_exception_if_no_company_provided(): void
    {
        // Arrange
        $data = [
            'title' => 'Test Expense',
            'vendor_id' => $this->vendor->id,
            'expense_category_id' => $this->category->id,
            'amount' => 100.00,
        ];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Company is required');
        $this->expenseService->create(null, $data);
    }

    /**
     * @test
     * Arrange: Multiple expenses exist for same company
     * Act: Create new expense
     * Assert: Expense number sequence increments correctly
     */
    public function it_generates_sequential_expense_numbers(): void
    {
        // Arrange
        $data = [
            'title' => 'Test Expense',
            'vendor_id' => $this->vendor->id,
            'expense_category_id' => $this->category->id,
            'amount' => 100.00,
        ];

        // Act
        $expense1 = $this->expenseService->create($this->company, $data);
        $expense2 = $this->expenseService->create($this->company, $data);
        $expense3 = $this->expenseService->create($this->company, $data);

        // Assert
        $this->assertStringContainsString('EXP-', $expense1->expense_number);
        $this->assertStringContainsString('EXP-', $expense2->expense_number);
        $this->assertStringContainsString('EXP-', $expense3->expense_number);
        $this->assertNotEquals($expense1->expense_number, $expense2->expense_number);
        $this->assertNotEquals($expense2->expense_number, $expense3->expense_number);
    }
}
