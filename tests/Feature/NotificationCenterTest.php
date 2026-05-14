<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notificationId = $this->createNotificationFor($user);

        $this->actingAs($user)
            ->patch(route('notifications.read', $notificationId))
            ->assertRedirect();

        $this->assertNotNull(DB::table('notifications')->where('id', $notificationId)->value('read_at'));
    }

    public function test_user_can_delete_own_notification(): void
    {
        $user = User::factory()->create();
        $notificationId = $this->createNotificationFor($user);

        $this->actingAs($user)
            ->delete(route('notifications.destroy', $notificationId))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', ['id' => $notificationId]);
    }

    public function test_opening_notification_marks_it_read_and_redirects_to_action_url(): void
    {
        $user = User::factory()->create();
        $notificationId = $this->createNotificationFor($user, ['action_url' => route('dashboard')]);

        $this->actingAs($user)
            ->get(route('notifications.open', $notificationId))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull(DB::table('notifications')->where('id', $notificationId)->value('read_at'));
    }

    private function createNotificationFor(User $user, array $data = []): string
    {
        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'Tests\\Feature\\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(array_merge([
                'titulo' => 'Aviso de prueba',
                'mensaje' => 'Mensaje de prueba',
            ], $data)),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $notificationId;
    }
}
