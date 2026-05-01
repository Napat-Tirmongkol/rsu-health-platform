<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Clinic;

class ServiceController extends Controller
{
    public function ncdClinic()
    {
        return view('user.services.ncd-clinic', [
            'clinic' => $this->currentClinic(),
        ]);
    }

    public function contact()
    {
        return view('user.services.contact', [
            'clinic' => $this->currentClinic(),
        ]);
    }

    public function help()
    {
        return view('user.services.help', [
            'clinic' => $this->currentClinic(),
        ]);
    }

    protected function currentClinic(): ?Clinic
    {
        return Clinic::find(currentClinicId());
    }
}
