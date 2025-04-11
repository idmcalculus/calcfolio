<?php

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

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

// Contact form endpoint
$app->post('/contact', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);

    if (!isset($data['name'], $data['email'], $data['subject'], $data['message'])) {
        $payload = ['success' => false, 'message' => 'Invalid submission'];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Connect to SQLite
    $dbPath = __DIR__ . '/../contact.db';
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        subject TEXT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert message
    $stmt = $db->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['name'],
        $data['email'],
        $data['subject'],
        $data['message']
    ]);

    // Send emails using Symfony Mailer
    // Adjust your DSN: user:pass@smtp.yourdomain.com:587
    $dsn = 'smtp://your-smtp-user:your-smtp-password@smtp.yourdomain.com:587';
    $transport = Transport::fromDsn($dsn);
    $mailer = new Mailer($transport);

    // Admin notification
    $adminEmail = (new Email())
        ->from('noreply@yourdomain.com')
        ->to('admin@yourdomain.com')
        ->subject('New Contact Message: ' . $data['subject'])
        ->text("From: {$data['name']} <{$data['email']}>\n\n{$data['message']}");

    // Auto-reply to sender
    $autoReply = (new Email())
        ->from('noreply@yourdomain.com')
        ->to($data['email'])
        ->subject('Thanks for contacting me!')
        ->text("Hi {$data['name']},\n\nThanks for reaching out. I'll get back to you soon!\n\nBest,\nDamilola");

    try {
        $mailer->send($adminEmail);
        $mailer->send($autoReply);

        $payload = ['success' => true, 'message' => 'Message received. Thank you!'];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $payload = ['success' => false, 'message' => 'Mailer error: ' . $e->getMessage()];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->run();