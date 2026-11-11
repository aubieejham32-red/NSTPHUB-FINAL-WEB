<?php

namespace Tests\Feature;

use App\Http\Middleware\EnforceCoordinatorModulePermission;
use App\Models\Coordinator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CoordinatorModuleMiddlewareTest extends TestCase
{
    public function test_coordinator_cannot_write_outside_assigned_module(): void
    {
        $actor = new class extends Coordinator {
            public function isAttendanceCoordinator(): bool
            {
                return false;
            }

            public function isAnnouncementCoordinator(): bool
            {
                return true;
            }

            public function isScheduleCoordinator(): bool
            {
                return false;
            }
        };

        $request = Request::create(
            '/instructor-coordinator/schedules',
            'POST'
        );

        $request->setUserResolver(fn () => $actor);

        try {
            (new EnforceCoordinatorModulePermission())->handle(
                $request,
                fn () => new Response('Incorrectly allowed'),
                'schedules'
            );

            $this->fail(
                'An announcement coordinator must not change schedules.'
            );
        } catch (HttpException $exception) {
            $this->assertSame(
                403,
                $exception->getStatusCode()
            );
        }
    }

    public function test_assigned_coordinator_can_write_to_their_module(): void
    {
        $actor = new class extends Coordinator {
            public function isAttendanceCoordinator(): bool
            {
                return true;
            }

            public function isAnnouncementCoordinator(): bool
            {
                return false;
            }

            public function isScheduleCoordinator(): bool
            {
                return false;
            }
        };

        $request = Request::create(
            '/instructor-coordinator/excuse-letters/1/approve',
            'PATCH'
        );

        $request->setUserResolver(fn () => $actor);

        $response = (new EnforceCoordinatorModulePermission())->handle(
            $request,
            fn () => new Response('Allowed'),
            'excuse-letters'
        );

        $this->assertSame(
            200,
            $response->getStatusCode()
        );
    }

    public function test_instructors_and_administrators_are_not_restricted(): void
    {
        $request = Request::create(
            '/instructor-coordinator/students/1',
            'PUT'
        );

        $request->setUserResolver(
            fn () => new \stdClass()
        );

        $response = (new EnforceCoordinatorModulePermission())->handle(
            $request,
            fn () => new Response('Allowed'),
            'students'
        );

        $this->assertSame(
            200,
            $response->getStatusCode()
        );
    }
}
