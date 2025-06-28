<?php

namespace App\Imports;

use App\Models\DiscountCode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DiscountCodesImport implements ToModel, WithHeadingRow
{
    private $brand_id;

    public function __construct($brand_id)
    {
        $this->brand_id = $brand_id;
    }

    public function model(array $row)
    {




        if (!empty($row['earning']) && !empty($row['code'])) {


            $exsist = DiscountCode::where('brand_id' , $this->brand_id)
                        ->where('code' , $row['code'])->first();

                        if($exsist){
                            return null;
                        }


            return new DiscountCode([
                'brand_id'            => $this->brand_id,
                'code'                => $row['code'],
                'earning_precentage'  => $row['earning'],
            ]);
        }

        return null;
    }

}