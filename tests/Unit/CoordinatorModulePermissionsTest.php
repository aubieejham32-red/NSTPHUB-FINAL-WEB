<?php

namespace Tests\Unit;

use App\Models\Coordinator;
use App\Support\CoordinatorModulePermissions;
use PHPUnit\Framework\TestCase;

class CoordinatorModulePermissionsTest extends TestCase
{
    public function test_each_coordinator_role_can_change_only_its_assigned_modules(): void
    {
        $coordinator = new class extends Coordinator {
            public string $assignedRole = '';

            public function isAttendanceCoordinator(): bool
            {
                return $this->assignedRole === 'attendance';
            }

            public function isAnnouncementCoordinator(): bool
            {
                return $this->assignedRole === 'announcement';
            }

            public function isScheduleCoordinator(): bool
            {
                return $this->assignedRole === 'schedule';
            }
        };

        $cases = [
            'attendance' => ['attendance', 'excuse-letters'],
            'announcement' => ['announcements'],
            'schedule' => ['schedules'],
            '' => [],
        ];

        $modules = [
            'attendance',
            'excuse-letters',
            'announcements',
            'schedules',
            'students',
            'unknown-module',
        ];

        foreach ($cases as $role => $allowedModules) {
            $coordinator->assignedRole = $role;

            foreach ($modules as $module) {
                $this->assertSame(
                    in_array($module, $allowedModules, true),
                    CoordinatorModulePermissions::canManage($coordinator, $module),
                    "Role {$role} and module {$module} have the wrong permission."
                );
            }
        }
    }
}
