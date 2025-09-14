<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{

    protected function error($error, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $error
        ], $code);
    }

    protected function paginate($query)
    {
        $perPage = request()->get('per_page', 10);
        return $query->paginate($perPage);
    }

    protected function success($resource, $code = 200)
    {
        return $resource->response()->setStatusCode($code);
    }
    protected function prepareSorting($query, $allowedFields, $dbSortBy = null)
    {
        $sortBy = $dbSortBy ? $dbSortBy : request()->get('sort_by', 'created_at');
        if(!in_array($sortBy, $allowedFields)) {
            $sortBy = 'created_at';
        }
        $sortDirection = request()->get('sort_direction', 'desc');
        if(!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        return $query->orderBy($sortBy, $sortDirection);
    }
}