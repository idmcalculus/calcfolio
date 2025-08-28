<?php

namespace App\Domain\Interfaces;

interface ValidationInterface
{
    /**
     * Validate contact form data
     */
    public function validateContactForm(array $data): array;

    /**
     * Validate admin login data
     */
    public function validateAdminLogin(array $data): array;

    /**
     * Validate message bulk action data
     */
    public function validateBulkAction(array $data): array;

    /**
     * Validate pagination parameters
     */
    public function validatePagination(array $params): array;

    /**
     * Validate search parameters
     */
    public function validateSearch(array $params): array;

    /**
     * Get validation errors
     */
    public function getErrors(): array;

    /**
     * Check if validation passed
     */
    public function isValid(): bool;

    /**
     * Get validated data
     */
    public function getValidatedData(): array;
}