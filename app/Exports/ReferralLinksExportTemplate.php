<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReferralLinksExportTemplate implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    
    

    public function collection()
    {
        return collect([]);
    }


    


    public function headings(): array
    {
        return ['Earning %', 'Link' , 'link_code'];
    }


    

    public function styles(Worksheet $sheet)
    {

        $sheet->getStyle('A1:B1:C1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'FFFF00',
                ],
            ],
        ]);

        return [];
    }


    


    public function columnWidths(): array
    {
        return [
            'A' => 20, 
            'B' => 50, 
            'C' => 20, 

        ];
    }


}