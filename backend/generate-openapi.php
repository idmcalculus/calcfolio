<?php

require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenvPath = __DIR__;
if (file_exists($dotenvPath . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
}

// Using Generator with finder (v5.3 compatible approach)
echo "Scanning for OpenAPI attributes...\n";

// Create finder for source directories
$finder = \Symfony\Component\Finder\Finder::create()
    ->files()
    ->name('*.php')
    ->in([
        __DIR__ . '/src/Presentation/OpenAPI',
        __DIR__ . '/src/Application/Controllers'
    ])
    ->exclude(['vendor', 'tests']);

// Generate OpenAPI spec
$openapi = \OpenApi\Generator::scan($finder);

echo "Scan completed.\n";

// Generate JSON spec
$jsonSpec = $openapi->toJson();

// Save to file
file_put_contents(__DIR__ . '/public/openapi.json', $jsonSpec);

// Generate YAML spec (optional)
$yamlSpec = $openapi->toYaml();
file_put_contents(__DIR__ . '/public/openapi.yaml', $yamlSpec);

// Generate HTML documentation (basic)
$htmlTemplate = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Portfolio Contact API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui.css" />
    <style>
        html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin: 0; background: #fafafa; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: '/openapi.json',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
        };
    </script>
</body>
</html>
HTML;

file_put_contents(__DIR__ . '/public/docs.html', $htmlTemplate);

// Create public directory if it doesn't exist
if (!is_dir(__DIR__ . '/public')) {
    mkdir(__DIR__ . '/public', 0755, true);
}

echo "OpenAPI documentation generated successfully!\n";
echo "- JSON spec: public/openapi.json\n";
echo "- YAML spec: public/openapi.yaml\n";
echo "- HTML docs: public/docs.html\n";
echo "\nYou can view the documentation at: http://localhost:8080/docs.html\n";