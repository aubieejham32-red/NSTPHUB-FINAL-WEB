<?php

namespace App\Support;

use App\Models\Coordinator;

/**
 * A coordinator may write only in the modules assigned to their Spatie role.
 * Every other NSTP module remains available through its read-only routes.
 */
final class CoordinatorModulePermissions
{
    public static function canManage(Coordinator $coordinator, string $module): bool
    {
        return match ($module) {
            'attendance', 'excuse-letters' => $coordinator->isAttendanceCoordinator(),
            'announcements' => $coordinator->isAnnouncementCoordinator(),
            'schedules' => $coordinator->isScheduleCoordinator(),
            // Student records can be edited by instructors and university admins,
            // but the Student Information module is view-only for coordinators.
            'students' => false,
            default => false,
        };
    }
}
