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
