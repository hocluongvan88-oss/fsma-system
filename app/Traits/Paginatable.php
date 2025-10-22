<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait Paginatable
{
    /**
     * Get pagination parameters from request with validation
     */
    public function getPaginationParams(Request $request, int $defaultPerPage = 25, int $maxPerPage = 50): array
    {
        $perPage = (int) $request->get('per_page', $defaultPerPage);
        $page = (int) $request->get('page', 1);
        
        // Enforce max per page limit
        $perPage = min($perPage, $maxPerPage);
        
        // Ensure positive values
        $perPage = max($perPage, 1);
        $page = max($page, 1);
        
        return [
            'per_page' => $perPage,
            'page' => $page,
        ];
    }
}
