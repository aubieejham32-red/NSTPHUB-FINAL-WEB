<?php

namespace Tests\Feature;

use App\Models\University;
use App\Models\UniversityAdministrator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityAdminStudentDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function university(string $suffix): University
    {
        return University::create([
            'name' => 'University '.$suffix,
            'acronym' => 'U'.$suffix,
            'type' => 'Public',
            'campus_type' => 'Main Campus',
            'email' => 'university'.$suffix.'@example.test',
            'region' => 'Caraga',
            'province' => 'Surigao del Norte',
            'city' => 'Surigao City',
            'barangay' => 'San Juan',
            'zip_code' => '8400',
            'complete_address' => 'Surigao City',
            'academic_year' => '2026-2027',
            'semester' => 'First',
            'components' => ['CWTS', 'LTS', 'ROTC'],
            'max_students' => 100,
            'access_code' => 'CODE-'.$suffix,
        ]);
    }

    private function universityAdmin(University $university): UniversityAdministrator
    {
        return UniversityAdministrator::create([
            'university_id' => $university->id,
            'first_name' => 'University',
            'last_name' => 'Administrator',
            'email' => 'admin'.$university->id.'@example.test',
            'phone' => '09123456789',
            'username' => 'admin'.$university->id,
            'password' => 'SecurePassword123!',
        ]);
    }

    private function approvedStudent(University $university): User
    {
        $student = User::factory()->create([
            'university_id' => $university->id,
            'registration_status' => 'approved',
            'surname' => 'Student',
            'first_name' => 'Test',
        ]);

        return $student;
    }

    public function test_university_admin_can_delete_approved_student_in_their_own_university(): void
    {
        $university = $this->university('A');
        $admin = $this->universityAdmin($university);
        $student = $this->approvedStudent($university);

        $this->actingAs($admin, 'university_admin')
            ->delete(route('university-admin.students.destroy', $student->id))
            ->assertRedirect(route('university-admin.students.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }

    public function test_university_admin_cannot_delete_another_universitys_student(): void
    {
        $universityA = $this->university('A');
        $universityB = $this->university('B');
        $admin = $this->universityAdmin($universityA);
        $otherStudent = $this->approvedStudent($universityB);

        $this->actingAs($admin, 'university_admin')
            ->delete(route('university-admin.students.destroy', $otherStudent->id))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $otherStudent->id]);
    }

    public function test_university_admin_cannot_delete_unapproved_student_from_enrolled_list(): void
    {
        $university = $this->university('A');
        $admin = $this->universityAdmin($university);
        $student = $this->approvedStudent($university);
        $student->update(['registration_status' => 'pending']);

        $this->actingAs($admin, 'university_admin')
            ->delete(route('university-admin.students.destroy', $student->id))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $student->id]);
    }

    public function test_guest_cannot_delete_student(): void
    {
        $university = $this->university('A');
        $student = $this->approvedStudent($university);

        $this->delete(route('university-admin.students.destroy', $student->id))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $student->id]);
    }
}
