<?php

namespace App\Http\Resources\Admin\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


// use App\Http\Resources\UserResource;




class WithdrawRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    // protected $fillable = ['user_id', 'total', 'status', 'withdrawable_type', 'withdrawable_id'];


        if ($this->withdrawable_type == 'App\Models\BankInfo') {
            $type = 'bank';
            $info =''; 
            if($this->withdrawable){
                $info = $this->withdrawable->bank_name . ' - ' . $this->withdrawable->account_number;
            }else{
                $info ='bank_info_deleted'; 
            }
        } else {

            $type = 'paypal';


            $info =''; 
            if($this->withdrawable){
                $info = $this->withdrawable->email;
            }else{
                $info ='paypal_account_deleted'; 
            }
        }


 return [
            'id'          => $this->id,
            'type'        => $type,
            'info'        => $info,

            'total'       => $this->total,
            'status'      => $this->status,
            'created_at'  => $this->created_at->format('F j, Y g:i A'),
        ];    
    
    
    }
}
