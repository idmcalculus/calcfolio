<?php

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    // Repositories
    $containerBuilder->addDefinitions([
        App\Domain\Interfaces\MessageRepositoryInterface::class => autowire(App\Infrastructure\Database\EloquentMessageRepository::class),
        App\Domain\Interfaces\AdminRepositoryInterface::class => autowire(App\Infrastructure\Database\EloquentAdminRepository::class),
    ]);

    // Services
    $containerBuilder->addDefinitions([
        App\Domain\Interfaces\EmailServiceInterface::class => autowire(App\Infrastructure\External\ResendEmailService::class),
        App\Domain\Interfaces\AuthenticationServiceInterface::class => autowire(App\Application\Services\AdminAuthenticationService::class),
        App\Domain\Interfaces\ValidationInterface::class => autowire(App\Application\Validators\RequestValidator::class),
    ]);

    // Application Services
    $containerBuilder->addDefinitions([
        App\Application\Services\ContactFormService::class => autowire(),
        App\Application\Services\AdminMessageService::class => autowire(),
    ]);

    // Controllers
    $containerBuilder->addDefinitions([
        App\Application\Controllers\ContactController::class => autowire(),
        App\Application\Controllers\AdminController::class => autowire(),
        App\Application\Controllers\AdminAuthController::class => autowire(),
        App\Application\Controllers\WebhookController::class => autowire(),
    ]);

    // Infrastructure Services
    $containerBuilder->addDefinitions([
        App\Infrastructure\Database\DatabaseSetupService::class => function (Psr\Container\ContainerInterface $container) {
            $capsule = new \Illuminate\Database\Capsule\Manager;
            $settings = $container->get('settings');
            $capsule->addConnection($settings['db']);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            return new App\Infrastructure\Database\DatabaseSetupService($capsule);
        },
    ]);

    // Middleware
    $containerBuilder->addDefinitions([
        App\Presentation\Middleware\CorsMiddleware::class => function (ContainerInterface $container) {
            $settings = $container->get('settings');
            return new App\Presentation\Middleware\CorsMiddleware(
                $settings['cors']['allowed_origins'] ?? []
            );
        },
        App\Presentation\Middleware\AdminAuthMiddleware::class => autowire(),
    ]);

    // External dependencies
    $containerBuilder->addDefinitions([
        'settings' => function () {
            return [
                'db' => require __DIR__ . '/database.php',
                'cors' => [
                    'allowed_origins' => array_map('trim', explode(',', ($_ENV['CORS_ALLOWED_ORIGINS'] ?? getenv('CORS_ALLOWED_ORIGINS')) ?: 'http://localhost:3000')),
                ],
                'email' => [
                    'resend_api_key' => $_ENV['RESEND_API_KEY'] ?? getenv('RESEND_API_KEY') ?: '',
                    'from_email' => $_ENV['FROM_EMAIL'] ?? getenv('FROM_EMAIL') ?: '',
                    'admin_email' => $_ENV['ADMIN_EMAIL'] ?? getenv('ADMIN_EMAIL') ?: '',
                ],
                'recaptcha' => [
                    'secret_key' => $_ENV['RECAPTCHA_V3_SECRET_KEY'] ?? getenv('RECAPTCHA_V3_SECRET_KEY') ?: '',
                ],
                'webhook' => [
                    'secret' => $_ENV['RESEND_WEBHOOK_SECRET'] ?? getenv('RESEND_WEBHOOK_SECRET') ?: '',
                ],
            ];
        },
    ]);

    return $containerBuilder->build();
};