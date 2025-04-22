<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use App\Models\Message;
use App\Models\EventLog;
use Illuminate\Database\Capsule\Manager as Capsule;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$app = AppFactory::create();

// (Optional) Add error middleware for debugging
$app->addErrorMiddleware(true, true, true);

// Example CORS middleware (if frontend is on a different domain)
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET,POST,OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

// Initialize Eloquent
$capsule = new Capsule;
$capsule->addConnection(require __DIR__ . '/config/database.php');
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Create tables if they don't exist
if (!Capsule::schema()->hasTable('messages')) {
    Capsule::schema()->create('messages', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('subject');
        $table->text('message');
        $table->string('status')->default(Message::STATUS_PENDING);
        $table->string('message_id')->unique();
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

// Contact form endpoint
$app->post('/contact', function (Request $request, Response $response) {
    try {
        $data = json_decode($request->getBody()->getContents(), true);

        if (!isset($data['name'], $data['email'], $data['subject'], $data['message'])) {
            throw new Exception('Invalid submission data');
        }

        // Generate unique message ID
        $messageId = uniqid('msg_', true);

        // Configure transport with explicit options
        $transport = Transport::fromDsn($_ENV['SMTP_DSN']);
        $mailer = new Mailer($transport);

        // Configure emails with tracking headers
        $adminEmail = (new Email())
            ->from($_ENV['FROM_EMAIL'])
            ->to($_ENV['ADMIN_EMAIL'])
            ->subject('New Contact Message: ' . $data['subject'])
            ->text("From: {$data['name']} <{$data['email']}>\n\n{$data['message']}");
        $adminEmail->getHeaders()->addTextHeader('X-SES-MESSAGE-TAGS', 'message_id=' . $messageId);
        $adminEmail->getHeaders()->addTextHeader('X-SES-CONFIGURATION-SET', $_ENV['SES_CONFIGURATION_SET']);

        // Auto-reply to sender
        $autoReply = (new Email())
            ->from($_ENV['FROM_EMAIL'])
            ->to($data['email'])
            ->subject('Thanks for contacting me!')
            ->text("Hi {$data['name']},\n\nThanks for reaching out. I'll get back to you soon!\n\nBest,\nDamilola");

        try {
            $mailer->send($adminEmail);
            $mailer->send($autoReply);

            // Save to database using Eloquent
            Message::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'],
                'message' => $data['message'],
                'message_id' => $messageId
            ]);

            $payload = ['success' => true, 'message' => 'Message received. Thank you!'];
            $response->getBody()->write(json_encode($payload));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            throw new Exception('Mailer error: ' . $e->getMessage());
        }
    } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
        error_log('SMTP Transport error: ' . $e->getMessage());
        $payload = [
            'success' => false,
            'message' => 'Email delivery failed',
            'debug' => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
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

// Modify webhook endpoint to support testing
$app->post('/ses-webhook', function (Request $request, Response $response) {
    $notification = json_decode($request->getBody()->getContents(), true);
    
    // Log webhook event
    EventLog::create([
        'event_type' => 'ses_webhook',
        'payload' => $notification
    ]);
    
    // Log incoming webhook for debugging
    error_log('Webhook received: ' . json_encode($notification));
    
    if (isset($notification['Message'])) {
        // Handle both string and array Message formats
        $message = is_string($notification['Message']) 
            ? json_decode($notification['Message'], true)
            : $notification['Message'];
            
        $messageId = $message['mail']['tags']['message_id'] ?? null;
        $eventType = $message['eventType'] ?? '';
        
        error_log("Processing event: $eventType for message: $messageId");
        
        if ($messageId) {
            $status = match($eventType) {
                'Delivery' => Message::STATUS_DELIVERED,
                'Bounce' => Message::STATUS_BOUNCED,
                'Open' => Message::STATUS_OPENED,
                'Click' => Message::STATUS_CLICKED,
                'Complaint' => Message::STATUS_COMPLAINED,
                default => null
            };

            if ($status) {
                Message::where('message_id', $messageId)
                    ->update(['status' => $status]);
            }
        }
    }

    return $response->withStatus(200);
});

$app->run();