<?php

require __DIR__ . '/../vendor/autoload.php';

// use Dotenv\Dotenv; // Removed to fix linter error
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Resend;
use App\Services\EmailService;
use App\Services\WebhookVerifier;
use App\Models\Admin; // Add Admin model
use App\Models\Message;
use App\Models\EventLog;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Pagination\Paginator; // Add Paginator
use Illuminate\Support\Facades\DB; // For raw queries if needed
use ReCaptcha\ReCaptcha; // Add ReCaptcha
use ReCaptcha\RequestMethod\CurlPost; // Add ReCaptcha method

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// --- Session Configuration ---
// Ensure sessions use secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Set secure flag if using HTTPS
ini_set('session.use_strict_mode', 1); // Prevent session fixation
session_start(); // Start the session

$app = AppFactory::create();

// (Optional) Add error middleware for debugging
$app->addErrorMiddleware(true, true, true);

// CORS middleware - Allow requests from localhost:3000 (for development)
$app->add(function (Request $request, $handler) {
    $origin = $request->getHeaderLine('Origin');
    $allowedOrigins = ['http://localhost:3000', 'https://calcfolio.com'];
    
    // Handle preflight OPTIONS requests
    if ($request->getMethod() === 'OPTIONS') {
        $response = new \Slim\Psr7\Response();
        if (in_array($origin, $allowedOrigins)) {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                ->withHeader('Access-Control-Max-Age', '3600');
        }
        return $response;
    }
    
    // Handle actual requests
    $response = $handler->handle($request);
    
    if (in_array($origin, $allowedOrigins)) {
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    return $response;
});

// Remove the separate OPTIONS route as it's handled in middleware now

// Initialize Eloquent
$capsule = new Capsule;
$capsule->addConnection(require __DIR__ . '/config/database.php');
$capsule->setAsGlobal();
$capsule->bootEloquent();

// --- Setup Eloquent Pagination ---
// Tell Paginator how to resolve the current page from the request query parameters
Paginator::currentPageResolver(function ($pageName = 'page') {
    // Get query parameters from the current request (assuming $app is accessible or pass request)
    // This part might need adjustment depending on how Slim handles request context here.
    // A simpler approach might be to just read $_GET directly if not in a route handler context.
    return (int) ($_GET[$pageName] ?? 1);
});
// Optional: Configure base path if needed (usually not required for API)
// Paginator::currentPathResolver(function () { return '/admin/messages'; });


// Create tables if they don't exist
if (!Capsule::schema()->hasTable('messages')) {
    Capsule::schema()->create('messages', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('subject');
        $table->text('message');
        $table->string('status')->default(Message::STATUS_PENDING);
        $table->string('message_id')->unique()->nullable(); // Make nullable if needed, or ensure always set
        $table->boolean('is_read')->default(0); // Add is_read column
        $table->timestamps();
    });
}

// Create admins table (redundant if migrate_schema.php was run, but safe)
if (!Capsule::schema()->hasTable('admins')) {
    Capsule::schema()->create('admins', function ($table) {
        $table->id();
        $table->string('username')->unique();
        $table->string('password_hash');
        $table->timestamps();
    });
}

if (!Capsule::schema()->hasTable('event_logs')) {
    Capsule::schema()->create('event_logs', function ($table) {
        $table->id();
        $table->string('event_type');
        $table->json('payload')->nullable();
        $table->timestamp('created_at')->useCurrent();
    });
}

// Log server start
EventLog::create([
    'event_type' => 'script_initialized',
    'payload' => [
        'php_version' => PHP_VERSION,
        'server_time' => date('c'),
        'environment' => $_ENV['APP_ENV'] ?? 'development'
    ]
]);

// --- Helper Function for Authentication Check ---
$isAdminAuthenticated = function () {
    // Regenerate session ID periodically to prevent session fixation
    if (isset($_SESSION['last_regen']) && (time() - $_SESSION['last_regen'] > 1800)) { // e.g., every 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regen'] = time();
    }
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
};

