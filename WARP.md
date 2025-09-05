# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Commands

### Backend (PHP 8.3+ Slim + Eloquent + SQLite/PostgreSQL)

```bash
# Start development server (from backend directory)
cd backend
php -S localhost:8080 -t src src/index.php

# Install dependencies
composer install

# Run tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Integration
./vendor/bin/phpunit --testsuite E2E

# Static analysis
./vendor/bin/phpstan analyse src

# Code standards check
./vendor/bin/phpcs src

# Database setup
php src/scripts/migrate_schema.php

# Create admin user
php src/scripts/create_admin.php

# Generate OpenAPI docs
php generate-openapi.php

# Docker build
docker build -t calcfolio-backend .

# Run with Docker
docker run -p 8080:8080 calcfolio-backend
```

### Frontend (Nuxt 4 + Vue 3 + TypeScript + Tailwind v4)

```bash
# Package manager: Bun (NOT npm/yarn)
cd frontend

# Install dependencies
bun install

# Start development server
bun run dev

# Build for production
bun run build

# Generate static site
bun run generate

# Preview production build
bun run preview

# Run tests
npx vitest
# or
npx vitest run

# Watch tests
npx vitest watch

# Run specific test file
npx vitest components/HeroSection.spec.ts
```

## Architecture Overview

### Backend Structure (Domain-Driven Design)

The backend follows DDD principles with clear layer separation:

```
backend/src/
├── Application/         # Application logic layer
│   ├── Controllers/     # HTTP controllers (Contact, Admin, Webhook)
│   ├── Services/        # Business services (Email, reCAPTCHA)
│   └── Validators/      # Input validation rules
├── Domain/              # Business domain layer
│   ├── Entities/        # Core domain entities
│   ├── Interfaces/      # Domain contracts
│   └── ValueObjects/    # Immutable value objects
├── Infrastructure/      # External services layer
│   ├── Database/        # Database setup and migrations
│   └── External/        # Third-party integrations (Resend)
├── Presentation/        # Presentation layer
│   ├── Middleware/      # HTTP middleware (CORS, Auth)
│   ├── Handlers/        # Error handlers
│   └── OpenAPI/         # API documentation
├── Models/              # Eloquent ORM models
│   ├── Admin.php        # Admin authentication model
│   ├── Contact.php      # Contact messages model
│   └── EventLog.php     # Event logging model
├── config/              # Configuration
│   ├── container.php    # DI container setup
│   └── database.php     # Database configuration
├── scripts/             # Utility scripts
│   ├── migrate_schema.php  # Database migrations
│   └── create_admin.php    # Admin user creation
└── index.php            # Application entry point
```

Key backend patterns:
- Dependency injection via PHP-DI container
- Repository pattern with Eloquent ORM
- Service layer for business logic
- Middleware for cross-cutting concerns
- OpenAPI/Swagger for API documentation

### Frontend Structure (Nuxt.js 4)

```
frontend/
├── pages/               # File-based routing
│   ├── index.vue        # Homepage
│   ├── contact.vue      # Contact form page
│   └── admin/           # Admin section
│       ├── login.vue    # Admin login
│       └── dashboard.vue # Admin dashboard
├── components/          # Vue components
│   ├── HeroSection.vue  # Landing hero
│   ├── ProjectSection.vue # Portfolio projects
│   ├── SkillsSection.vue # Skills visualization
│   ├── TestimonialsSection.vue # Client testimonials
│   ├── ExperienceTimeline.vue # Work experience
│   └── admin/           # Admin-specific components
├── composables/         # Composition API utilities
├── layouts/             # Page layouts
│   ├── default.vue      # Public layout
│   └── admin.vue        # Admin layout
├── plugins/             # Nuxt plugins
│   └── recaptcha.client.ts # reCAPTCHA v3 setup
├── middleware/          # Route middleware
│   └── auth.ts          # Authentication guard
├── public/              # Static assets
│   └── favicon/         # Favicon files
├── server/              # Server-side code
│   └── api/             # Server API routes
└── nuxt.config.ts       # Nuxt configuration
```

### Key Integration Points

1. **API Communication**
   - Frontend calls backend at `NUXT_PUBLIC_BACKEND_URL`
   - CORS configured for cross-origin requests
   - Session-based admin authentication

2. **Email System**
   - Resend API for transactional emails
   - Webhook endpoint for delivery tracking
   - Admin notifications for new messages

3. **Security**
   - Google reCAPTCHA v3 for spam prevention
   - Secure session management
   - CSRF protection
   - Input validation on both ends

4. **Database**
   - SQLite for local development (`contact.db`)
   - PostgreSQL for production
   - Eloquent ORM for data access
   - Migration scripts for schema setup

### Environment Configuration

Backend `.env` requirements:
```
APP_ENV=development
RESEND_API_KEY=your_key
RESEND_WEBHOOK_SECRET=your_secret
FROM_EMAIL=noreply@domain.com
ADMIN_EMAIL=admin@domain.com
RECAPTCHA_V3_SECRET_KEY=your_key
DATABASE_URL=postgresql://... (production only)
```

Frontend `.env` requirements:
```
NUXT_PUBLIC_BACKEND_URL=http://localhost:8080
NUXT_PUBLIC_RECAPTCHA_SITE_KEY=your_key
NUXT_PUBLIC_SITE_URL=https://yourdomain.com
```

## Development Workflow

1. **Initial Setup**
   ```bash
   # Backend
   cd backend
   composer install
   cp .env.example .env
   # Edit .env with your keys
   php src/scripts/migrate_schema.php
   php src/scripts/create_admin.php

   # Frontend
   cd ../frontend
   bun install
   cp .env.example .env
   # Edit .env with your keys
   ```

2. **Running Development**
   ```bash
   # Terminal 1 - Backend
   cd backend
   php -S localhost:8080 -t src src/index.php

   # Terminal 2 - Frontend
   cd frontend
   bun run dev
   ```

3. **Access Points**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8080
   - API Docs: http://localhost:8080/docs.html
   - Admin Panel: http://localhost:3000/admin

## Important Notes

- Frontend uses **Bun** package manager (not npm/yarn)
- Backend requires **PHP 8.3+** with extensions: pdo_sqlite, pdo_pgsql, curl
- Both services must run simultaneously for full functionality
- API documentation auto-generated from OpenAPI annotations
- Database migrations must be run before first use
- Admin credentials are set during `create_admin.php` execution
- Production deployments: Backend on Railway, Frontend on Vercel
