<?php

namespace App\Concerns;

trait Searchable
{
    /**
     * Scope a query to search with case-insensitive matching
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $search
     * @param array $fields Fields to search in (optional)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search, $fields = null)
    {
        if (empty($search)) {
            return $query;
        }

        $search_term = '%' . strtolower($search) . '%';
        $fields = $fields ?? $this->searchable_fields ?? ['id'];

        return $query->where(function ($q) use ($search_term, $fields) {
            foreach ($fields as $field) {
                $q->orWhereRaw('LOWER(' . $field . ') LIKE ?', [$search_term]);
            }
        });
    }
}