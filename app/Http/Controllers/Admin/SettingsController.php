<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCollegeSettingRequest;
use App\Models\CollegeSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /** Show the college settings form (creating a default row if needed). */
    public function edit()
    {
        $setting = CollegeSetting::current() ?? CollegeSetting::create([
            'name' => config('college.name'),
            'tagline' => config('college.tagline'),
            'address' => config('college.address'),
            'affiliation' => config('college.affiliation'),
            'email' => config('college.email'),
            'phone' => config('college.phone'),
            'website' => config('college.website'),
        ]);

        return view('admin.settings.edit', compact('setting'));
    }

    /** Persist the college settings and (optionally) a new logo. */
    public function update(UpdateCollegeSettingRequest $request): RedirectResponse
    {
        $setting = CollegeSetting::current() ?? new CollegeSetting;
        $setting->fill($request->safe()->except('logo'));

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $setting->logo_path = $request->file('logo')->store('settings', 'public');
        }

        $setting->save();

        return redirect()->route('admin.settings.edit')->with('success', 'College settings saved.');
    }
}
