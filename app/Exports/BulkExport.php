<?php

namespace App\Exports;

use App\NaacFile;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BulkExport implements FromView, ShouldAutoSize, WithStyles
{
    
   use Exportable;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
        
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => [
                        'font' => ['bold' => true],
                        'background' => ['color'=> '#cccccc']
                    ],
        ];
    }

    public function view(): View
    {
        $naacfiles = NaacFile::where('criteria_id',$this->data)->with('getCriteria','geDepartment')->orderBy('created_at', 'asc')->get();
        return view('exports.naacexport', [
            'naacfiles' => $naacfiles
        ]);
    }
}
