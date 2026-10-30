<?php

use App\Enums\Role;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\Order;
use App\Models\Region;
use App\Models\User;

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'name' => 'Superadmin Test',
        'role' => Role::Superadmin,
        'email_verified_at' => now(),
    ]);

    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'role' => Role::Admin,
        'email_verified_at' => now(),
    ]);
});

test('non-superadmin cannot access complaint categories management', function () {
    $this->actingAs($this->admin)
        ->get(route('superadmin.complaint-categories.index'))
        ->assertForbidden();
});

test('superadmin can view complaint categories list', function () {
    $category = ComplaintCategory::factory()->create([
        'name' => 'Masalah Tarif Aplikasi',
        'description' => 'Terapis menagih biaya di luar sistem.',
    ]);

    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.complaint-categories.index'));

    $response->assertOk()
        ->assertSee('Master Kategori Aduan')
        ->assertSee('Masalah Tarif Aplikasi')
        ->assertSee($category->slug);
});

test('superadmin can store a new complaint category with auto slug and penalty points', function () {
    $response = $this->actingAs($this->superadmin)
        ->post(route('superadmin.complaint-categories.store'), [
            'name' => 'Kehilangan Barang Bawaan',
            'penalty_points' => 30,
            'description' => 'Barang berharga pelanggan hilang setelah sesi pijat.',
        ]);

    $response->assertRedirect(route('superadmin.complaint-categories.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('complaint_categories', [
        'name' => 'Kehilangan Barang Bawaan',
        'slug' => 'kehilangan_barang_bawaan',
        'penalty_points' => 30,
        'is_active' => true,
    ]);
});

test('superadmin can update an existing complaint category with penalty points', function () {
    $category = ComplaintCategory::factory()->create([
        'name' => 'Kategori Lama',
        'penalty_points' => 15,
        'description' => 'Deskripsi lama',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.complaint-categories.update', $category), [
            'name' => 'Kategori Diperbarui',
            'penalty_points' => 45,
            'description' => 'Deskripsi baru',
            'is_active' => false,
        ]);

    $response->assertRedirect(route('superadmin.complaint-categories.index'))
        ->assertSessionHas('status');

    $category->refresh();
    expect($category->name)->toBe('Kategori Diperbarui')
        ->and($category->penalty_points)->toBe(45)
        ->and($category->description)->toBe('Deskripsi baru')
        ->and($category->is_active)->toBeFalse();
});

test('superadmin can toggle active status of a category', function () {
    $category = ComplaintCategory::factory()->create(['is_active' => true]);

    $response = $this->actingAs($this->superadmin)
        ->post(route('superadmin.complaint-categories.toggle', $category));

    $response->assertRedirect(route('superadmin.complaint-categories.index'));

    $category->refresh();
    expect($category->is_active)->toBeFalse();

    // Toggle back
    $this->actingAs($this->superadmin)
        ->post(route('superadmin.complaint-categories.toggle', $category));

    $category->refresh();
    expect($category->is_active)->toBeTrue();
});

test('superadmin can delete a category without associated complaints', function () {
    $category = ComplaintCategory::factory()->create(['name' => 'Kategori Kosong']);

    $response = $this->actingAs($this->superadmin)
        ->delete(route('superadmin.complaint-categories.destroy', $category));

    $response->assertRedirect(route('superadmin.complaint-categories.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('complaint_categories', ['id' => $category->id]);
});

test('superadmin deleting category with complaints will safely deactivate it instead', function () {
    $category = ComplaintCategory::factory()->create([
        'name' => 'Kategori Berisi Aduan',
        'is_active' => true,
    ]);

    $region = Region::factory()->create();
    $order = Order::factory()->create(['region_id' => $region->id]);

    Complaint::factory()->create([
        'complaint_category_id' => $category->id,
        'region_id' => $region->id,
        'order_id' => $order->id,
        'category' => $category->slug,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->delete(route('superadmin.complaint-categories.destroy', $category));

    $response->assertRedirect(route('superadmin.complaint-categories.index'));

    $this->assertDatabaseHas('complaint_categories', [
        'id' => $category->id,
        'is_active' => false,
    ]);
});
