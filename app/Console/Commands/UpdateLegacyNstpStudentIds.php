<?php

namespace App\Console\Commands;

use App\Models\RotcStudentProfile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateLegacyNstpStudentIds extends Command
{
    protected $signature = 'nstp:upgrade-student-ids {--apply : Commit the changes (default is a preview)}';

    protected $description = 'Preview or update legacy NSTP-YY-NNNNN IDs with the university acronym';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $changed = 0;
        $skipped = 0;

        // Intentionally only match the old ID format. Never modify IDs already upgraded.
        User::query()
            ->whereNotNull('student_id_number')
            ->with('university')
            ->orderBy('id')
            ->chunkById(200, function ($students) use ($apply, &$changed, &$skipped) {
                foreach ($students as $student) {
                    $old = (string) $student->student_id_number;
                    if (!preg_match('/^NSTP-(\d{2})-(\d{5,})$/', $old, $matches)) {
                        continue;
                    }

                    $acronym = strtoupper(preg_replace(
                        '/[^A-Za-z0-9]/',
                        '',
                        (string) ($student->university?->acronym ?? '')
                    ));

                    if ($acronym === '') {
                        $this->warn("SKIP user {$student->id}: missing university acronym");
                        $skipped++;
                        continue;
                    }

                    $next = "NSTP-{$acronym}{$matches[1]}-{$matches[2]}";
                    if (User::query()->where('student_id_number', $next)
                        ->whereKeyNot($student->id)->exists()) {
                        $this->warn("SKIP user {$student->id}: ID {$next} is already assigned");
                        $skipped++;
                        continue;
                    }

                    $this->line("{$old} => {$next}");
                    if ($apply) {
                        DB::transaction(function () use ($student, $old, $next) {
                            $locked = User::query()->whereKey($student->id)
                                ->lockForUpdate()->firstOrFail();
                            if ($locked->student_id_number !== $old) {
                                throw new \RuntimeException('Student ID changed during conversion.');
                            }
                            $locked->student_id_number = $next;
                            $locked->save();

                            // Keep the ROTC profile consistent with the main student account.
                            RotcStudentProfile::query()
                                ->where('user_id', $student->id)
                                ->where('nstp_id_no', $old)
                                ->update(['nstp_id_no' => $next]);
                        });
                    }
                    $changed++;
                }
            });

        $this->info(($apply ? 'Updated' : 'Would update') . " {$changed} student ID(s); skipped {$skipped}.");
        if (!$apply) {
            $this->comment('Preview only. Run with --apply after backing up the database.');
        }
        return self::SUCCESS;
    }
}
