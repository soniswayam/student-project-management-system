<?php

namespace App\Support;

use App\Models\CollegeSetting;
use App\Models\Project;
use App\Models\ProjectSimilarity;

class PlagiarismChecker
{
    private const KEEP_MATCHES = 5;

    private const FLOOR_SCORE = 15.0;

    public static function checkSynopsis(Project $project): array
    {
        $candidates = Project::query()
            ->where('id', '!=', $project->id)
            ->with('academicYear')
            ->get()
            ->map(fn (Project $other) => [
                'id' => $other->id,
                'label' => $other->name,
                'text' => self::synopsisText($other),
                'project' => $other,
            ]);

        $matches = SimilarityService::rank(
            self::synopsisText($project),
            $candidates,
            self::FLOOR_SCORE,
            self::KEEP_MATCHES
        );

        self::record($project, $matches, ProjectSimilarity::STAGE_SYNOPSIS);

        return ['top' => $matches[0] ?? null, 'matches' => $matches];
    }

    public static function checkReport(Project $project, ?string $reportText): array
    {
        if (! $reportText) {
            return ['top' => null, 'matches' => []];
        }

        $candidates = Project::query()
            ->where('id', '!=', $project->id)
            ->whereHas('submission', fn ($q) => $q->whereNotNull('report_text'))
            ->with('submission')
            ->get()
            ->map(fn (Project $other) => [
                'id' => $other->id,
                'label' => $other->name,
                'text' => $other->submission?->report_text,
            ]);

        $matches = SimilarityService::rank(
            $reportText,
            $candidates,
            self::FLOOR_SCORE,
            self::KEEP_MATCHES
        );

        self::record($project, $matches, ProjectSimilarity::STAGE_REPORT);

        return ['top' => $matches[0] ?? null, 'matches' => $matches];
    }

    public static function screenNewSynopsis(string $name, string $abstract, ?int $ignoreProjectId = null): array
    {
        $candidates = Project::query()
            ->when($ignoreProjectId, fn ($q) => $q->where('id', '!=', $ignoreProjectId))
            ->get()
            ->map(fn (Project $other) => [
                'id' => $other->id,
                'label' => $other->name,
                'text' => self::synopsisText($other),
            ]);

        $matches = SimilarityService::rank(
            self::combine($name, $abstract),
            $candidates,
            self::FLOOR_SCORE,
            self::KEEP_MATCHES
        );

        $top = $matches[0] ?? null;
        $score = $top['score'] ?? 0.0;
        $blockAt = CollegeSetting::blockPercent();

        return [
            'blocked' => $blockAt < 100 && $score >= $blockAt,
            'warn' => $score >= CollegeSetting::warnPercent(),
            'top' => $top,
        ];
    }

    private static function synopsisText(Project $project): string
    {
        return self::combine($project->name, $project->abstract);
    }

    private static function combine(?string $name, ?string $abstract): string
    {
        return trim(($name ?? '').' '.($abstract ?? ''));
    }

    private static function record(Project $project, array $matches, string $stage): void
    {
        ProjectSimilarity::where('project_id', $project->id)
            ->where('stage', $stage)
            ->delete();

        foreach ($matches as $match) {
            ProjectSimilarity::create([
                'project_id' => $project->id,
                'matched_project_id' => $match['id'],
                'stage' => $stage,
                'score' => $match['score'],
                'details' => [
                    'phrases' => $match['phrases'],
                    'shared' => $match['shared'],
                    'matched_name' => $match['label'],
                ],
            ]);
        }
    }
}
