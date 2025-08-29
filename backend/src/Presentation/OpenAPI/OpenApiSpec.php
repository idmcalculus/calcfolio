<?php

namespace App\Presentation\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Portfolio Contact API",
    version: "1.0.0",
    description: "API for managing contact form submissions and admin dashboard",
    contact: new OA\Contact(
        name: "Damilola Michael Ige",
        email: "idm.calculus@gmail.com"
    )
)]
#[OA\Server(url: "http://localhost:8080", description: "Local Development server")]
#[OA\Server(url: "https://calcfolio-api-dev.up.railway.app", description: "Remote Development server")]
#[OA\Server(url: "https://calcfolio-api.up.railway.app", description: "Remote Development server")]
#[OA\SecurityScheme(
    securityScheme: "sessionAuth",
    type: "apiKey",
    in: "cookie",
    name: "PHPSESSID",
    description: "Session-based authentication for admin endpoints"
)]
#[OA\Schema(
    schema: "Error",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: false),
        new OA\Property(property: "message", type: "string"),
        new OA\Property(property: "errors", type: "object")
    ]
)]
#[OA\Schema(
    schema: "Message",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "name", type: "string"),
        new OA\Property(property: "email", type: "string", format: "email"),
        new OA\Property(property: "subject", type: "string"),
        new OA\Property(property: "message", type: "string"),
        new OA\Property(property: "message_id", type: "string"),
        new OA\Property(property: "status", type: "string", enum: ["pending","delivered","bounced","opened","clicked","complained"]),
        new OA\Property(property: "is_read", type: "boolean"),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
#[OA\Schema(
    schema: "Pagination",
    type: "object",
    properties: [
        new OA\Property(property: "total", type: "integer"),
        new OA\Property(property: "per_page", type: "integer"),
        new OA\Property(property: "current_page", type: "integer"),
        new OA\Property(property: "last_page", type: "integer"),
        new OA\Property(property: "from", type: "integer"),
        new OA\Property(property: "to", type: "integer")
    ]
)]
class OpenApiSpec
{
    // This class serves as a container for OpenAPI attributes (Info, Servers, Schemas, SecuritySchemes)
}