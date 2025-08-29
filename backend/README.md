# Portfolio Backend API

The backend API for my personal portfolio website, built with PHP 8.3 and Slim Framework 4. This RESTful API handles contact form submissions, admin authentication, message management, and email notifications using Resend.

## 🚀 Features

- **RESTful API**: Clean REST endpoints for contact forms and admin operations
- **Admin Dashboard**: Secure authentication and message management interface
- **Email Integration**: Automated email notifications via Resend API
- **Database Support**: PostgreSQL with SQLite fallback for development
- **Security**: reCAPTCHA v3 protection, CORS middleware, input validation
- **Documentation**: Auto-generated OpenAPI/Swagger documentation
- **Docker Support**: Containerized deployment with multi-stage builds
- **Clean Architecture**: Domain-Driven Design with separation of concerns
- **Webhook Handling**: Resend webhook integration for email status updates

## 🛠️ Tech Stack

- **Language**: PHP 8.3
- **Framework**: Slim Framework 4 with PSR-7
- **ORM**: Eloquent ORM (Laravel/Illuminate)
- **Database**: PostgreSQL (production) / SQLite (development)
- **Email Service**: Resend API with webhooks
- **Security**: Google reCAPTCHA v3
- **Dependency Injection**: PHP-DI
- **Validation**: Respect/Validation
- **Documentation**: Swagger-PHP with OpenAPI 3.0
- **Containerization**: Docker
- **Deployment**: Railway (production)

## 📦 Installation

### Prerequisites

- PHP 8.3+
- Composer
- PostgreSQL (production) or SQLite (development)
- Docker (optional, for containerized deployment)

### Setup

1. **Clone the repository** (if not already done):
   ```bash
   git clone <repository-url>
   cd backend
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Environment Configuration**:
   ```bash
   cp .env.example .env
   ```

   Configure the following environment variables in `.env`:
   ```env
   # Resend Email Configuration
   RESEND_API_KEY=re_your_resend_api_key_here
   RESEND_WEBHOOK_SECRET=whsec_your_webhook_secret_here
   FROM_EMAIL=noreply@yourdomain.com
   ADMIN_EMAIL=admin@yourdomain.com

   # Database Configuration
   DATABASE_URL=postgresql://user:password@localhost:5432/portfolio_db

   # reCAPTCHA Configuration
   RECAPTCHA_V3_SECRET_KEY=YOUR_GOOGLE_RECAPTCHA_SECRET_KEY

   # Application Environment
   APP_ENV=development
   ```

4. **Database Setup**:
   The application automatically creates required tables on startup using the `DatabaseSetupService`.

5. **Generate API Documentation** (optional):
   ```bash
   php generate-openapi.php
   ```

## 🚀 Development Server

### Start Development Server

```bash
# Using PHP built-in server
php -S localhost:8080 -t src src/index.php

# Or using Docker
docker build -t portfolio-backend .
docker run -p 8080:8080 -e PORT=8080 portfolio-backend
```

The API will be available at `http://localhost:8080`

### Development Features

- **Auto-reload**: PHP built-in server automatically reloads on file changes
- **Error Display**: Development mode shows detailed error information
- **Database Auto-setup**: Tables are created automatically on startup
- **CORS Enabled**: Cross-origin requests allowed for frontend development

## 📁 Project Structure

```
backend/
├── src/                          # Application source code
│   ├── Application/              # Application layer (use cases)
│   │   ├── Controllers/          # HTTP controllers
│   │   │   ├── ContactController.php
│   │   │   ├── AdminAuthController.php
│   │   │   ├── AdminController.php
│   │   │   └── WebhookController.php
│   │   └── Services/             # Application services
│   │       └── ContactFormService.php
│   ├── Domain/                   # Domain layer (business logic)
│   │   ├── Entities/             # Domain entities
│   │   ├── ValueObjects/         # Value objects
│   │   └── Repositories/         # Repository interfaces
│   ├── Infrastructure/           # Infrastructure layer
│   │   ├── Database/             # Database implementations
│   │   │   ├── EloquentMessageRepository.php
│   │   │   └── DatabaseSetupService.php
│   │   └── External/             # External service integrations
│   │       ├── ResendEmailService.php
│   │       └── WebhookVerifier.php
│   ├── Presentation/             # Presentation layer
│   │   ├── Middleware/           # HTTP middleware
│   │   │   ├── CorsMiddleware.php
│   │   │   └── AdminAuthMiddleware.php
│   │   └── OpenAPI/              # API documentation
│   ├── Models/                   # Eloquent models
│   ├── config/                   # Configuration files
│   │   └── container.php         # Dependency injection config
│   ├── scripts/                  # Utility scripts
│   └── index.php                 # Application entry point
├── public/                       # Public web assets
│   ├── docs.html                 # Swagger UI documentation
│   ├── openapi.json              # OpenAPI JSON specification
│   └── openapi.yaml              # OpenAPI YAML specification
├── .env                          # Environment variables
├── .env.example                  # Environment template
├── composer.json                 # PHP dependencies
├── Dockerfile                    # Docker configuration
├── generate-openapi.php          # API documentation generator
└── README.md                     # This file
```

