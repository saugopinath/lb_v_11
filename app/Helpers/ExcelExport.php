<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExport
{

    public static function DownloadExcel($data, $file_name)
    {
        if (ob_get_contents())
            ob_end_clean();

        Excel::create($file_name, function ($excel) use ($data, $file_name) {
            $excel->getProperties()
                ->setCreator(Auth::user()->username)
                ->setLastModifiedBy('User ID: ' . Auth::id())
                ->setTitle($file_name)
                ->setKeywords('Download from IP Address:' . request()->ip());
            $excel->setManager('Finance, WB');

            $excel->sheet('Beneficiary List', function ($sheet) use ($data) {
                $sheet->fromArray($data, null, 'A1', false, false);
            });
        })->download('csv');
    }
}
