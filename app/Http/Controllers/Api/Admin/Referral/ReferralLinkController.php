<?php

namespace App\Http\Controllers\Api\Admin\Referral;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\ReferralLink ;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\Admin\Referral\ImportReferralLinkRequest;
use App\Http\Requests\Admin\Referral\ReferralLinkRequest;

use App\Http\Requests\Admin\Referral\ReferralLinkListRequest;


use App\Http\Resources\Admin\Referral\ReferralLinkResource;
use App\Http\Resources\Admin\Referral\ReferralLinkIndexResource;

use Maatwebsite\Excel\Facades\Excel;


use App\Imports\ReferralLinksImport;
use App\Exports\ReferralLinksExportTemplate;

class ReferralLinkController extends BaseController
{


    protected const RESOURCE = ReferralLinkIndexResource::class;
    protected const RESOURCE_SHOW = ReferralLinkResource::class;
    protected const REQUEST = ReferralLinkRequest::class;

    public function model()
    {
        return   ReferralLink::class; 
    }


    public function showRelations()
    {
        return ['brand'];
    }



    // public function store(Request $request)
    // {
    //         $reqClass      = static::REQUEST;
    //         $effectiveRequest = $reqClass !== Request::class
    //             ? app($reqClass)
    //             : $request;

    //         $validated = method_exists($effectiveRequest, 'validated')
    //             ? $effectiveRequest->validated()
    //             : $effectiveRequest->all();

    //         DB::beginTransaction();
    //         try {
    //                 ReferralLink::create([
    //                     'brand_id'            => $request->brand_id,
    //                     'link'                => $request->link,
    //                     'earning_precentage'  => $link['earning_precentage'],
    //                     'link_code'           => $link['link_code'],
    //                 ]);
                
    //             DB::commit();
    //             return jsonResponse(
    //                 true, 201, __('messages.add_success'),
    //             );
    //     }catch (\Throwable $e) {
    //             DB::rollBack();
    //             return jsonResponse(false, 500, __('messages.general_message'), null, null, [
    //                 'message' => $e->getMessage(),
    //                 'file'    => $e->getFile(),
    //                 'line'    => $e->getLine(),
    //             ]);
    //     }
    // }


    public function storeList(ReferralLinkListRequest $request)
    {
            DB::beginTransaction();
            try {
                foreach($request->links as $link){
                    ReferralLink::create([
                        'brand_id'            => $request->brand_id,
                        'link'                => $link['link'],
                        'earning_precentage'  => $link['earning_precentage'],
                        'link_code'           => $link['link_code'],
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

    public function index(Request $request)
    {

        $searchTerm = trim($request->input('search', ''));
        $filters = $request->input('filter', []);
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        
        $query = ReferralLink::query();
      
            
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







    public function exportReferralLinksTemplate(){
        return Excel::download(new ReferralLinksExportTemplate, 'Referral Links Template.xlsx');
    }
    



    public function importReferralLinks(ImportReferralLinkRequest $request)
    {

        Excel::import(new ReferralLinksImport($request->brand_id), $request->file('file'));
        return jsonResponse(
            true,
            200,
            __('messages.Referral_Links_Imported_Successfully'),
        );
    }





    public function getReferralLinksNumbers()
    {
        $referralLinksCount = ReferralLink::count();

        $usedReferralLinksCount = ReferralLink::whereHas('referralEarning')->count();

        $notUsedReferralLinksCount = ReferralLink::whereDoesntHave('referralEarning')->count();

        $inactiveReferralLinksCount = ReferralLink::where('status', 'inactive')->count();

        $usedReferralLinksThisMonthCount = ReferralLink::whereHas('referralEarning', function ($query) {
            $query->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'));
        })->count();

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            [
                'referral_links_count' => $referralLinksCount,
                'used_referral_links_count' => $usedReferralLinksCount,
                'not_used_referral_links_count' => $notUsedReferralLinksCount,
                'inactive_referral_links_count' => $inactiveReferralLinksCount,
                'used_referral_links_this_month_count' => $usedReferralLinksThisMonthCount,
            ]
        );
    }


    public function updateStatus(Request $request , $id){
    

        $referralLink = ReferralLink::find($id);

        if (!$referralLink) {
            return jsonResponse(false, 404, __('messages.not_found'));
        }

        $referralLink->status = $status;
        $referralLink->save();

        return jsonResponse(
            true,
            200,
            __('messages.status_updated_successfully'),
            new ReferralLinkResource($referralLink)
        );
    }

    





}