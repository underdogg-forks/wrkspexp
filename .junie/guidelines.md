# Project Guidelines - WRKSPEXP

## Architecture
- Laravel 12 + Filament 4 + TailwindCSS 4
- Multi-tenancy with company-based isolation
- Nord theme for Filament UI

## Code Standards

### SOLID Principles
Apply SOLID principles throughout the codebase:
- Single Responsibility
- Open/Closed
- Liskov Substitution
- Interface Segregation
- Dependency Inversion

### DRY (Don't Repeat Yourself)
- Extract reusable logic into services/traits
- Use Laravel helpers and built-in features
- Avoid code duplication

### Early Returns
- Return early to avoid nested conditions
- Check error states first
- Keep happy path at the lowest indentation level

### Dynamic Programming
- Use memoization for recursive solutions
- Leverage PHP's dynamic features appropriately
- Optimize performance-critical operations

## Database Conventions

### Timestamps
- **Never** use Laravel's `timestamps()` in migrations
- Set `public $timestamps = false` in all models
- Handle timestamps manually if needed

### Mass Assignment
- **Never** use `$fillable` or `$guarded` in models
- Use `Model::unguard()` globally in AppServiceProvider

### Field Naming
- Date/time fields: Use `*_at` suffix (e.g., `issued_at`, `expires_at`, `started_at`, `ended_at`)
- Never use `*_date` suffix
- Boolean fields: Use `is_*` or `has_*` prefix

### Enums
- Use PHP enums for all type constants
- Status fields must use enums (InvoiceStatus, ProjectStatus, TaskStatus, ExpenseStatus, etc.)
- All enums must have a `label()` method for display purposes
- All enums must have a `color()` method for badge coloring in Filament tables
- Use the `color()` method in BadgeColumn: `->colors(fn (StatusEnum $state): string => $state->color())`

Example enum structure:
```php
enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Paid = 'paid';
    
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Paid => 'Paid',
        };
    }
    
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::Sent => 'info',
            self::Paid => 'success',
        };
    }
}
```

## Filament V4 Resources

### Structure
Each Filament resource must follow this structure:
```
app/Filament/Company/Resources/{EntityName}/
├── {EntityName}Resource.php          # Main resource class
├── Pages/
│   ├── List{EntityName}.php         # List page with table
│   ├── Create{EntityName}.php       # Create page (modal or full page)
│   └── Edit{EntityName}.php         # Edit page (modal or full page)
├── Schemas/
│   └── {EntityName}Form.php         # Form schema configuration
└── Tables/
    └── {EntityName}Table.php        # Table configuration
```

### Service Classes
- All business logic must reside in service classes under `app/Services/`
- Service classes handle: create, update, status changes, duplication, calculations
- Use early returns for validation
- Wrap operations in DB transactions where appropriate

### Observers
- Model observers handle lifecycle events (creating, created, updating, updated, etc.)
- Keep observers focused on side effects (logging, notifications, event triggering)
- Validate state transitions in observers
- Use observers for auto-generating `*_number` fields (e.g., invoice_number, project_number)
- Observers should handle number generation in the `creating` event before save

Example observer usage for number generation:
```php
public function creating(Project $project): void
{
    if (empty($project->project_number)) {
        $project->project_number = $this->generateProjectNumber();
    }
}
```

### Filament Form Fields
- Use `createOptionForm()` on Select fields to allow inline creation of related records
- Improves UX by avoiding navigation away from current form

Example:
```php
Select::make('client_id')
    ->relationship('client', 'name')
    ->createOptionForm([
        TextInput::make('name')->required(),
    ]),
```

## Testing Standards

### PHPUnit Tests
- All test methods start with `it_` prefix
- Use descriptive names that read as plain English sentences
- Follow AAA (Arrange, Act, Assert) pattern
- Add PHPDoc comments above each test scenario section:
  ```php
  /**
   * @test
   * Arrange: Context and preconditions
   * Act: Action being tested
   * Assert: Expected outcome
   */
  ```

## Resource Implementation Checklist

Per resource, ensure the following are completed:

### Database Layer
- [ ] Migration with `*_number` field
- [ ] Status enum with default value
- [ ] No `notes` or `description` fields (use polymorphic notes_descriptions)
- [ ] All date fields use `*_at` naming
- [ ] Boolean fields use `is_*` naming

### Model Layer
- [ ] Model with relationships (alphabetized)
- [ ] Enum casts for status fields
- [ ] `public $timestamps = false`
- [ ] No `$fillable` or `$guarded`
- [ ] Relationships to notes_descriptions, addresses, communicatables

### Service Layer
- [ ] Service class created
- [ ] CRUD methods (create, update)
- [ ] Business logic methods (markAs*, calculate*, duplicate)
- [ ] Early returns for validation
- [ ] DB transactions for multi-step operations

### Observer Layer
- [ ] Observer created
- [ ] Lifecycle event handlers
- [ ] Status transition validation
- [ ] Side effect handling (notifications, logging)

### Filament Layer
- [ ] Resource class with navigation config
- [ ] Form schema with trans() for all labels
- [ ] Table configuration with filters, actions
- [ ] List/Create/Edit pages
- [ ] Modal configuration (`modalWidth`, `modalHeading`)
- [ ] Integration with service class

