<?php

namespace Tests\Feature;

use App\Models\JenisOpt;
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

    public function test_popt_can_view_opt_index_with_management_controls(): void
    {
        Opt::create(['nama_opt' => 'Gugur Daun']);
        $response = $this->actingAs($this->createPopt())->get('/opt');

        $response->assertOk();
        $response->assertSee('Master OPT');
        $response->assertSee('Gugur Daun');
        $response->assertSee('Tambah OPT');
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

    public function test_popt_can_perform_opt_and_child_type_crud(): void
    {
        $popt = $this->createPopt();
        $this->actingAs($popt)->post('/opt', [
            'nama_opt' => 'OPT Petugas',
            'jenis_opts' => ['Jenis Awal'],
        ])->assertRedirect(route('opt.index'));

        $opt = Opt::where('nama_opt', 'OPT Petugas')->firstOrFail();
        $jenis = JenisOpt::where('opt_id', $opt->id)->firstOrFail();
        $this->assertDatabaseHas('jenis_opts', ['id' => $jenis->id, 'nama_jenis_opt' => 'Jenis Awal']);

        $this->actingAs($popt)->put("/opt/{$opt->id}", [
            'nama_opt' => 'OPT Petugas Diperbarui',
            'jenis_opts_present' => '1',
            'jenis_opts' => [['id' => $jenis->id, 'nama_jenis_opt' => 'Jenis Diperbarui']],
        ])->assertRedirect(route('opt.index'));
        $this->assertDatabaseHas('opts', ['id' => $opt->id, 'nama_opt' => 'OPT Petugas Diperbarui']);
        $this->assertDatabaseHas('jenis_opts', ['id' => $jenis->id, 'nama_jenis_opt' => 'Jenis Diperbarui']);

        $this->actingAs($popt)->post(route('opt.jenis.store', $opt), ['nama_jenis_opt' => 'Jenis Tambahan'])->assertRedirect(route('opt.index'));
        $tambahan = JenisOpt::where('opt_id', $opt->id)->where('nama_jenis_opt', 'Jenis Tambahan')->firstOrFail();
        $this->actingAs($popt)->put(route('opt.jenis.update', [$opt, $tambahan]), ['nama_jenis_opt' => 'Jenis Tambahan Ubah'])->assertRedirect(route('opt.index'));
        $this->actingAs($popt)->delete(route('opt.jenis.destroy', [$opt, $tambahan]))->assertRedirect(route('opt.index'));
        $this->actingAs($popt)->delete("/opt/{$opt->id}")->assertRedirect(route('opt.index'));

        $this->assertDatabaseMissing('opts', ['id' => $opt->id]);
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

    public function test_creates_opt_with_multiple_child_types(): void
    {
        $response = $this->actingAs($this->createAdmin())->post('/opt', [
            'nama_opt' => 'Gugur Daun',
            'jenis_opts' => ['Corynespora cassiicola', 'Jenis Kedua'],
        ]);

        $response->assertRedirect(route('opt.index'));
        $opt = Opt::where('nama_opt', 'Gugur Daun')->firstOrFail();
        $this->assertDatabaseHas('jenis_opts', [
            'opt_id' => $opt->id,
            'nama_jenis_opt' => 'Corynespora cassiicola',
        ]);
        $this->assertDatabaseHas('jenis_opts', [
            'opt_id' => $opt->id,
            'nama_jenis_opt' => 'Jenis Kedua',
        ]);
    }

    public function test_admin_can_manage_child_opt_types(): void
    {
        $admin = $this->createAdmin();
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $response = $this->actingAs($admin)->post(route('opt.jenis.store', $opt), ['nama_jenis_opt' => 'Corynespora cassiicola']);
        $response->assertRedirect(route('opt.index'));
        $jenis = JenisOpt::firstOrFail();
        $this->assertSame($opt->id, $jenis->opt_id);

        $this->put(route('opt.jenis.update', [$opt, $jenis]), ['nama_jenis_opt' => 'Corynespora'])->assertRedirect(route('opt.index'));
        $this->delete(route('opt.jenis.destroy', [$opt, $jenis]))->assertRedirect(route('opt.index'));
        $this->assertDatabaseMissing('jenis_opts', ['id' => $jenis->id]);
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

    public function test_editing_opt_updates_its_child_types_with_the_same_form(): void
    {
        $admin = $this->createAdmin();
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $jenisPertama = JenisOpt::create(['opt_id' => $opt->id, 'nama_jenis_opt' => 'Jenis Lama 1']);
        $jenisKedua = JenisOpt::create(['opt_id' => $opt->id, 'nama_jenis_opt' => 'Jenis Lama 2']);

        $response = $this->actingAs($admin)->put("/opt/{$opt->id}", [
            'nama_opt' => 'Gugur Daun Karet',
            'jenis_opts_present' => '1',
            'jenis_opts' => [
                ['id' => $jenisPertama->id, 'nama_jenis_opt' => 'Jenis Diperbarui'],
                ['id' => '', 'nama_jenis_opt' => 'Jenis Baru'],
            ],
        ]);

        $response->assertRedirect(route('opt.index'));
        $this->assertDatabaseHas('opts', ['id' => $opt->id, 'nama_opt' => 'Gugur Daun Karet']);
        $this->assertDatabaseHas('jenis_opts', ['id' => $jenisPertama->id, 'nama_jenis_opt' => 'Jenis Diperbarui']);
        $this->assertDatabaseHas('jenis_opts', ['opt_id' => $opt->id, 'nama_jenis_opt' => 'Jenis Baru']);
        $this->assertDatabaseMissing('jenis_opts', ['id' => $jenisKedua->id]);
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
