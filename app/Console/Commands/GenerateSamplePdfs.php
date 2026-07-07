<?php

namespace App\Console\Commands;

use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class GenerateSamplePdfs extends Command
{
    protected $signature = 'pdf:samples';

    protected $description = 'Render the three PDF design samples into public/samples for preview.';

    public function handle(): int
    {
        $college = config('college');
        $project = Project::with('members.student.user', 'department', 'assignment.faculty.user')
            ->whereNotNull('marks')
            ->first();

        if (! $project) {
            $this->error('No graded project found. Run: php artisan db:seed --class=DemoSeeder');

            return self::FAILURE;
        }

        $dir = public_path('samples');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $data = ['project' => $project, 'college' => $college, 'generatedAt' => now()->format('d M Y, H:i')];

        foreach (['modern', 'formal', 'elegant'] as $design) {
            $pdf = Pdf::loadView("pdf.designs.{$design}", $data)->setPaper('a4');
            file_put_contents("{$dir}/design-{$design}.pdf", $pdf->output());
            $this->info("Wrote samples/design-{$design}.pdf");
        }

        return self::SUCCESS;
    }
}
