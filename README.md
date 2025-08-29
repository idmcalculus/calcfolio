# Damilola Michael Ige - Personal Portfolio

A modern, full-stack personal portfolio website featuring a PHP backend API and Nuxt.js frontend, designed to showcase my professional work as a software engineer with contact functionality and admin management capabilities.

## 🚀 Overview

This is my personal portfolio website that combines a robust PHP backend with a modern Vue.js frontend. The application showcases my work as a software engineer, including project displays, contact forms, testimonials, and an admin dashboard for managing inquiries.

## ✨ Features

### Backend Features

**Core API**
- RESTful API built with Slim Framework 4
- PSR-7 HTTP message interfaces
- Dependency injection with PHP-DI
- Comprehensive error handling and logging

**Contact Management**
- Secure contact form with reCAPTCHA v3 integration
- Email notifications via Resend API
- Message status tracking (pending, read, replied)
- Admin authentication system
- Webhook verification for email events

**Database & Storage**
- SQLite for development, PostgreSQL for production
- Eloquent ORM for data modeling
- Database migrations and seeding
- File-based configuration management

**Security & Validation**
- Google reCAPTCHA v3 spam protection
- Input validation with Respect/Validation
- CORS middleware for cross-origin requests
- Admin authentication with secure sessions
- Webhook signature verification

**Developer Experience**
- Docker containerization (simple and complex setups)
- OpenAPI/Swagger documentation generation
- Composer dependency management
- Environment-based configuration

### Frontend Features

**Modern UI/UX**
- Built with Nuxt.js 4 and Vue 3
- TypeScript for type safety
- Tailwind CSS for responsive design
- Nuxt UI component library
- Dark mode support
- Smooth animations with AOS (Animate On Scroll)

**SEO & Performance**
- Server-side rendering (SSR)
- Automatic sitemap generation
- Robots.txt configuration
- Schema.org structured data
- Meta tags optimization
- Font optimization with Google Fonts

**Interactive Components**
- Hero section with animated elements
- Project showcase with filtering
- Skills visualization with charts
- Experience timeline
- Testimonials carousel
- Contact form with validation
- CV download functionality
- Mobile-responsive navigation
- Admin dashboard for managing messages

**Admin Dashboard**
- Secure admin authentication
- Message management interface
- Analytics and statistics
- Message status updates
- Responsive admin layout

**Development Tools**
- ESLint for code quality
- Vitest for unit testing
- TypeScript strict mode
- Hot module replacement
- Build optimization

## 🛠 Tech Stack

### Backend
- **Framework**: Slim Framework 4.15
- **Language**: PHP 8.3
- **Database**: SQLite (dev) / PostgreSQL (prod)
- **ORM**: Eloquent 12.26
- **Email**: Resend API
- **Validation**: Respect/Validation 2.4
- **Container**: PHP-DI 7.1
- **Documentation**: OpenAPI/Swagger
- **Testing**: PHPUnit

### Frontend
- **Framework**: Nuxt.js 4.0
- **Language**: TypeScript 5.9
- **UI Library**: Vue 3.5, Nuxt UI 3.3
- **Styling**: Tailwind CSS 4.1
- **Icons**: Nuxt Icon, Lucide Icons
- **Animations**: AOS 2.3
- **Charts**: Chart.js 4.5
- **Testing**: Vitest 3.2
- **Build Tool**: Vite

### DevOps & Deployment
- **Containerization**: Docker
- **Infrastructure**: Railway, Vercel
- **CI/CD**: GitHub Actions
- **Package Management**: Composer (PHP), Bun (JS)

## 📋 Prerequisites

- **PHP**: 8.3 or higher
- **Node.js**: 18+ with Bun package manager
- **Composer**: Latest version
- **Docker**: For containerized deployment
- **PostgreSQL**: For production database (optional)

## 🚀 Installation

### Backend Setup

