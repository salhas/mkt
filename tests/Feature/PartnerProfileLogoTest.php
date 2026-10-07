<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PartnerProfileLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_can_upload_logo(): void
    {
        Storage::fake('public');

        $partner = Partner::create([
            'code' => 'MTR-PMI-001',
            'name' => 'PMI Unhas',
            'category' => 'PMI',
            'pic_name' => 'Ketua Relawan',
            'pic_phone' => '081234567890',
            'email' => 'pmi@unhas.ac.id',
            'status' => 'Aktif',
        ]);

        $user = User::factory()->create([
            'role' => 'mitra',
            'email' => 'pmi@unhas.ac.id',
            'partner_id' => $partner->id,
        ]);

        $file = UploadedFile::fake()->image('logo-pmi.png', 300, 300);

        $response = $this->actingAs($user)->post(route('partner.profile.update'), [
            'name' => 'PMI Unhas Makassar',
            'category' => 'PMI',
            'pic_name' => 'Ketua Relawan',
            'pic_phone' => '081234567890',
            'email' => 'pmi@unhas.ac.id',
            'logo' => $file,
        ]);

        $response->assertSessionHas('success');

        $partner->refresh();
        $this->assertNotNull($partner->logo_path);
        $this->assertStringStartsWith('/storage/partners/logos/', $partner->logo_path);

        $savedFileName = str_replace('/storage/', '', $partner->logo_path);
        Storage::disk('public')->assertExists($savedFileName);
    }

    public function test_mitra_can_replace_and_delete_old_logo(): void
    {
        Storage::fake('public');

        // Create initial logo
        $initialFile = UploadedFile::fake()->image('old-logo.png');
        $oldPath = $initialFile->store('partners/logos', 'public');

        $partner = Partner::create([
            'code' => 'MTR-PMI-001',
            'name' => 'PMI Unhas',
            'category' => 'PMI',
            'pic_name' => 'Ketua Relawan',
            'pic_phone' => '081234567890',
            'email' => 'pmi@unhas.ac.id',
            'status' => 'Aktif',
            'logo_path' => '/storage/' . $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $user = User::factory()->create([
            'role' => 'mitra',
            'email' => 'pmi@unhas.ac.id',
            'partner_id' => $partner->id,
        ]);

        // Upload new logo
        $newFile = UploadedFile::fake()->image('new-logo.png');

        $response = $this->actingAs($user)->post(route('partner.profile.update'), [
            'name' => 'PMI Unhas',
            'category' => 'PMI',
            'pic_name' => 'Ketua Relawan',
            'pic_phone' => '081234567890',
            'email' => 'pmi@unhas.ac.id',
            'logo' => $newFile,
        ]);

        $response->assertSessionHas('success');

        $partner->refresh();
        $newSavedFileName = str_replace('/storage/', '', $partner->logo_path);

        // Old file must be deleted, new file must exist
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newSavedFileName);
    }

    public function test_mitra_can_remove_logo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png');
        $path = $file->store('partners/logos', 'public');

        $partner = Partner::create([
            'code' => 'MTR-PMI-001',
            'name' => 'PMI Unhas',
            'category' => 'PMI',
            'pic_name' => 'Ketua Relawan',
            'pic_phone' => '081234567890',
            'email' => 'pmi@unhas.ac.id',
            'status' => 'Aktif',
            'logo_path' => '/storage/' . $path,
        ]);

        Storage::disk('public')->assertExists($path);

        $user = User::factory()->create([
            'role' => 'mitra',
            'email' => 'pmi@unhas.ac.id',
            'partner_id' => $partner->id,
        ]);

        $response = $this->actingAs($user)->post(route('partner.profile.update'), [
            'name' => 'PMI Unhas',
            'category' => 'PMI',
            'pic_name' => 'Ketua Relawan',
            'pic_phone' => '081234567890',
            'email' => 'pmi@unhas.ac.id',
            'remove_logo' => true,
        ]);

        $response->assertSessionHas('success');

        $partner->refresh();
        $this->assertNull($partner->logo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_logo_validation_fails_for_non_image(): void
    {
        Storage::fake('public');

        $partner = Partner::create([
            'code' => 'MTR-PMI-001',
            'name' => 'PMI Unhas',
            'category' => 'PMI',
            'pic_name' => 'Ketua Relawan',
            'pic_phone' => '081234567890',
            'email' => 'pmi@unhas.ac.id',
            'status' => 'Aktif',
        ]);

        $user = User::factory()->create([
            'role' => 'mitra',
            'email' => 'pmi@unhas.ac.id',
            'partner_id' => $partner->id,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)->post(route('partner.profile.update'), [
            'name' => 'PMI Unhas',
            'category' => 'PMI',
            'pic_name' => 'Ketua Relawan',
            'pic_phone' => '081234567890',
            'email' => 'pmi@unhas.ac.id',
            'logo' => $file,
        ]);

        $response->assertSessionHasErrors('logo');
    }
}