## 🔧 Configuration

### Environment Variables

| Variable | Description | Required | Default |
|----------|-------------|----------|---------|
| `RESEND_API_KEY` | Resend API key for email sending | Yes | - |
| `RESEND_WEBHOOK_SECRET` | Resend webhook signature secret | Yes | - |
| `FROM_EMAIL` | Sender email address | Yes | - |
| `ADMIN_EMAIL` | Admin notification email | Yes | - |
| `DATABASE_URL` | PostgreSQL connection string | Yes | - |
| `RECAPTCHA_V3_SECRET_KEY` | Google reCAPTCHA secret key | Yes | - |
| `APP_ENV` | Application environment | No | `development` |
| `PORT` | Server port (Railway) | No | `8080` |

### Database Configuration

The application supports both PostgreSQL (production) and SQLite (development):

**PostgreSQL** (Recommended for production):
```env
DATABASE_URL=postgresql://user:password@host:5432/database
```

**SQLite** (Development fallback):
```env
DATABASE_URL=sqlite:/absolute/path/to/database.db
```

## 🌐 API Endpoints

### Public Endpoints

#### Contact Form
- `POST /contact` - Submit contact form
- `GET /message/{messageId}` - Get message status

#### API Documentation
- `GET /docs.html` - Swagger UI documentation
- `GET /openapi.json` - OpenAPI JSON specification
- `GET /openapi.yaml` - OpenAPI YAML specification

### Admin Endpoints (Protected)

#### Authentication
- `POST /admin/login` - Admin login
- `POST /admin/logout` - Admin logout
- `GET /admin/check` - Check authentication status
- `POST /admin/recover-session` - Recover admin session

#### Message Management
- `GET /admin/messages` - Get paginated messages
- `GET /admin/messages/{id}` - Get specific message
- `PATCH /admin/bulk/messages` - Bulk message actions
- `GET /admin/messages/stats` - Message statistics

### Webhook Endpoints
- `POST /resend-webhook` - Resend email webhook handler

## 🔐 Authentication & Security

### Admin Authentication

The admin system uses session-based authentication with the following features:

- **Secure Sessions**: HTTP-only cookies with SameSite protection
- **Session Management**: Automatic session recovery and validation
- **CSRF Protection**: Built-in CSRF token validation
- **Rate Limiting**: Request rate limiting for security

### Security Features

- **reCAPTCHA v3**: Bot protection for contact forms
- **Input Validation**: Comprehensive input sanitization and validation
- **CORS Protection**: Configurable cross-origin resource sharing
- **SQL Injection Prevention**: Parameterized queries with Eloquent ORM
- **XSS Protection**: Output encoding and content security policies

## 📧 Email Integration

### Resend API Configuration

The application integrates with Resend for email delivery:

- **Transactional Emails**: Contact form submissions and notifications
- **Webhook Integration**: Real-time email status updates
- **Template Support**: HTML and plain text email templates
- **Delivery Tracking**: Bounce, complaint, and delivery notifications

### Email Templates

- **Contact Notification**: Sent to admin when contact form is submitted
- **Auto-Reply**: Optional auto-reply to contact form submitter
- **Status Updates**: Email status notifications via webhooks

## 🗄️ Database Schema

### Tables