1. **Clone and navigate to backend directory:**
   ```bash
   cd backend
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Environment configuration:**
   ```bash
   cp .env.example .env
   ```

   Configure the following in `.env`:
   ```env
   APP_ENV=development
   RESEND_API_KEY=your_resend_api_key
   RESEND_WEBHOOK_SECRET=your_webhook_secret
   FROM_EMAIL=noreply@yourdomain.com
   ADMIN_EMAIL=admin@yourdomain.com
   RECAPTCHA_V3_SECRET_KEY=your_recaptcha_secret
   ```

4. **Database setup:**
   ```bash
   # For SQLite (development)
   touch contact.db

   # For PostgreSQL (production)
   # Configure DATABASE_URL in .env
   ```

5. **Start the development server:**
   ```bash
   php -S localhost:8080 -t src src/index.php
   ```

### Frontend Setup

1. **Navigate to frontend directory:**
   ```bash
   cd frontend
   ```

2. **Install dependencies:**
   ```bash
   bun install
   ```

3. **Environment configuration:**
   ```bash
   cp .env.example .env
   ```

   Configure the following in `.env`:
   ```env
   NUXT_PUBLIC_BACKEND_URL=http://localhost:8080
   NUXT_PUBLIC_RECAPTCHA_SITE_KEY=your_recaptcha_site_key
   NUXT_PUBLIC_SITE_URL=https://yourdomain.com
   ```

4. **Start development server:**
   ```bash
   bun run dev
   ```

## 📖 Usage

### Development Workflow

1. **Start both services:**
   ```bash
   # Terminal 1 - Backend
   cd backend && php -S localhost:8080 -t src src/index.php

   # Terminal 2 - Frontend
   cd frontend && bun run dev
   ```

2. **Access the application:**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8080
   - API Documentation: http://localhost:8080/docs.html

### Admin Dashboard

1. **Access admin login:**
   Navigate to `/admin/login`

2. **Default credentials:**
   - Username: admin
   - Password: admin123 (configure in database setup)

3. **Features:**
   - View contact messages
   - Update message status
   - Analytics dashboard
   - Message management

## 📚 API Documentation

The backend provides comprehensive API documentation:

### Endpoints

**Contact Form**
- `POST /api/contact` - Submit contact form
- `GET /api/contact` - List messages (admin only)
- `PUT /api/contact/{id}` - Update message status (admin only)

**Authentication**
- `POST /api/admin/login` - Admin login
- `POST /api/admin/logout` - Admin logout
- `GET /api/admin/verify` - Verify admin session

**Webhooks**
- `POST /api/webhooks/resend` - Handle email webhooks

### OpenAPI Specification

Access the interactive API documentation at `/docs.html` or view the OpenAPI spec at `/openapi.json`.

## 🧪 Testing

### Backend Testing
```bash
cd backend
composer test
```

### Frontend Testing
```bash
cd frontend
bun run test
```

## 🚢 Deployment

### Backend Deployment

**Using Docker:**
```bash
cd backend
docker build -t calcfolio-backend .
docker run -p 8080:8080 calcfolio-backend
```

**Railway Deployment:**
1. Connect GitHub repository
2. Configure environment variables
3. Deploy automatically on push

### Frontend Deployment

**Vercel Deployment:**
```bash
cd frontend
bun run build
```

**Static Generation:**
```bash
bun run generate
```

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 About Me

**Damilola Michael Ige**
- Portfolio: [https://idmcalculus.cv](https://idmcalculus.cv)
- GitHub: [@idmcalculus](https://github.com/idmcalculus)
- LinkedIn: [idmcalculus](https://linkedin.com/in/idmcalculus)
- Email: idm.calculus@gmail.com

## 🙏 Technologies Used

- **Slim Framework** - Robust PHP micro-framework
- **Nuxt.js** - Amazing Vue.js framework
- **Resend** - Reliable email delivery
- **Google reCAPTCHA** - Spam protection
- **Tailwind CSS v4** - Utility-first styling
- **Chart.js** - Data visualization
- **AOS** - Smooth animations

---

Built with ❤️ by [Damilola Michael Ige](https://buymeacoffee.com/idmcalculus)