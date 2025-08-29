<?php

namespace App\Application\Validators;

use App\Domain\Interfaces\ValidationInterface;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class RequestValidator implements ValidationInterface
{
    private array $errors = [];
    private array $validatedData = [];

    public function validateContactForm(array $data): array
    {
        $this->errors = [];
        $this->validatedData = [];

        try {
            // Validate required fields
            $validator = v::key('name', v::stringType()->notEmpty()->length(1, 100))
                ->key('email', v::email())
                ->key('subject', v::stringType()->notEmpty()->length(1, 200))
                ->key('message', v::stringType()->notEmpty()->length(1, 5000));

            // Add optional reCAPTCHA validation
            if (isset($data['recaptcha_token'])) {
                $validator = $validator->key('recaptcha_token', v::stringType()->notEmpty());
            }

            $validator->assert($data);

            // Sanitize and validate email
            $data['email'] = strtolower(trim($data['email']));

            $this->validatedData = $data;
            return $this->validatedData;

        } catch (NestedValidationException $e) {
            $this->errors = $this->formatValidationErrors($e);
            return [];
        }
    }

    public function validateAdminLogin(array $data): array
    {
        $this->errors = [];
        $this->validatedData = [];

        try {
            $validator = v::key('username', v::stringType()->notEmpty()->length(1, 100))
                ->key('password', v::stringType()->notEmpty()->length(1, 255));

            $validator->assert($data);

            // Sanitize username (could be email)
            $data['username'] = trim($data['username']);

            $this->validatedData = $data;
            return $this->validatedData;

        } catch (NestedValidationException $e) {
            $this->errors = $this->formatValidationErrors($e);
            return [];
        }
    }

    public function validateBulkAction(array $data): array
    {
        $this->errors = [];
        $this->validatedData = [];

        try {
            $validator = v::key('action', v::in(['mark_read', 'mark_unread', 'delete']))
                ->key('ids', v::arrayType()->notEmpty()->each(v::intType()->positive()));

            $validator->assert($data);

            $this->validatedData = $data;
            return $this->validatedData;

        } catch (NestedValidationException $e) {
            $this->errors = $this->formatValidationErrors($e);
            return [];
        }
    }

    public function validatePagination(array $params): array
    {
        $this->errors = [];
        $this->validatedData = [];

        try {
            // Convert string parameters to appropriate types
            $processedParams = $this->processPaginationParams($params);

            // Build validator for only the keys that are present
            $validator = v::arrayType();

            // Validate page if present
            if (isset($processedParams['page'])) {
                $validator = $validator->key('page', v::intType()->min(1));
            }

            // Validate limit if present (allow up to 10000 for large datasets)
            if (isset($processedParams['limit'])) {
                $validator = $validator->key('limit', v::intType()->min(1)->max(10000));
            }

            // Validate sort if present
            if (isset($processedParams['sort'])) {
                $validator = $validator->key('sort', v::in(['created_at', 'name', 'email', 'subject', 'is_read']));
            }

            // Validate order if present
            if (isset($processedParams['order'])) {
                $validator = $validator->key('order', v::in(['asc', 'desc']));
            }

            // Validate is_read if present (accept both string and int)
            if (isset($processedParams['is_read'])) {
                $validator = $validator->key('is_read', v::in(['0', '1', 0, 1]));
            }

            // Validate search if present
            if (isset($processedParams['search'])) {
                $validator = $validator->key('search', v::stringType()->length(1, 100));
            }

            // Validate status if present
            if (isset($processedParams['status'])) {
                $validator = $validator->key('status', v::stringType());
            }

            $validator->assert($processedParams);

            // Set defaults for missing parameters
            $this->validatedData = array_merge([
                'page' => 1,
                'limit' => 15,
                'sort' => 'created_at',
                'order' => 'desc',
                'is_read' => null,
                'search' => null,
                'status' => null,
            ], $processedParams);

            return $this->validatedData;

        } catch (NestedValidationException $e) {
            $this->errors = $this->formatValidationErrors($e);
            return [];
        }
    }

    /**
     * Process and convert pagination parameters from strings to appropriate types
     */
    private function processPaginationParams(array $params): array
    {
        $processed = [];

        foreach ($params as $key => $value) {
            switch ($key) {
                case 'page':
                case 'limit':
                    // Convert string numbers to integers
                    $processed[$key] = is_numeric($value) ? (int) $value : $value;
                    break;
                case 'is_read':
                    // Convert string booleans to appropriate format
                    if ($value === '0' || $value === '1') {
                        $processed[$key] = $value; // Keep as string for validation
                    } elseif (is_bool($value)) {
                        $processed[$key] = $value ? '1' : '0';
                    } else {
                        $processed[$key] = $value;
                    }
                    break;
                default:
                    // Keep other parameters as-is
                    $processed[$key] = $value;
                    break;
            }
        }

        return $processed;
    }

    public function validateSearch(array $params): array
    {
        $this->errors = [];
        $this->validatedData = [];

        try {
            $validator = v::key('query', v::stringType()->notEmpty()->length(1, 100))
                ->key('is_read', v::optional(v::boolType()))
                ->key('status', v::optional(v::stringType()));

            $validator->assert($params);

            $this->validatedData = $params;
            return $this->validatedData;

        } catch (NestedValidationException $e) {
            $this->errors = $this->formatValidationErrors($e);
            return [];
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    private function formatValidationErrors(NestedValidationException $e): array
    {
        $errors = [];
        foreach ($e->getMessages() as $field => $messages) {
            $errors[$field] = is_array($messages) ? $messages : [$messages];
        }
        return $errors;
    }
}