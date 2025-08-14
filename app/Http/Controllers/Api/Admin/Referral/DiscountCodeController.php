<?php

namespace App\Http\Controllers\Api\Admin\Referral;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\DiscountCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
    
use App\Http\Requests\Admin\Referral\DiscountCodeRequest;
use App\Http\Requests\Admin\Referral\DiscountCodeListRequest;
use App\Http\Requests\Admin\Referral\UpdateLinkStatusRequest
;

use App\Http\Resources\Admin\Referral\DiscountCodeResource;
use App\Http\Resources\Admin\Referral\DiscountCodeIndexResource;
use Maatwebsite\Excel\Facades\Excel;


use App\Http\Requests\Admin\Referral\ImportReferralLinkRequest;

use App\Imports\DiscountCodesImport;
use App\Exports\DiscountCodeExportTemplate;
class DiscountCodeController extends BaseController
{


    protected const RESOURCE = DiscountCodeIndexResource::class;
    protected const RESOURCE_SHOW = DiscountCodeResource::class;
    protected const REQUEST = DiscountCodeRequest::class;

    public function model()
    {
        return DiscountCode::class; 
    }


    public function showRelations()
    {
        return ['brand'];
    }



    public function store(Request $request)
    {
        $reqClass      = static::REQUEST;
        $effectiveRequest = $reqClass !== Request::class
            ? app($reqClass)
            : $request;

        $validated = method_exists($effectiveRequest, 'validated')
            ? $effectiveRequest->validated()
            : $effectiveRequest->all();
            
            DB::beginTransaction();
            try {
                 $discountCode = DiscountCode::where('code' , $request->code)
                    ->where('brand_id' ,  $request->brand_id)->first();

                    if($discountCode){

                        $discountCode->earning_precentage = $request->earning_precentage;
                        $discountCode->save();

                    }else{
                        DiscountCode::create([
                            'brand_id'            => $request->brand_id,
                            'code'                => $request->code,
                            'earning_precentage'  => $request->earning_precentage,
                        ]);
                    }              
                DB::commit();
                return jsonResponse(
                    true, 201, __('messages.add_success'),
                );
        }catch (\Throwable $e) {
                DB::rollBack();
                return jsonResponse(false, 500, __('messages.general_message'), null, null, [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);
        }
    }


    public function storeList(DiscountCodeListRequest $request)
    {

            DB::beginTransaction();
            try {
                foreach($request->codes as $code){

                    $discountCode = DiscountCode::where('code' , $code['code'])
                    ->where('brand_id' ,  $request->brand_id)->first();

                    if($discountCode){

                        $discountCode->earning_precentage = $code['earning_precentage'];
                        $discountCode->save();

                    }else{
                        DiscountCode::create([
                            'brand_id'            => $request->brand_id,
                            'code'                => $code['code'],
                            'earning_precentage'  => $code['earning_precentage'],
                        ]);
                    }

                    
                }
                DB::commit();
                return jsonResponse(
                    true, 201, __('messages.add_success'),
                );
        }catch (\Throwable $e) {
                DB::rollBack();
                return jsonResponse(false, 500, __('messages.general_message'), null, null, [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);
        }
    }

    public function index(Request $request)
    {

        $searchTerm = trim($request->input('searchTerm', ''));
        $filters = $request->input('filter', []);
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query = $this->getModel()->with($this->getRelations());





    if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'LIKE', "%$searchTerm%")
                    ->orWhere('code', 'LIKE', "%$searchTerm%")
                    ->orWhereHas('referralEarning', function ($q2) use ($searchTerm) {
                        $q2->whereHas('user', function ($innerQuery) use ($searchTerm) {
                            $innerQuery->where('name', 'LIKE', "%$searchTerm%");
                        });
                    })
                    ->orWhereHas('brand', function ($q2) use ($searchTerm) {
                        $q2->where('name', 'LIKE', "%$searchTerm%");
                    });
            });
    }



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



        // if (!empty($searchTerm)) {
        //     $searchableFields = $this->getSearchableFields();
        //     $query->where(function ($q) use ($searchableFields, $searchTerm) {
        //         foreach ($searchableFields as $field) {
        //             $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
        //         }
        //     });
        // }

        if ($sortBy && $sortOrder) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            foreach ($this->getSort() as $sort) {
                $query->orderBy($sort['sort'], $sort['order']);
            }
        }

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




    public function exportDiscountCodesTemplate(){
        return Excel::download(new DiscountCodeExportTemplate, 'Discount codes Template.xlsx');
    }
    

    public function importDiscountCodes(ImportReferralLinkRequest $request)
    {

        Excel::import(new DiscountCodesImport($request->brand_id), $request->file('file'));
        return jsonResponse(
            true,
            200,
            __('messages.discount_codes_Imported_Successfully'),
        );
    }






    public function getDiscountCodesNumbers()
    {
        $discountCodesCount = DiscountCode::count();

        $usedDiscountCodesCount = DiscountCode::whereHas('referralEarning')->count();

        $notUsedDiscountCodesCount = DiscountCode::whereDoesntHave('referralEarning')->count();

        $inactiveDiscountCodesCount = DiscountCode::where('status', 'inactive')->count();

        $usedDiscountCodesThisMonthCount = DiscountCode::whereHas('referralEarning', function ($query) {
            $query->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'));
        })->count();

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            [
                'discount_codes_count' => $discountCodesCount,
                'used_discount_codes_count' => $usedDiscountCodesCount,
                'not_used_discount_codes_count' => $notUsedDiscountCodesCount,
                'inactive_discount_codes_count' => $inactiveDiscountCodesCount,
                'used_discount_codes_this_month_count' => $usedDiscountCodesThisMonthCount,
            ]
        );
    }


    public function updateStatus(UpdateLinkStatusRequest $request , $id){
    

        $discountCode = DiscountCode::find($id);

        if (!$discountCode) {
            return jsonResponse(false, 404, __('messages.not_found'));
        }


        if($request->status == 'inactive'){
            $discountCode->inactive_reason = $request->reason;
        } else {
            $discountCode->inactive_reason = null;
        }

        $discountCode->status = $request->status;
        $discountCode->save();

        return jsonResponse(
            true,
            200,
            __('messages.status_updated_successfully'),
            new DiscountCodeResource($discountCode)
        );
    }




    public function getNotReserved(Request $request , $brand_id)
    {

        $searchTerm = trim($request->input('searchTerm', ''));
        

        $query = DiscountCode::whereDoesntHave('referralEarning')
        ->where('brand_id', $brand_id)
        ->where('status', 'active');

            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('id', 'LIKE', "%$searchTerm%")
                        ->orWhere('code', 'LIKE', "%$searchTerm%");
                });
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $data = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

        $codes = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'code' => $item->code,
            ];
        });

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
            $codes,
            $pagination
        );
     
    }

    

}