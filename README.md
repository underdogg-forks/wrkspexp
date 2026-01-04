# 🚀 WRKSPEXP - Multi-Tenant Workspace & Expense Management

This is a **Laravel 12 + Filament 4** multi-tenant application for workspace and expense management with company-based isolation, built following **SOLID**, **DRY**, and **Dynamic Programming** principles.

## 🎨 Preview

![Workspace Selection](https://github.com/user-attachments/assets/7897ba33-9c64-40ff-bee9-cd0b16c7ad83)

## ✨ Features

### Multi-Tenancy
- **Company-based isolation**: Each company operates in its own isolated context
- **User workspace**: Users can belong to multiple companies with different roles
- **Seamless switching**: Easy navigation between different company contexts
- **Role-based access**: Powered by Spatie Permissions & Filament Shield

### Core Modules
- **Relations Management**: Flexible entity system (Clients, Suppliers, Partners, Contractors)
- **Invoicing & Quotes**: Full invoicing system with items, taxes, and payment tracking
- **Project Management**: Projects with tasks, time tracking, and deadlines
- **Expense Tracking**: Categorized expense management with client association
- **Product Catalog**: Services and physical products with SKU management
- **Contact Management**: Polymorphic communicatables (emails, phones) and addresses

### Technical Highlights
- ✅ **SOLID Principles**: Clean, maintainable, extensible code
- ✅ **DRY**: Reusable components and logic
- ✅ **Early Returns**: Improved code readability
- ✅ **Theme-Agnostic UI**: Works with any Filament theme (Nord, Reddit, etc.)
- ✅ **Internationalization**: Ready for 10+ languages (en, pt-BR, de, fr, nl, es, it, ar)
- ✅ **Comprehensive Tests**: PHPUnit tests for multi-tenancy and relations
- ✅ **Rich Permissions**: CRUD + duplicate/import/export permissions

## 📦 Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- Database (MySQL, PostgreSQL, or SQLite)

### Quick Start

1️⃣ **Clone the repository**
\`\`\`bash
git clone https://github.com/underdogg-forks/wrkspexp.git
cd wrkspexp
\`\`\`

2️⃣ **Install dependencies**
\`\`\`bash
composer install
npm install
\`\`\`

3️⃣ **Environment setup**
\`\`\`bash
cp .env.example .env
php artisan key:generate
\`\`\`

4️⃣ **Database configuration**

Update your \`.env\` file with database credentials:
\`\`\`env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wrkspexp
DB_USERNAME=root
DB_PASSWORD=
\`\`\`

5️⃣ **Run migrations and seeders**
\`\`\`bash
php artisan migrate --seed
\`\`\`

This will create:
- Default company (slug: \`ivplv2\`)
- Admin user: \`admin@test.com\` / \`password\`
- 12+ users with various roles
- 15+ client relations with contacts and addresses
- 180+ invoices with items
- 150+ quotes with items
- 75+ projects with tasks
- 225+ standalone tasks
- 150+ expenses
- 20 products (services and physical items)
- Comprehensive permission system

6️⃣ **Build assets**
\`\`\`bash
npm run build
\`\`\`

7️⃣ **Start the development server**
\`\`\`bash
php artisan serve
\`\`\`

Visit: \`http://localhost:8000/workspace/login\`

## 🔐 Authentication

### Default Credentials
- **Email**: \`admin@test.com\`
- **Password**: \`password\`

### User Flow
1. **Login** → \`/workspace/login\`
2. **Company Selection** → \`/workspace/workspace\` (shows all companies user has access to)
3. **Company Panel** → \`/company/{slug}\` (tenant-aware interface)

## 🏗️ Architecture

### Database Schema

**Multi-tenancy structure:**
- \`companies\` - Company entities with unique slugs
- \`company_user\` - Pivot with \`role_id\` (Spatie Permissions)
- \`relations\` - Flexible entities (Clients, Suppliers, Partners, Contractors)
- \`company_relation\` - Many-to-many with \`relation_type\`
- \`communicatables\` - Polymorphic contacts (email, phone) with \`is_primary\`
- \`addresses\` - Polymorphic addresses with full fields
- \`clients\` - Pivot between relations and companies
- \`projects\`, \`tasks\`, \`invoices\`, \`quotes\`, \`expenses\` - Core business entities
- \`items\` - Polymorphic line items for invoices/quotes/expenses
- \`products\` - Product catalog with types (Service/Physical)

**Naming Conventions:**
- Date/time fields use \`*_at\` suffix: \`issued_at\`, \`expires_at\`, \`started_at\`, \`ended_at\`, \`due_at\`, \`incurred_at\`
- All datetime type (not date)
- No \`timestamps()\` in migrations
- No \`fillable\` arrays in models
- PHP enums for all type constants

### Filament Panels

**Three-panel architecture:**

1. **Admin Panel** (\`/admin\`) - Original admin panel
2. **Workspace Panel** (\`/workspace\`) - Company selection interface
3. **Company Panel** (\`/company/{slug}\`) - Tenant-aware business operations

### PHP Enums

\`\`\`php
enum ProductType: string {
    case Service = 'service';
    case Physical = 'physical';
}

enum RelationType: string {
    case Client = 'client';
    case Supplier = 'supplier';
    case Partner = 'partner';
    case Contractor = 'contractor';
}

enum UserRole: string {
    case Admin = 'admin';
    case Manager = 'manager';
    case Employee = 'employee';
    case Client = 'client';
}
\`\`\`

## 🔒 Permissions

Comprehensive permission system with:

### Standard CRUD Permissions
- \`view_*\`, \`view_any_*\`, \`create_*\`, \`update_*\`, \`delete_*\`, \`restore_*\`, \`force_delete_*\`

### Special Actions
- \`duplicate_*\` - Copy/clone records
- \`import_*\` - Bulk import functionality
- \`export_*\` - Export to various formats

### Resources Covered
- Users, Companies, Relations, Clients
- Invoices, Quotes, Expenses
- Projects, Tasks, Products, Items
- Roles & Permissions

### Role Hierarchy
1. **Admin**: Full access to everything
2. **Manager**: All except delete/force_delete
3. **Employee**: View, create, update, export
4. **Client**: View-only access

## 🧪 Testing

\`\`\`bash
# Run PHPUnit tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
\`\`\`

**Test Coverage:**
- \`MultiTenancyTest.php\` - Tenant isolation and access control (5 tests)
- \`RelationTest.php\` - Relations, communicatables, addresses (6 tests)

## 🛠️ Development

### Code Quality

\`\`\`bash
# Run all checks (tests, PHPStan, Pint)
composer check

# PHPStan static analysis
./vendor/bin/phpstan analyse

# Laravel Pint code formatting
./vendor/bin/pint
\`\`\`

### Seeder Configuration

The seeder creates realistic test data:
- **12+ users** per company with various roles
- **15+ relations** (clients) with contacts
- **12-20 invoices** per client (180-300 total)
- **10-15 quotes** per client (150-225 total)
- **5-8 projects** per client with tasks
- **15-25 standalone tasks** per client
- **10-20 expenses** per client

### Factory Usage

\`\`\`php
// Create a company with users
$company = Company::factory()
    ->hasUsers(5)
    ->create();

// Create a relation with communicatables
$relation = Relation::factory()
    ->has(Communicatable::factory()->count(2), 'communicatables')
    ->create();

// Create an invoice with items
$invoice = Invoice::factory()
    ->hasItems(5)
    ->create();
\`\`\`

## 📚 Documentation

- **\`.junie\`** - Comprehensive project guidelines
- **\`.github/DEVELOPMENT_GUIDELINES.md\`** - Coding standards and conventions
- **In-code documentation** - PHPDoc comments throughout

## 🌍 Internationalization

All user-facing text uses the \`trans()\` function:

\`\`\`php
{{ trans('workspace.open_workspace') }}
\`\`\`

**Supported Languages:**
- English (en)
- Portuguese Brazil (pt-BR)
- German (de)
- French (fr)
- Dutch (nl)
- Spanish (es)
- Italian (it)
- Arabic (ar)

Translation files: \`lang/{locale}/{module}.php\`

## 🎨 Theming

The UI is fully theme-agnostic and works with any Filament theme:
- ✅ Nord Theme (default)
- ✅ Reddit Theme
- ✅ Custom themes

No hard-coded colors - all styling uses Filament's theme system.

## 📦 Included Packages

### Filament Plugins
- [Shield](https://filamentphp.com/plugins/bezhansalleh-shield) - Access management
- [Backgrounds](https://filamentphp.com/plugins/swisnl-backgrounds) - Beautiful auth page backgrounds
- [Logger](https://filamentphp.com/plugins/z3d0x-logger) - Activity logging
- [Nord Theme](https://filamentphp.com/plugins/andreia-bohner-nord-theme) - Nord color palette
- [Breezy](https://filamentphp.com/plugins/jeffgreco-breezy) - User profile management

### Development Tools
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debugging toolbar
- [Larastan](https://github.com/larastan/larastan) - PHPStan for Laravel (Level 5)
- [Fast Refresh Database](https://github.com/PlannrCrm/laravel-fast-refresh-database) - Faster test database refresh

## 🚀 Deployment

### Production Checklist

- [ ] Set \`APP_ENV=production\` in \`.env\`
- [ ] Set \`APP_DEBUG=false\` in \`.env\`
- [ ] Configure proper database credentials
- [ ] Run \`php artisan optimize\`
- [ ] Run \`php artisan config:cache\`
- [ ] Run \`php artisan route:cache\`
- [ ] Run \`php artisan view:cache\`
- [ ] Set up proper queue workers
- [ ] Configure scheduled tasks (cron)
- [ ] Set up SSL certificate
- [ ] Configure backup solution

## 📜 License

This project is open-source and licensed under the MIT License.

## 💡 Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Follow coding standards (SOLID, DRY, early returns)
4. Add tests for new features
5. Submit a pull request

## 🙏 Credits

Built with:
- [Laravel 12](https://laravel.com)
- [Filament 4](https://filamentphp.com)
- [TailwindCSS 4](https://tailwindcss.com)
- [Spatie Permissions](https://spatie.be/docs/laravel-permission)

---

### 🚀 Happy Coding! 🎉