### Testing Layer
- [ ] Service tests with `it_*` naming
- [ ] AAA pattern with PHPDoc comments
- [ ] Tests for: create, update, status changes, duplication, calculations
- [ ] Edge case tests (already in status, early returns, validations)
- [ ] Factory for test data generation

### Translation Layer
- [ ] Translation file (e.g., `lang/en/{resource}.php`)
- [ ] All UI text uses trans() function
- [ ] Singular/plural labels
- [ ] Field labels, actions, statuses
- **Never** use database enum columns
- **Never** use JSON columns
- Store enum values as strings in VARCHAR fields

## Multi-Tenancy

### Relations (formerly Clients)
- Relations can be: clients, suppliers, partners, contractors
- Relations belong to multiple companies (many-to-many)
- Each company-relation relationship has a `relation_type`
- Use pivot table `company_relation` for the relationship

### Communicatables
- Email, phone, and other contact methods are stored in `communicatables` table
- Polymorphic relationship: can belong to Company, Relation, etc.
- Each type can have multiple entries
- Use `is_primary` flag to mark primary contact method per type

### Addresses
- Multiple addresses per entity (Company, Relation, etc.)
- Polymorphic relationship via `addressable_type` and `addressable_id`
- Fields: address_line_1, address_line_2, city, state, postal_code, zip_code, country
- Use `is_primary` flag to mark primary address

### Roles
- Use Spatie Permission for role management
- Roles are stored in `roles` table (from Spatie)
- `company_user` pivot uses `role_id` foreign key
- Integrate with Filament Shield for panel-level authorization

## Internationalization
- All user-facing text **must** use `trans()` function
- Supported languages: en, pt-BR, de, fr, nl, es, it, ar
- Create language files per module/feature
- Format: `lang/{locale}/{module}.php`

## UI/UX

### Filament Components
- **Always** use Filament components for consistent UI
- **Never** hard-code colors (e.g., `bg-white`, `text-gray-900`)
- Use theme-aware classes and Filament's color system
- Leverage `x-filament::*` components

### Blade Directives
- Use `@forelse` instead of `@foreach` when handling empty states
- Provide meaningful empty state messages
- Use Filament's section/card components for grouping

## Testing
- Write PHPUnit tests for all new features
- **Do not run tests** automatically in CI (as per user preference)
- Test multi-tenant isolation
- Use factories for test data
- Mock external dependencies

## Project Structure
```
app/
├── Enums/          # PHP enums for type safety
├── Models/         # Eloquent models (no fillable, no timestamps)
├── Filament/
│   ├── Workspace/  # Workspace panel (company selection)
│   ├── Company/    # Tenant-aware company panel
│   └── Resources/  # Filament resources
└── Providers/
    └── Filament/   # Panel providers

database/
├── migrations/     # Database migrations
├── factories/      # Model factories
└── seeders/        # Database seeders

lang/
└── {locale}/       # Translation files per locale
```

## Git Workflow
- Make small, focused commits
- Write descriptive commit messages
- Follow conventional commits format
- Review all changes before committing

## Tenant Scoping

### Company Context
- All resources must be scoped to the current company (tenant)
- Add `company_id` foreign key to main entity tables (invoices, projects, etc.)
- Use global scopes for automatic tenant filtering
- Always validate user has access to tenant before operations

### Payment Model Example
```php
protected static function booted(): void
{
    static::addGlobalScope('company', function (Builder $builder) {
        if (filament()->hasTenancy() && filament()->getTenant()) {
            $builder->whereHas('invoice.client', function ($query) {
                $query->where('company_id', filament()->getTenant()->id);
            });
        }
    });
}
```

### Return Type Hints
- All methods should have explicit return type hints
- Use `Collection`, `Model`, `void`, `bool`, `int`, `string`, etc.
- Example: `public function getCompanies(): Collection`

## Factory Best Practices

### State Methods
- Add state methods for common variations
- Example: `inactive()`, `draft()`, `paid()`
```php
public function inactive(): static
{
    return $this->state(fn (array $attributes) => [
        'is_active' => false,
    ]);
}
```

### Logical Dates
- Generate dates in logical order (issued_at before expires_at)
- Use relative date generation
```php
$issuedAt = fake()->dateTimeBetween('-6 months', 'now');
$expiresAt = fake()->dateTimeBetween($issuedAt, '+60 days from ' . $issuedAt->format('Y-m-d'));
```

### Unique Fields
- Generate unique values for `*_number` fields
- Use consistent prefixes (INV-, PRJ-, C-, etc.)
```php
'invoice_number' => 'INV-' . fake()->unique()->numberBetween(10000, 99999),
'client_number' => 'C-' . fake()->unique()->numberBetween(100000, 999999),
```

### Foreign Keys from Related Models
- When a field depends on related model, create related first
```php
$client = Client::factory()->create();
return [
    'client_id' => $client->id,
    'company_id' => $client->company_id, // Inherit from client
];
```

## Payment Methods
- Use PaymentMethod enum for type safety
- Enum includes: Cash, BankTransfer, CreditCard, DebitCard, Check, PayPal, Stripe, Other
- Each enum case has `label()` and `color()` methods

