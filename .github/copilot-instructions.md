# Development Guidelines

## Programming Principles

### SOLID Principles
- **Single Responsibility**: Each class should have one reason to change
- **Open/Closed**: Classes should be open for extension but closed for modification
- **Liskov Substitution**: Derived classes must be substitutable for their base classes
- **Interface Segregation**: Clients should not depend on interfaces they don't use
- **Dependency Inversion**: Depend on abstractions, not concretions

### DRY (Don't Repeat Yourself)
- Extract common functionality into reusable methods/classes
- Use inheritance and composition appropriately
- Leverage Laravel's built-in features and helpers

### Early Returns
- Return early to reduce nesting and improve readability
- Check for error conditions first
- Avoid deep if/else nesting

### Dynamic Programming
- Use dynamic solutions where applicable
- Leverage PHP's dynamic features appropriately
- Optimize recursive solutions with memoization

## Code Style

### PHP
- Follow PSR-12 coding standards
- Use type hints for parameters and return types
- Prefer strict comparisons (=== over ==)
- Use null coalescing operator (??) and null-safe operator (?->)

### Laravel Conventions
- Use Eloquent relationships over manual queries
- Leverage service providers for application setup
- Use form requests for validation
- Follow repository pattern for complex data access

### Filament Best Practices
- Use Filament components for consistent UI
- Leverage theme variables instead of hard-coded colors
- Implement proper authorization with policies
- Use resource pages for CRUD operations

## Database

### Migrations
- Never use `timestamps()` - we handle timestamps manually
- All models set `public $timestamps = false`
- Date fields use `*_at` suffix (issued_at, expires_at, started_at, ended_at, due_at, incurred_at)
- Boolean fields use `is_*` prefix (is_primary, is_active, is_billable)
- All major resources have `*_number` fields for tracking (invoice_number, project_number, task_number, etc.)
- Status fields use enum defaults: `Enum::case->value`

## Current Implementation Status

### ✅ Completed Resources (with Full Stack)

#### Invoice Resource
- [x] Database: invoice_number, status enum, payments relationship
- [x] Model: InvoiceStatus enum cast, all relationships
- [x] Service: InvoiceService with CRUD + markAsSent, markAsPaid, duplicate, calculateTotals
- [x] Observer: InvoiceObserver for lifecycle management
- [x] Filament: Full resource with separate Schema/Tables classes, modals
- [x] Tests: 12 comprehensive tests with it_* naming and AAA pattern
- [x] Translations: lang/en/invoices.php with all labels

#### Project Resource
- [x] Database: project_number, status enum, tasks relationship
- [x] Model: ProjectStatus enum cast, all relationships
- [x] Service: ProjectService with CRUD + markAsCompleted, putOnHold, cancel, duplicate, calculateCompletionPercentage
- [x] Observer: ProjectObserver for lifecycle management
- [x] Filament: Full resource with separate Schema/Tables classes, modals
- [x] Tests: 14 comprehensive tests with it_* naming and AAA pattern
- [x] Translations: lang/en/projects.php with all labels

### 🔄 Partially Completed Resources

#### Task Resource
- [x] Database: task_number, status enum, timesheets relationship
- [x] Model: TaskStatus enum cast, timesheets() relationship
- [x] Factories: Updated to use enums
- [ ] Service: TaskService needed
- [ ] Observer: TaskObserver needed
- [ ] Filament: Resource needed
- [ ] Tests: Comprehensive tests needed

#### Expense Resource
- [x] Database: expense_number, status enum, vendor_id, expense_category_id
- [x] Model: ExpenseStatus enum cast, vendor(), expenseCategory() relationships
- [x] Factories: Updated to use enums and foreign keys
- [ ] Service: ExpenseService needed
- [ ] Observer: ExpenseObserver needed
- [ ] Filament: Resource needed
- [ ] Tests: Comprehensive tests needed

#### Quote Resource
- [x] Database: quote_number, status enum, prospect_id
- [x] Model: QuoteStatus enum cast, prospect() relationship
- [x] Factories: Updated to use enums
- [ ] Service: QuoteService needed
- [ ] Observer: QuoteObserver needed
- [ ] Filament: Resource needed
- [ ] Tests: Comprehensive tests needed

### 📋 Pending Resources

#### Company Resource
- [x] Database: company_number, communicatables, addresses
- [x] Model: All relationships defined
- [ ] Service: CompanyService needed
- [ ] Observer: CompanyObserver needed
- [ ] Filament: Resource needed (already has workspace selection UI)
- [ ] Tests: Comprehensive tests needed

#### Relation Resource
- [x] Database: relation_number, RelationType enum (Client, Supplier, Partner, Contractor, Vendor, Prospect)
- [x] Model: All relationships, communicatables, addresses
- [ ] Service: RelationService needed
- [ ] Observer: RelationObserver needed
- [ ] Filament: Resource needed
- [ ] Tests: Comprehensive tests needed

#### Client Resource (Pivot)
- [x] Database: client_number, company-relation pivot
- [x] Model: All relationships
- [ ] Service: ClientService needed
- [ ] Filament: Resource needed
- [ ] Tests: Comprehensive tests needed

## VAT System Preparation

Preparing for VAT systems from:
- UAE, Portugal, Brazil, Spain, Italy, France, Germany, Belgium, Netherlands, USA

Structure needed:
- [ ] VatRate model/table with country-specific rates
- [ ] VatRuleEnum for different VAT calculation rules
- [ ] Service class for VAT calculations per country
- [ ] Migration: vat_rates table with country, rate, effective_from, effective_to
- [ ] Tests for multi-country VAT calculations
- Never use `fillable` arrays - use `Model::unguard()`
- Use descriptive field names with `_at` suffix for datetime fields
- Use foreign keys with proper cascade rules
- Create indexes for frequently queried columns

### Models
- Set `public $timestamps = false` on all models
- Use PHP enums for type-safe constants
- Define relationships explicitly
- Use accessors and mutators for data transformation
- Implement early returns in methods

### Relationships
- Prefer many-to-many with pivot tables over direct foreign keys
- Use polymorphic relationships for flexible associations
- Always define inverse relationships

## Multi-Tenancy
- Use tenant-aware queries throughout the application
- Scope all queries to current company context
- Use Filament's tenant system for panel isolation
- Store tenant context in session

## Internationalization
- All user-facing text must use `trans()` function
- Create separate language files per feature/module
- Support multiple locales: en, pt-BR, de, fr, nl, es, it, ar

## Testing
- Write PHPUnit tests for all new features
- Test both happy path and edge cases
- Use factories for test data generation
- Mock external dependencies
- Test multi-tenant isolation

## Security
- Use Spatie Permission for authorization
- Implement proper CSRF protection
- Sanitize user input
- Use parameterized queries
- Validate all incoming data
