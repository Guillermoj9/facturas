<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingRequest;
use App\Models\Setting;

class SetupController extends Controller
{
    public function create()
    {
        return view('setup');
    }

    public function store(SettingRequest $request)
    {
        Setting::create($request->settingData());

        return redirect()->route('dashboard')->with('success', '¡Configuración guardada! Ya puedes empezar a facturar.');
    }
}
