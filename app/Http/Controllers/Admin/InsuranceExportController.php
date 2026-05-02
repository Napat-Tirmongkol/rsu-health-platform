<?php

namespace App\Http\Controllers\Admin;

use App\Exports\InsuranceMemberExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class InsuranceExportController extends Controller
{
    public function export()
    {
        $clinicId = Auth::guard('admin')->user()->clinic_id ?? 1;
        $filename = 'insurance_members_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new InsuranceMemberExport($clinicId), $filename);
    }
}
