<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Opt;

class OptTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $popt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->popt = User::factory()->create(['role' => 'popt']);
    }

    public function test_only_admin_can_access_opt_index()
    {
        // POPT tidak bisa akses
        $responsePopt = $this->actingAs($this->popt)->get('/opt');
        $responsePopt->assertStatus(403);

        // Admin bisa akses
        $responseAdmin = $this->actingAs($this->admin)->get('/opt');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertViewIs('master.opt.index');
    }

    public function test_admin_can_search_opt()
    {
        Opt::create(['nama_opt' => 'Wereng Coklat']);
        Opt::create(['nama_opt' => 'Belalang']);

        $response = $this->actingAs($this->admin)->get('/opt?search=Wereng');
        
        $response->assertStatus(200);
        $response->assertSee('Wereng Coklat');
        // search is now client-side, so all data is returned initially
    }

    public function test_validation_fails_on_store_opt()
    {
        Opt::create(['nama_opt' => 'Penggerek Batang']);

        // Test validasi kosong
        $responseEmpty = $this->actingAs($this->admin)->post('/opt', []);
        $responseEmpty->assertSessionHasErrors(['nama_opt']);

        // Test validasi unik
        $responseDuplicate = $this->actingAs($this->admin)->post('/opt', [
            'nama_opt' => 'Penggerek Batang'
        ]);
        $responseDuplicate->assertSessionHasErrors(['nama_opt']);
    }

    public function test_admin_can_store_opt()
    {
        $response = $this->actingAs($this->admin)->post('/opt', [
            'nama_opt' => 'Keong Mas'
        ]);

        $response->assertRedirect(route('opt.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('opts', [
            'nama_opt' => 'Keong Mas'
        ]);
    }

    public function test_admin_can_update_opt()
    {
        $opt = Opt::create(['nama_opt' => 'Ulat Grayak']);

        $response = $this->actingAs($this->admin)->put("/opt/{$opt->id}", [
            'nama_opt' => 'Walang Sangit'
        ]);

        $response->assertRedirect(route('opt.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('opts', [
            'id' => $opt->id,
            'nama_opt' => 'Walang Sangit'
        ]);
    }

    public function test_admin_can_delete_opt()
    {
        $opt = Opt::create(['nama_opt' => 'Burung']);

        $response = $this->actingAs($this->admin)->delete("/opt/{$opt->id}");

        $response->assertRedirect(route('opt.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('opts', [
            'id' => $opt->id
        ]);
    }
}