#### messages
```sql
CREATE TABLE messages (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    recaptcha_score DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### admin_sessions
```sql
CREATE TABLE admin_sessions (
    id UUID PRIMARY KEY,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL
);
```

#### event_logs
```sql
CREATE TABLE event_logs (
    id UUID PRIMARY KEY,
    event_type VARCHAR(100) NOT NULL,
    payload JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🐳 Docker Deployment

### Build and Run

```bash
# Build the image
docker build -t portfolio-backend .

# Run the container
docker run -p 8080:8080 \
  -e RESEND_API_KEY=your_key \
  -e DATABASE_URL=your_db_url \
  portfolio-backend
```

### Docker Compose (Development)

```yaml
version: '3.8'
services:
  backend:
    build: .
    ports:
      - "8080:8080"
    environment:
      - RESEND_API_KEY=${RESEND_API_KEY}
      - DATABASE_URL=${DATABASE_URL}
    depends_on:
      - postgres

  postgres:
    image: postgres:15
    environment:
      - POSTGRES_DB=portfolio
      - POSTGRES_USER=user
      - POSTGRES_PASSWORD=password
    volumes:
      - postgres_data:/var/lib/postgresql/data

volumes:
  postgres_data:
```

## 🚀 Deployment

### Railway (Recommended)

1. **Connect Repository**:
   - Link your GitHub repository to Railway
   - Railway automatically detects PHP applications

2. **Environment Variables**:
   Set the following environment variables in Railway dashboard:
   ```
   RESEND_API_KEY
   RESEND_WEBHOOK_SECRET
   FROM_EMAIL
   ADMIN_EMAIL
   DATABASE_URL (auto-provided by Railway)
   RECAPTCHA_V3_SECRET_KEY
   APP_ENV=production
   ```

3. **Database Setup**:
   - Railway automatically provisions PostgreSQL
   - Tables are created automatically on first deployment

4. **Domain Configuration**:
   - Configure custom domain in Railway dashboard
   - Update CORS settings for production domain

### Manual Deployment

For other platforms, ensure:

- PHP 8.3+ with required extensions
- PostgreSQL database
- Environment variables configured
- Web server configured (Apache/Nginx)
- SSL certificate for HTTPS

## 📊 Monitoring & Logging

### Application Logs

- **Error Logging**: PHP errors logged to server logs
- **Event Logging**: Application events stored in `event_logs` table
- **Email Logs**: Email delivery status tracked via webhooks

### Health Checks

- **Database Connection**: Automatic database connectivity checks
- **API Health**: Basic health check endpoint available
- **Service Dependencies**: External service availability monitoring

## 🔧 Development Tools

### API Documentation

Generate and view API documentation:

```bash
# Generate OpenAPI specification
php generate-openapi.php

# View documentation
# Visit: http://localhost:8080/docs.html
```

### Code Quality

```bash
# Run PHPStan (if configured)
composer run phpstan

# Run tests (if configured)
composer run test
```

### Database Management

```bash
# Reset database (development only)
php src/scripts/reset-database.php

# Run database migrations (if implemented)
php src/scripts/migrate.php
```

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Failed**:
   - Verify `DATABASE_URL` format
   - Check database server status
   - Ensure proper permissions

2. **Email Not Sending**:
   - Verify Resend API key
   - Check webhook signature secret
   - Review Resend dashboard for errors

3. **CORS Errors**:
   - Update allowed origins in CORS middleware
   - Verify frontend domain configuration

4. **Session Issues**:
   - Check PHP session configuration
   - Verify cookie settings for production

### Debug Mode

Enable debug mode by setting:
```env
APP_ENV=development
```

This enables:
- Detailed error messages
- Request/response logging
- Development-friendly CORS settings

## 🤝 API Usage Examples

### Submit Contact Form

```bash
curl -X POST http://localhost:8080/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "subject": "Project Inquiry",
    "message": "I am interested in your services...",
    "recaptchaToken": "recaptcha_response_token"
  }'
```

### Admin Login

```bash
curl -X POST http://localhost:8080/admin/login \
  -H "Content-Type: application/json" \
  -d '{
    "password": "admin_password"
  }'
```

### Get Messages (Admin)

```bash
curl -X GET http://localhost:8080/admin/messages \
  -H "Cookie: session_token=your_session_token"
```

## 📄 License

This project is private and proprietary.

## 📞 Support

For technical questions about this backend API:

- **Check API Documentation**: Visit `/docs.html` for interactive API docs
- **Review Logs**: Check application and server logs for errors
- **Database Issues**: Verify database connectivity and permissions

---

Built with ❤️ by [Damilola Michael Ige](https://buymeacoffee.com/idmcalculus)