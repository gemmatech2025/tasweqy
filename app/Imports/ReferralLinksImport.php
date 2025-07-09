<?php

namespace App\Imports;

use App\Models\ReferralLink;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ReferralLinksImport implements ToModel, WithHeadingRow
{
    private $brand_id;

    public function __construct($brand_id)
    {
        $this->brand_id = $brand_id;
    }

    public function model(array $row)
    {




        if (!empty($row['earning']) && !empty($row['link'])) {


            $exsist = ReferralLink::where('brand_id' , $this->brand_id)
                        ->where('link' , $row['link'])->first();

                        if($exsist){
                            return null;
                        }


            return new ReferralLink([
                'brand_id'            => $this->brand_id,
                'link'                => $row['link'],
                'earning_precentage'  => $row['earning'],
                'earning_precentage'  => $row['earning'],
                'link_code'           => $row['link_code'] ?? null,
            ]);
        }

        return null;
    }

}