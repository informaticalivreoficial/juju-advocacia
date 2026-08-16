<?php

namespace Tests\Feature\Admin;

use App\Enums\CalendarEventTypeEnum;
use App\Enums\UserRoleEnum;
use App\Models\CalendarEvent;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function lawyer(): User
    {
        return User::factory()->role(UserRoleEnum::Lawyer)->create();
    }

    private function payload(array $overrides = []): array
    {
        $responsible = User::factory()->role(UserRoleEnum::Lawyer)->create();

        return array_merge([
            'responsible_user_id' => $responsible->id,
            'title' => 'Audiência de conciliação',
            'type' => CalendarEventTypeEnum::Hearing->value,
            'start_datetime' => now()->addDays(2)->format('Y-m-d 10:00:00'),
            'end_datetime' => now()->addDays(2)->format('Y-m-d 11:00:00'),
            'all_day' => false,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/calendar')->assertRedirect('/login');
    }

    public function test_lawyer_can_view_calendar_index(): void
    {
        $this->actingAs($this->lawyer())->get('/admin/calendar')->assertOk();
    }

    public function test_lawyer_can_create_event(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/calendar', $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('calendar_events', ['title' => 'Audiência de conciliação']);
    }

    public function test_title_is_required(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/calendar', $this->payload(['title' => '']))
            ->assertSessionHasErrors('title');
    }

    public function test_start_datetime_is_required(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/calendar', $this->payload(['start_datetime' => '']))
            ->assertSessionHasErrors('start_datetime');
    }

    public function test_end_must_be_after_start(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/calendar', $this->payload([
                'start_datetime' => now()->addDays(2)->format('Y-m-d 11:00:00'),
                'end_datetime' => now()->addDays(2)->format('Y-m-d 10:00:00'),
            ]))
            ->assertSessionHasErrors('end_datetime');
    }

    public function test_lawyer_can_update_event(): void
    {
        $event = CalendarEvent::factory()->create();

        $this->actingAs($this->lawyer())
            ->put("/admin/calendar/{$event->id}", $this->payload([
                'title' => 'Título atualizado',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('calendar_events', [
            'id' => $event->id,
            'title' => 'Título atualizado',
        ]);
    }

    public function test_lawyer_cannot_delete_event(): void
    {
        $event = CalendarEvent::factory()->create();

        $this->actingAs($this->lawyer())
            ->delete("/admin/calendar/{$event->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('calendar_events', ['id' => $event->id]);
    }

    public function test_admin_can_delete_event(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $event = CalendarEvent::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/calendar/{$event->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }
}
