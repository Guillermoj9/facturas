<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        return view('settings.edit', ['setting' => Setting::current()]);
    }

    public function update(SettingRequest $request)
    {
        $setting = Setting::current();

        if ($request->hasFile('logo') && $setting->logo_path) {
            Storage::disk('public')->delete($setting->logo_path);
        }

        if ($request->boolean('remove_logo') && $setting->logo_path) {
            Storage::disk('public')->delete($setting->logo_path);
            $setting->update(['logo_path' => null]);
        }

        $setting->update($request->settingData($setting->fresh()->logo_path));

        return redirect()->route('settings.edit')->with('success', 'Configuración actualizada.');
    }
}
