<?php

namespace App\Services;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SearchService
{



    public function search(Request $request, $model, $resource ,$need_pagination = false , $filterFields = [], $relations = [])
    {
    if (!is_subclass_of($resource, \Illuminate\Http\Resources\Json\JsonResource::class)) {
        abort(500, 'Invalid resource class provided.');
    }
    
    $modelInstance = new $model;
    $query = $modelInstance->with($relations);
    $columns = \Schema::getColumnListing($modelInstance->getTable());

    
    $searchTerm = trim($request->input('search', ''));
    $filters = $request->input('filter', []);
    $sortBy = $request->input('sort_by', 'id');
    $sortOrder = $request->input('sort_order', 'asc');

    
    $filters = array_map(function ($value) {
        if (is_string($value)) {
            $lower = strtolower($value);
            return match ($lower) {
                'true' => 1,
                'false' => 0,
                default => is_numeric($value) ? $value + 0 : $value,
            };
        }
        return $value;
    }, $filters);

    
    $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

    
    foreach ($filters as $key => $value) {
        if (in_array($key, $columns)) {
            $query->where($key, $value);
        }
    }

    
    if (!empty($searchTerm)) {
        $query->where(function ($q) use ($filterFields, $searchTerm) {
            foreach ($filterFields as $field) {
                $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
            }
        });
    }

    
    if ($sortBy && in_array($sortBy, $columns)) {
        $query->orderBy($sortBy, $sortOrder);
    } elseif (method_exists($this, 'getSort')) {
        foreach ($this->getSort() as $sort) {
            $query->orderBy($sort['sort'], $sort['order']);
        }
    } else {
        $query->orderByDesc('id'); 
    }

    
    if ($need_pagination) {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $data = $query->paginate($perPage, ['*'], 'page', $page);

        $pagination = [
            'total'         => $data->total(),
            'current_page'  => $data->currentPage(),
            'per_page'      => $data->perPage(),
            'last_page'     => $data->lastPage(),
        ];

        return jsonResponse(true, 200, __('messages.success'), $resource::collection($data), $pagination);
    }

    return jsonResponse(true, 200, __('messages.success'), $resource::collection($query->get()));
}

    
}