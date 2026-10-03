<?php

namespace Tests\Feature;

use App\Models\Opt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function createPopt(): User
    {
        return User::factory()->create(['role' => 'popt']);
    }

    public function test_forbids_popt_from_accessing_index(): void
    {
        $response = $this->actingAs($this->createPopt())->get('/opt');
        
        $response->assertForbidden();
    }

    public function test_renders_index_for_admin(): void
    {
        $response = $this->actingAs($this->createAdmin())->get('/opt');
        
        $response->assertOk();
        $response->assertViewIs('master.opt.index');
    }

    public function test_renders_filtered_index_based_on_search(): void
    {
        Opt::create(['nama_opt' => 'Wereng Coklat']);
        Opt::create(['nama_opt' => 'Belalang']);

        $response = $this->actingAs($this->createAdmin())->get('/opt?search=Wereng');

        $response->assertOk();
        $response->assertSee('Wereng Coklat');
    }

    public function test_rejects_creation_when_nama_opt_is_empty(): void
    {
        $response = $this->actingAs($this->createAdmin())->post('/opt', []);
        
        $response->assertSessionHasErrors(['nama_opt']);
    }

    public function test_rejects_creation_when_nama_opt_is_duplicate(): void
    {
        Opt::create(['nama_opt' => 'Penggerek Batang']);

        $response = $this->actingAs($this->createAdmin())->post('/opt', [
            'nama_opt' => 'Penggerek Batang',
        ]);
        
        $response->assertSessionHasErrors(['nama_opt']);
    }

    public function test_creates_opt_and_redirects(): void
    {
        $response = $this->actingAs($this->createAdmin())->post('/opt', [
            'nama_opt' => 'Keong Mas',
        ]);

        $response->assertRedirect(route('opt.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('opts', [
            'nama_opt' => 'Keong Mas',
        ]);
    }

    public function test_updates_opt_and_redirects(): void
    {
        $opt = Opt::create(['nama_opt' => 'Ulat Grayak']);

        $response = $this->actingAs($this->createAdmin())->put("/opt/{$opt->id}", [
            'nama_opt' => 'Walang Sangit',
        ]);

        $response->assertRedirect(route('opt.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('opts', [
            'id' => $opt->id,
            'nama_opt' => 'Walang Sangit',
        ]);
    }

    public function test_deletes_opt_and_redirects(): void
    {
        $opt = Opt::create(['nama_opt' => 'Burung']);

        $response = $this->actingAs($this->createAdmin())->delete("/opt/{$opt->id}");

        $response->assertRedirect(route('opt.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('opts', [
            'id' => $opt->id,
        ]);
    }
}
