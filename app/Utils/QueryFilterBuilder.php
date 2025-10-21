<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Builder;

/**
 * Query Filter Builder Utility
 *
 * Provides reusable methods for common query filtering patterns,
 * eliminating code duplication across services.
 */
class QueryFilterBuilder
{
    /**
     * Apply text search across multiple fields
     *
     * @param Builder $query
     * @param string|null $search Search term
     * @param array $fields Fields to search in
     * @return Builder
     */
    public static function applySearch(Builder $query, ?string $search, array $fields): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $fields) {
            foreach ($fields as $field) {
                // Check if field is JSON field
                if (str_ends_with($field, '_json')) {
                    $cleanField = str_replace('_json', '', $field);
                    $q->orWhereJsonContains($cleanField, $search);
                } else {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            }
        });
    }

    /**
     * Apply JSON contains filter (supports single value or array)
     *
     * @param Builder $query
     * @param mixed $values Single value, array, or comma-separated string
     * @param string $field JSON field name
     * @return Builder
     */
    public static function applyJsonContains(Builder $query, mixed $values, string $field): Builder
    {
        if (empty($values)) {
            return $query;
        }

        // Normalize to array
        $valuesArray = self::normalizeToArray($values);

        foreach ($valuesArray as $value) {
            $value = trim($value);
            if (!empty($value)) {
                $query->whereJsonContains($field, $value);
            }
        }

        return $query;
    }

    /**
     * Apply whereIn filter (supports single value or array)
     *
     * @param Builder $query
     * @param mixed $values Single value or array
     * @param string $field Field name
     * @return Builder
     */
    public static function applyWhereIn(Builder $query, mixed $values, string $field): Builder
    {
        if (empty($values)) {
            return $query;
        }

        if (is_array($values)) {
            $query->whereIn($field, $values);
        } else {
            $query->where($field, $values);
        }

        return $query;
    }

    /**
     * Apply LIKE filter
     *
     * @param Builder $query
     * @param mixed $value Value to search
     * @param string $field Field name
     * @return Builder
     */
    public static function applyLike(Builder $query, mixed $value, string $field): Builder
    {
        if (empty($value)) {
            return $query;
        }

        return $query->where($field, 'like', "%{$value}%");
    }

    /**
     * Apply exact match filter
     *
     * @param Builder $query
     * @param mixed $value Value to match
     * @param string $field Field name
     * @return Builder
     */
    public static function applyExact(Builder $query, mixed $value, string $field): Builder
    {
        if ($value === null || $value === '') {
            return $query;
        }

        return $query->where($field, $value);
    }

    /**
     * Apply boolean filter
     *
     * @param Builder $query
     * @param mixed $value Boolean value
     * @param string $field Field name
     * @return Builder
     */
    public static function applyBoolean(Builder $query, mixed $value, string $field): Builder
    {
        if ($value === null || $value === '') {
            return $query;
        }

        // Convert string booleans
        if (is_string($value)) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $query->where($field, $value);
    }

    /**
     * Apply range filter (min/max)
     *
     * @param Builder $query
     * @param mixed $min Minimum value
     * @param mixed $max Maximum value
     * @param string $field Field name
     * @return Builder
     */
    public static function applyRange(Builder $query, mixed $min, mixed $max, string $field): Builder
    {
        if (!empty($min)) {
            $query->where($field, '>=', $min);
        }

        if (!empty($max)) {
            $query->where($field, '<=', $max);
        }

        return $query;
    }

    /**
     * Apply dynamic sorting with validation
     *
     * @param Builder $query
     * @param array $filters Filters array containing sort_by and sort_direction
     * @param array $allowedFields Allowed fields for sorting
     * @param string $defaultField Default sort field
     * @param string $defaultDirection Default sort direction
     * @return Builder
     */
    public static function applySorting(
        Builder $query,
        array $filters,
        array $allowedFields,
        string $defaultField = 'created_at',
        string $defaultDirection = 'asc'
    ): Builder {
        $sortBy = $filters['sort_by'] ?? $defaultField;
        $sortDirection = $filters['sort_direction'] ?? $defaultDirection;

        // Validate sort field
        if (!in_array($sortBy, $allowedFields)) {
            $sortBy = $defaultField;
        }

        // Validate sort direction
        $sortDirection = strtolower($sortDirection);
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = $defaultDirection;
        }

        return $query->orderBy($sortBy, $sortDirection);
    }

    /**
     * Apply field alias filter (supports multiple field names for same filter)
     *
     * @param Builder $query
     * @param array $filters Filter array
     * @param array $aliases Array of possible filter keys (first found wins)
     * @param string $field Database field name
     * @return Builder
     */
    public static function applyWithAliases(
        Builder $query,
        array $filters,
        array $aliases,
        string $field
    ): Builder {
        foreach ($aliases as $alias) {
            if (!empty($filters[$alias])) {
                return self::applyExact($query, $filters[$alias], $field);
            }
        }

        return $query;
    }

    /**
     * Normalize value to array
     *
     * @param mixed $value Single value, array, or comma-separated string
     * @return array
     */
    private static function normalizeToArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && str_contains($value, ',')) {
            return array_map('trim', explode(',', $value));
        }

        return [$value];
    }
}
