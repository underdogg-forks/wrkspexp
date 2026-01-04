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