// Contact form endpoint
$app->post('/contact', function (Request $request, Response $response) {
    try {
        $data = json_decode($request->getBody()->getContents(), true);
        $recaptchaSecret = $_ENV['RECAPTCHA_V3_SECRET_KEY'] ?? null;

        // --- reCAPTCHA v3 Verification ---
        if (!$recaptchaSecret) {
             error_log('reCAPTCHA secret key not configured.');
             throw new Exception('Server configuration error.');
        }
        if (!isset($data['recaptcha_token'])) {
            throw new Exception('reCAPTCHA token missing.');
        }

        $recaptcha = new ReCaptcha($recaptchaSecret, new CurlPost());
        $recaptchaResponse = $recaptcha->verify($data['recaptcha_token'], $request->getServerParams()['REMOTE_ADDR'] ?? null);

        if (!$recaptchaResponse->isSuccess() || $recaptchaResponse->getScore() < 0.5) { // Adjust score threshold as needed
            error_log('reCAPTCHA verification failed. Score: ' . $recaptchaResponse->getScore() . ' Errors: ' . implode(', ', $recaptchaResponse->getErrorCodes()));
            throw new Exception('reCAPTCHA verification failed. Are you a bot?');
        }
        // --- End reCAPTCHA Verification ---


        if (!isset($data['name'], $data['email'], $data['subject'], $data['message'])) {
            throw new Exception('Invalid submission data');
        }

        // Sanitize and lowercase email
        $email = strtolower(trim($data['email']));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             throw new Exception('Invalid email format provided.');
        }

        // Generate unique message ID
        $messageId = uniqid('msg_', true);

        // Initialize Resend EmailService
        $emailService = new EmailService();

        try {
            // Send both emails using Resend
            $adminEmailResult = $emailService->sendContactNotification($data, $messageId);
            $autoReplyResult = $emailService->sendAutoReply([
                'name' => $data['name'],
                'email' => $email // Use sanitized email
            ], $messageId);

            // Save to database using Eloquent (use lowercased email)
            Message::create([
                'name' => $data['name'],
                'email' => $email, // Use sanitized email
                'subject' => $data['subject'],
                'message' => $data['message'],
                'message_id' => $messageId
            ]);

            $payload = ['success' => true, 'message' => 'Message received. Thank you!'];
            $response->getBody()->write(json_encode($payload));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            throw new Exception('Email delivery failed: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        error_log('General error: ' . $e->getMessage());
        $payload = [
            'success' => false,
            'message' => 'An error occurred',
            'debug' => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
});

// Add status check endpoint
$app->get('/message/{messageId}', function (Request $request, Response $response, array $args) {
    $message = Message::where('message_id', $args['messageId'])->first();
    
    $response->getBody()->write(json_encode($message ?? ['error' => 'Message not found']));
    return $response->withHeader('Content-Type', 'application/json');
});

// Resend webhook endpoint
$app->post('/resend-webhook', function (Request $request, Response $response) {
    $payload = $request->getBody()->getContents();
    $signature = $request->getHeaderLine('resend-signature');
    
    // Verify webhook signature
    $verifier = new WebhookVerifier();
    if (!$verifier->verify($payload, $signature)) {
        error_log('Resend webhook signature verification failed');
        return $response->withStatus(401);
    }
    
    $event = json_decode($payload, true);
    
    // Log webhook event
    EventLog::create([
        'event_type' => 'resend_webhook',
        'payload' => $event
    ]);
    
    error_log('Resend webhook received: ' . $payload);
    
    // Extract message ID from tags
    $messageId = null;
    if (isset($event['data']['tags'])) {
        foreach ($event['data']['tags'] as $tag) {
            if ($tag['name'] === 'message_id') {
                $messageId = $tag['value'];
                break;
            }
        }
    }
    
    // Process event
    $eventType = $event['type'] ?? '';
    
    error_log("Processing Resend event: $eventType for message: $messageId");
    
    if ($messageId) {
        $status = match($eventType) {
            'email.delivered' => Message::STATUS_DELIVERED,
            'email.bounced' => Message::STATUS_BOUNCED,
            'email.opened' => Message::STATUS_OPENED,
            'email.clicked' => Message::STATUS_CLICKED,
            'email.complained' => Message::STATUS_COMPLAINED,
            default => null
        };

        if ($status) {
            Message::where('message_id', $messageId)
                ->update(['status' => $status]);
        }
    }

    return $response->withStatus(200);
});

// Keep legacy SES webhook for transition (can be removed later)
$app->post('/ses-webhook', function (Request $request, Response $response) {
    error_log('Warning: Legacy SES webhook called - please update to use /resend-webhook');
    return $response->withStatus(410); // Gone - endpoint deprecated
});


// --- Admin Authentication Endpoints ---

// POST /admin/login
$app->post('/admin/login', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);
    // Trim and potentially lowercase username if it's treated like an email
    $usernameInput = trim($data['username'] ?? '');
    // Decide whether to lowercase based on if usernames ARE emails or just strings
    // If they can be mixed case and are not emails, don't lowercase here.
    // Assuming username might be an email for login consistency:
    $username = strtolower($usernameInput);
    $password = $data['password'] ?? null;

    if (!$username || !$password) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Username and password required.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $admin = Admin::where('username', $username)->first();

        if ($admin && password_verify($password, $admin->password_hash)) {
            // Password matches - Log the admin in
            session_regenerate_id(true); // Regenerate ID on login
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin->username;
            $_SESSION['last_regen'] = time(); // Set initial regeneration time

            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Login successful.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            // Invalid credentials
            error_log("Admin login failed for username: {$username}"); // Log failed attempt
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Invalid username or password.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401); // Unauthorized
        }
    } catch (\Exception $e) {
        error_log('Admin login error: ' . $e->getMessage());
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'An internal error occurred during login.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// POST /admin/logout
$app->post('/admin/logout', function (Request $request, Response $response) use ($isAdminAuthenticated) {
    if (!$isAdminAuthenticated()) {
        // Optional: Return error if trying to logout when not logged in
         $response->getBody()->write(json_encode(['success' => false, 'message' => 'Not logged in.']));
         return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Unset all session variables
    $_SESSION = [];

    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();

    $response->getBody()->write(json_encode(['success' => true, 'message' => 'Logout successful.']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

// GET /admin/check (Check authentication status)
$app->get('/admin/check', function (Request $request, Response $response) use ($isAdminAuthenticated) {
    $isAuthenticated = $isAdminAuthenticated();
    $response->getBody()->write(json_encode(['authenticated' => $isAuthenticated]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


// --- Admin Message Management Endpoints (Protected) ---

// GET /admin/messages - List messages with filtering, sorting, pagination, search
$app->get('/admin/messages', function (Request $request, Response $response) use ($isAdminAuthenticated) {
    if (!$isAdminAuthenticated()) {
        $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $params = $request->getQueryParams();

    // Parameters with defaults
    $page = isset($params['page']) ? max(1, (int)$params['page']) : 1;
    $limit = isset($params['limit']) ? max(1, (int)$params['limit']) : 15; // Default 15 per page
    $filterRead = $params['is_read'] ?? null; // '0' for unread, '1' for read, null for all
    $sortBy = $params['sort'] ?? 'created_at'; // Default sort by creation date
    $sortOrder = isset($params['order']) && strtolower($params['order']) === 'asc' ? 'asc' : 'desc'; // Default descending
    $search = $params['search'] ?? null;

    // Validate sort column
    $allowedSortColumns = ['created_at', 'name', 'email', 'subject', 'is_read'];
    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'created_at'; // Default to safe column if invalid sort provided
    }

    try {
        $query = Message::query();

        // Apply filters
        if ($filterRead !== null && in_array($filterRead, ['0', '1'])) {
            $query->where('is_read', '=', (int)$filterRead);
        }

        // Apply search
        if ($search) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                  ->orWhere('email', 'LIKE', $searchTerm)
                  ->orWhere('subject', 'LIKE', $searchTerm)
                  ->orWhere('message', 'LIKE', $searchTerm);
            });
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Apply pagination
        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        $response->getBody()->write(json_encode([
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (\Exception $e) {
        error_log('Error fetching admin messages: ' . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Failed to retrieve messages.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// GET /admin/messages/stats - Get message counts per month
// NOTE: Define static routes like '/stats' before variable routes like '/{id}'
$app->get('/admin/messages/stats', function (Request $request, Response $response) use ($isAdminAuthenticated) {
    if (!$isAdminAuthenticated()) {
        $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    try {
        // Query to get count per year/month - use different syntax for different DB drivers
        $driver = Capsule::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            $stats = Message::selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count")
                            ->groupBy('month')
                            ->orderBy('month', 'asc')
                            ->get();
        } else {
            // PostgreSQL syntax
            $stats = Message::selectRaw("to_char(created_at, 'YYYY-MM') as month, COUNT(*) as count")
                            ->groupBy('month')
                            ->orderBy('month', 'asc')
                            ->get();
        }

        // Format for Chart.js
        $labels = $stats->pluck('month')->map(function ($monthYear) {
            // Format 'YYYY-MM' to 'Mon YYYY' (e.g., 'Apr 2025')
            $date = \DateTime::createFromFormat('Y-m', $monthYear);
            return $date ? $date->format('M Y') : $monthYear;
        })->toArray();
        $data = $stats->pluck('count')->toArray();

        $response->getBody()->write(json_encode([
            'labels' => $labels,
            'data' => $data,
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (\Exception $e) {
        error_log('Error fetching message stats: ' . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Failed to retrieve message statistics.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// GET /admin/messages/{id} - Fetch a single message and mark as read
$app->get('/admin/messages/{id}', function (Request $request, Response $response, array $args) use ($isAdminAuthenticated) {
    if (!$isAdminAuthenticated()) {
        $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $messageId = $args['id'];

    try {
        $message = Message::find($messageId);

        if (!$message) {
            $response->getBody()->write(json_encode(['error' => 'Message not found.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Mark as read if it's not already
        if (!$message->is_read) {
            $message->is_read = true;
            $message->save();
        }

        $response->getBody()->write(json_encode($message));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (\Exception $e) {
        error_log('Error fetching single admin message: ' . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Failed to retrieve message.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// PATCH /admin/messages/bulk - Perform bulk actions (read, unread, delete)
$app->patch('/admin/messages/bulk', function (Request $request, Response $response) use ($isAdminAuthenticated) {
    if (!$isAdminAuthenticated()) {
        $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $data = json_decode($request->getBody()->getContents(), true);
    $action = $data['action'] ?? null;
    $ids = $data['ids'] ?? null;

    if (!in_array($action, ['mark_read', 'mark_unread', 'delete']) || !is_array($ids) || empty($ids)) {
        $response->getBody()->write(json_encode(['error' => 'Invalid action or message IDs provided.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Sanitize IDs to ensure they are integers
    $sanitizedIds = array_filter($ids, 'is_int');
    if (count($sanitizedIds) !== count($ids)) {
         $response->getBody()->write(json_encode(['error' => 'Invalid message IDs provided.']));
         return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $affectedRows = 0;
        switch ($action) {
            case 'mark_read':
                $affectedRows = Message::whereIn('id', $sanitizedIds)->update(['is_read' => true]);
                break;
            case 'mark_unread':
                $affectedRows = Message::whereIn('id', $sanitizedIds)->update(['is_read' => false]);
                break;
            case 'delete':
                $affectedRows = Message::destroy($sanitizedIds); // Eloquent's destroy method
                break;
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => "Action '{$action}' completed.",
            'affected_rows' => $affectedRows
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (\Exception $e) {
        error_log('Error performing bulk action on messages: ' . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Failed to perform bulk action.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


$app->run();
