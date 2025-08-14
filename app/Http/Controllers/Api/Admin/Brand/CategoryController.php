<?php

namespace App\Http\Controllers\Api\Admin\Brand;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use Illuminate\Support\Facades\Log;

use App\Services\SearchService;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\BandCountry;

use App\Http\Requests\Admin\Brand\CategoryRequest;
use App\Http\Resources\Admin\Brand\CategoryIndexResource;
use App\Http\Resources\Admin\Brand\CategoryShowResource;




class CategoryController extends BaseController
{

    protected const RESOURCE = CategoryIndexResource::class;
    protected const RESOURCE_SHOW = CategoryShowResource::class;
    protected const REQUEST = CategoryRequest::class;

    public function model()
    {
        return   Category::class; 
    }


    public function getSearchableFields()
    {
        return ['name' ];
    }


    public function uploadImages()
    {
       return ['image'];
    }
    
    
    public function indexPaginat()
    {
        return true;
    }

    public function getAllForSellect()
    {
        $categories = Category::all()->map(function($category){
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $category->image ? asset($category->image) : null,
            ];
        });


        return jsonResponse(
            true,
            200,
            __('messages.success'),
            $categories
        );
    }
public function index(Request $request)
{

    $searchTerm = trim($request->input('searchTerm', ''));
    $filters = $request->input('filter', []);
    $sortBy = $request->input('sort_by', 'id');
    $sortOrder = $request->input('sort_order', 'asc');
    $query = $this->getModel()->with($this->getRelations());
    $columns = \Schema::getColumnListing($this->getModel()->getTable());
    

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
        $searchableFields = $this->getSearchableFields();
        $query->where(function ($q) use ($searchableFields, $searchTerm) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
            }
        });
    }

    if ($sortBy && $sortOrder) {
        $query->orderBy($sortBy, $sortOrder);
    } else {
        foreach ($this->getSort() as $sort) {
            $query->orderBy($sort['sort'], $sort['order']);
        }
    }

    if ($this->indexPaginat()) {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $data = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

        $pagination = [
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ];

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            (static::RESOURCE)::collection($data),
            $pagination
        );
    }

    return jsonResponse(
        true,
        200,
        __('messages.success'),
        (static::RESOURCE)::collection($query->get())
    );
}



    
}