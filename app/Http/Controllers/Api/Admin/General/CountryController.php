<?php

namespace App\Http\Controllers\Api\Admin\General;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\Country;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\Admin\General\CountryRequest;
use App\Http\Resources\Admin\General\CountryResource;
use App\Http\Resources\Admin\General\CountryShowResource;


class CountryController extends BaseController
{


    protected const RESOURCE = CountryResource::class;
    protected const RESOURCE_SHOW = CountryShowResource::class;
    protected const REQUEST = CountryRequest::class;

    public function model()
    {
        return   Country::class; 
    }


    public function getSearchableFields()
    {
        return ['name' , 'code'];
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


        $countries = Country::all()->map(function($country){
            return['id' => $country->id , 'name' => $country->name];
        });


        return jsonResponse(
        true,
        200,
        __('messages.success'),
        $countries
        // (static::RESOURCE)::collection($countries)
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
        $data = $query->paginate($perPage, ['*'], 'page', $page);

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