<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCollegeSettingRequest;
use App\Models\CollegeSetting;
use App\Models\DegreeLevelSetting;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
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

        $levels = collect(Department::LEVELS)->mapWithKeys(fn (string $level) => [
            $level => DegreeLevelSetting::firstOrCreate(['level' => $level]),
        ]);

        return view('admin.settings.edit', compact('setting', 'levels'));
    }

    public function updateLevels(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'levels' => ['required', 'array'],
            'levels.*.max_group_size' => ['required', 'integer', 'min:1', 'max:10'],
            'levels.*.submission_deadline' => ['nullable', 'date'],
            'levels.*.max_marks' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'levels.*.viva_required' => ['nullable', 'boolean'],
        ]);

        foreach ($data['levels'] as $level => $values) {
            if (! in_array($level, Department::LEVELS, true)) {
                continue;
            }

            DegreeLevelSetting::updateOrCreate(['level' => $level], [
                'max_group_size' => $values['max_group_size'],
                'submission_deadline' => $values['submission_deadline'] ?? null,
                'max_marks' => $values['max_marks'] ?? null,
                'viva_required' => (bool) ($values['viva_required'] ?? false),
            ]);
        }

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Graduation / Post-Graduation rules saved.');
    }

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
