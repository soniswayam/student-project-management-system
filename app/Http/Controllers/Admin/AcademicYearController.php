<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    public function index()
    {
        $years = AcademicYear::withCount(['projects', 'assignments'])
            ->orderByDesc('starts_on')
            ->orderByDesc('label')
            ->paginate(15);

        return view('admin.academic-years.index', compact('years'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:20', 'unique:academic_years,label'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'make_current' => ['nullable', 'boolean'],
        ]);

        $year = AcademicYear::create([
            'label' => $data['label'],
            'starts_on' => $data['starts_on'] ?? null,
            'ends_on' => $data['ends_on'] ?? null,
        ]);

        if ($request->boolean('make_current') || AcademicYear::count() === 1) {
            $year->markCurrent();
        }

        return back()->with('success', "Academic year {$year->label} added.");
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:20', Rule::unique('academic_years', 'label')->ignore($academicYear->id)],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
        ]);

        $academicYear->update($data);

        return back()->with('success', 'Academic year updated.');
    }

    public function makeCurrent(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->markCurrent();

        return back()->with('success', "{$academicYear->label} is now the current academic year.");
    }

    public function archive(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->is_current) {
            return back()->with('error', 'This is the current academic year. Make another year current before archiving it.');
        }

        if ($academicYear->isArchived()) {
            return back()->with('info', 'That year is already archived.');
        }

        $academicYear->archive();

        return back()->with('success', "{$academicYear->label} archived. Its projects are now in the Archive.");
    }

    public function unarchive(AcademicYear $academicYear): RedirectResponse
    {
        if (! $academicYear->isArchived()) {
            return back()->with('info', 'That year is not archived.');
        }

        $academicYear->unarchive();

        return back()->with('success', "{$academicYear->label} reopened.");
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->loadCount(['projects', 'assignments']);

        if ($academicYear->projects_count > 0 || $academicYear->assignments_count > 0) {
            return back()->with('error', 'This year has projects or assignments filed under it and cannot be deleted. Archive it instead.');
        }

        if ($academicYear->is_current) {
            return back()->with('error', 'The current academic year cannot be deleted.');
        }

        $label = $academicYear->label;
        $academicYear->delete();

        return back()->with('success', "Academic year {$label} deleted.");
    }
}
