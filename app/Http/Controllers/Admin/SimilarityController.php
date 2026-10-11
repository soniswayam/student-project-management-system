<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollegeSetting;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectSimilarity;
use App\Support\SimilarityService;
use App\Support\SpreadsheetExporter;
use Illuminate\Http\Request;

class SimilarityController extends Controller
{
    public function index(Request $request)
    {
        $findings = $this->filtered($request)
            ->with('project.members.student.user', 'project.department', 'project.academicYear', 'matchedProject.academicYear')
            ->orderByDesc('score')
            ->paginate(20)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return view('admin.similarity.index', [
            'findings' => $findings,
            'departments' => $departments,
            'warnPercent' => CollegeSetting::warnPercent(),
            'blockPercent' => CollegeSetting::blockPercent(),
            'highCount' => ProjectSimilarity::where('score', '>=', CollegeSetting::blockPercent())->count(),
            'reviewCount' => ProjectSimilarity::where('score', '>=', CollegeSetting::warnPercent())
                ->where('score', '<', CollegeSetting::blockPercent())->count(),
        ]);
    }

    public function show(ProjectSimilarity $similarity)
    {
        $similarity->load(
            'project.members.student.user', 'project.department', 'project.academicYear', 'project.submission',
            'matchedProject.members.student.user', 'matchedProject.department', 'matchedProject.academicYear', 'matchedProject.submission'
        );

        $left = $this->stageText($similarity->project, $similarity->stage);
        $right = $this->stageText($similarity->matchedProject, $similarity->stage);
        $live = SimilarityService::compare($left, $right);

        return view('admin.similarity.show', compact('similarity', 'left', 'right', 'live'));
    }

    public function export(Request $request)
    {
        $findings = $this->filtered($request)
            ->with('project.members.student.user', 'project.department', 'matchedProject')
            ->orderByDesc('score')
            ->get();

        $rows = $findings->map(fn (ProjectSimilarity $f) => [
            $f->project?->name ?? '—',
            $f->project?->memberNames() ?: '—',
            $f->project?->department?->name ?? '—',
            $f->matchedProject?->name ?? '—',
            ucfirst($f->stage),
            number_format($f->score, 2).'%',
            $f->severityLabel(),
            $f->created_at?->format('d M Y'),
        ])->toArray();

        return SpreadsheetExporter::download(
            'similarity-report-'.now()->format('Y-m-d').'.xlsx',
            'Similarity Report — '.config('college.name'),
            ['Project', 'Group', 'Department', 'Matched With', 'Stage', 'Score', 'Severity', 'Found'],
            $rows
        );
    }

    private function filtered(Request $request)
    {
        $minScore = $request->filled('min_score')
            ? (float) $request->min_score
            : CollegeSetting::warnPercent();

        $query = ProjectSimilarity::where('score', '>=', $minScore);

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('project', fn ($p) => $p->where('department_id', $request->department_id));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('project', fn ($p) => $p->where('name', 'like', "%{$search}%"));
        }

        return $query;
    }

    private function stageText(?Project $project, string $stage): ?string
    {
        if (! $project) {
            return null;
        }

        return $stage === ProjectSimilarity::STAGE_REPORT
            ? $project->submission?->report_text
            : trim($project->name.' '.$project->abstract);
    }
}
