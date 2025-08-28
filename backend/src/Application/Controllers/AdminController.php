<?php

namespace App\Application\Controllers;

use OpenApi\Attributes as OA;
use App\Application\Services\AdminMessageService;
use App\Application\Services\AdminAuthenticationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminController
{
    private AdminMessageService $adminMessageService;
    private AdminAuthenticationService $authService;

    public function __construct(
        AdminMessageService $adminMessageService,
        AdminAuthenticationService $authService
    ) {
        $this->adminMessageService = $adminMessageService;
        $this->authService = $authService;
    }

    #[OA\Get(
        path: "/admin/messages",
        summary: "Get paginated messages",
        tags: ["Admin Messages"],
        security: [["sessionAuth" => []]],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer", minimum: 1)),
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", minimum: 1, maximum: 1000)),
            new OA\Parameter(name: "sort", in: "query", schema: new OA\Schema(type: "string", enum: ["created_at","name","email","subject","is_read"])),
            new OA\Parameter(name: "order", in: "query", schema: new OA\Schema(type: "string", enum: ["asc","desc"])),
            new OA\Parameter(name: "is_read", in: "query", schema: new OA\Schema(type: "string", enum: ["0","1"])),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Messages retrieved successfully"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function getMessages(Request $request, Response $response): Response
    {
        if (!$this->authService->isAuthenticated()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $params = $request->getQueryParams();
        $result = $this->adminMessageService->getMessages($params);

        $statusCode = $result['success'] ? 200 : 400;
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }

    #[OA\Get(
        path: "/admin/messages/{id}",
        summary: "Get single message",
        tags: ["Admin Messages"],
        security: [["sessionAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Message retrieved successfully"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Message not found")
        ]
    )]
    public function getMessage(Request $request, Response $response, array $args): Response
    {
        if (!$this->authService->isAuthenticated()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $id = (int) ($args['id'] ?? 0);
        $result = $this->adminMessageService->getMessage($id);

        $statusCode = match(true) {
            !$result['success'] && isset($result['message']) && str_contains($result['message'], 'not found') => 404,
            !$result['success'] => 400,
            default => 200
        };

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }

    #[OA\Patch(
        path: "/admin/bulk/messages",
        summary: "Bulk message actions",
        tags: ["Admin Messages"],
        security: [["sessionAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["action","ids"],
                properties: [
                    new OA\Property(property: "action", type: "string", enum: ["mark_read","mark_unread","delete"]),
                    new OA\Property(property: "ids", type: "array", items: new OA\Items(type: "integer"))
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Bulk action completed"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 400, description: "Invalid request")
        ]
    )]
    public function bulkAction(Request $request, Response $response): Response
    {
        if (!$this->authService->isAuthenticated()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        try {
            $data = json_decode($request->getBody()->getContents(), true);
            $action = $data['action'] ?? '';
            $ids = $data['ids'] ?? [];

            $result = $this->adminMessageService->bulkAction($action, $ids);

            $statusCode = $result['success'] ? 200 : 400;
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);

        } catch (\Exception $e) {
            error_log('Bulk action error: ' . $e->getMessage());
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to perform bulk action'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    #[OA\Get(
        path: "/admin/messages/stats",
        summary: "Get message statistics",
        tags: ["Admin Messages"],
        security: [["sessionAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Statistics retrieved"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function getStatistics(Request $request, Response $response): Response
    {
        if (!$this->authService->isAuthenticated()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $result = $this->adminMessageService->getStatistics();

        $statusCode = $result['success'] ? 200 : 500;
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }
}