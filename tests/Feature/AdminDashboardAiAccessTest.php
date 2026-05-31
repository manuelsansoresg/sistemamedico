<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

it('shows the artificial intelligence access card to root users', function (): void {
    $this->withoutVite();

    Role::firstOrCreate(['name' => 'root']);

    $root = User::factory()->create();
    $root->assignRole('root');

    $this->actingAs($root)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('ai.config'), false)
        ->assertSee(__('dashboard.card_labels.ai_management'));
});
