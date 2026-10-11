<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ClassPicker
{
    public static function build(Collection $items, string $noun, callable $semesterOf, callable $chips): array
    {
        $count = fn (int $n) => $n.' '.Str::plural($noun, $n);

        $departments = [];
        $semesters = [];

        $byDepartment = $items->groupBy('department_id')
            ->sortBy(fn (Collection $rows) => $rows->first()->department?->name ?? '');

        foreach ($byDepartment as $departmentId => $rows) {
            $departments[] = [
                'id' => (int) $departmentId,
                'name' => ($rows->first()->department?->name ?? 'No department').' — '.$count($rows->count()),
            ];

            $options = [[
                'value' => '',
                'label' => 'All semesters — '.$count($rows->count()),
                'chips' => $chips($rows),
            ]];

            $bySemester = $rows->groupBy(fn ($row) => $semesterOf($row)[0])
                ->sortBy(fn (Collection $group, $value) => is_numeric($value) ? (int) $value : -1);

            foreach ($bySemester as $value => $group) {
                $options[] = [
                    'value' => (string) $value,
                    'label' => $semesterOf($group->first())[1].' — '.$count($group->count()),
                    'chips' => $chips($group),
                ];
            }

            $semesters[(int) $departmentId] = $options;
        }

        return ['departments' => $departments, 'semesters' => $semesters];
    }
}
