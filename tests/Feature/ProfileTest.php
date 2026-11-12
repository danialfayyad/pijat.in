<?php

use App\Enums\Role;
use App\Models\AccountDeletionRequest;
use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user without active blockers can submit account deletion request', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.delete'))
        ->delete('/profile', [
            'password' => 'password',
            'reason' => 'Sudah tidak menggunakan layanan.',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'deletion-requested')
        ->assertRedirect(route('profile.delete'));

    $this->assertAuthenticatedAs($user);
    $this->assertNotNull($user->fresh());
    $this->assertDatabaseHas('account_deletion_requests', [
        'user_id' => $user->id,
        'status' => AccountDeletionRequest::STATUS_PENDING,
        'reason' => 'Sudah tidak menggunakan layanan.',
    ]);
});

test('user with wallet balance cannot submit account deletion request', function () {
    $user = User::factory()->create();
    $user->wallet()->create(['balance' => 50000, 'held_balance' => 0, 'currency' => 'IDR']);

    $response = $this
        ->actingAs($user)
        ->from(route('profile.delete'))
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'eligibility')
        ->assertRedirect(route('profile.delete'));

    $this->assertDatabaseMissing('account_deletion_requests', [
        'user_id' => $user->id,
    ]);
});

test('superadmin can approve or reject account deletion request', function () {
    $admin = User::factory()->create(['role' => Role::Superadmin]);

    $userToApprove = User::factory()->create();
    $approveReq = AccountDeletionRequest::create([
        'user_id' => $userToApprove->id,
        'status' => AccountDeletionRequest::STATUS_PENDING,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.users.deletion-requests.approve', $approveReq))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $this->assertNull($userToApprove->fresh());

    $userToReject = User::factory()->create();
    $rejectReq = AccountDeletionRequest::create([
        'user_id' => $userToReject->id,
        'status' => AccountDeletionRequest::STATUS_PENDING,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.users.deletion-requests.reject', $rejectReq), [
            'rejection_reason' => 'Masih ada verifikasi tertunda.',
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $this->assertNotNull($userToReject->fresh());
    $this->assertSame(AccountDeletionRequest::STATUS_REJECTED, $rejectReq->fresh()->status);
    $this->assertSame('Masih ada verifikasi tertunda.', $rejectReq->fresh()->rejection_reason);
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

test('profile sub-pages are displayed for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('profile.personal'))->assertOk();
    $this->actingAs($user)->get(route('profile.security'))->assertOk();
    $this->actingAs($user)->get(route('profile.delete'))->assertOk();
});

test('personal information can include phone, gender, and date of birth', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.personal'))
        ->patch('/profile', [
            'name' => 'Siti Aminah',
            'email' => $user->email,
            'phone' => '081234567899',
            'gender' => 'wanita',
            'date_of_birth' => '1995-05-15',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.personal'));

    $user->refresh();

    $this->assertSame('Siti Aminah', $user->name);
    $this->assertSame('081234567899', $user->phone);
    $this->assertSame('wanita', $user->gender);
    $this->assertSame('1995-05-15', $user->date_of_birth?->format('Y-m-d'));
});
