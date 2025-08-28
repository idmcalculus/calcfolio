<?php

namespace App\Application\Controllers;

use OpenApi\Attributes as OA;
use App\Application\Services\ContactFormService;
use App\Application\Services\AdminMessageService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use ReCaptcha\ReCaptcha;
use ReCaptcha\RequestMethod\CurlPost;

class ContactController
{
    private ContactFormService $contactFormService;
    private AdminMessageService $adminMessageService;

    public function __construct(
        ContactFormService $contactFormService,
        AdminMessageService $adminMessageService
    ) {
        $this->contactFormService = $contactFormService;
        $this->adminMessageService = $adminMessageService;
    }

    #[OA\Post(
        path: "/contact",
        summary: "Submit contact form",
        tags: ["Contact"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name","email","subject","message","recaptcha_token"],
                properties: [
                    new OA\Property(property: "name", type: "string", maxLength: 100),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "subject", type: "string", maxLength: 200),
                    new OA\Property(property: "message", type: "string", maxLength: 5000),
                    new OA\Property(property: "recaptcha_token", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Message sent successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "message_id", type: "string")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Validation error"),
            new OA\Response(response: 500, description: "Server error")
        ]
    )]
    public function submit(Request $request, Response $response): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);

            // Verify reCAPTCHA
            $recaptchaSecret = $_ENV['RECAPTCHA_V3_SECRET_KEY'] ?? getenv('RECAPTCHA_V3_SECRET_KEY');
            if (!$recaptchaSecret) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Server configuration error.'
                ]));
                return $response->withStatus(500);
            }

            if (!isset($data['recaptcha_token'])) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'reCAPTCHA token missing.'
                ]));
                return $response->withStatus(400);
            }

            $recaptcha = new ReCaptcha($recaptchaSecret, new CurlPost());
            $recaptchaResponse = $recaptcha->verify(
                $data['recaptcha_token'],
                $request->getServerParams()['REMOTE_ADDR'] ?? null
            );

            if (!$recaptchaResponse->isSuccess() || $recaptchaResponse->getScore() < 0.5) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'reCAPTCHA verification failed.'
                ]));
                return $response->withStatus(400);
            }

            // Process contact form
            $result = $this->contactFormService->processContactForm($data);

            $statusCode = $result['success'] ? 200 : 400;
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);

        } catch (\Exception $e) {
            error_log('Contact form error: ' . $e->getMessage());
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'An error occurred while processing your request.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    #[OA\Get(
        path: "/message/{messageId}",
        summary: "Get message status",
        tags: ["Contact"],
        parameters: [
            new OA\Parameter(
                name: "messageId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Message status retrieved",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean"),
                        new OA\Property(property: "message_id", type: "string"),
                        new OA\Property(property: "status", type: "string"),
                        new OA\Property(property: "is_read", type: "boolean"),
                        new OA\Property(property: "created_at", type: "string", format: "date-time")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Message not found")
        ]
    )]
    public function getMessageStatus(Request $request, Response $response, array $args): Response
    {
        $messageId = $args['messageId'] ?? '';

        if (empty($messageId)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Message ID is required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $result = $this->contactFormService->getMessageStatus($messageId);

        $statusCode = $result['success'] ? 200 : 404;
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }
}