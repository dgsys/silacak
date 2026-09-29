<?php

namespace Tests\Feature;

use App\Enums\ShipmentStatus;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Shipment\ShipmentService;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SilacakTest extends TestCase
{
    use RefreshDatabase;

    private Branch $a;
    private Branch $b;
    private Branch $c;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ServiceSeeder::class);
        $this->a = Branch::create(['kode' => 'AAA001', 'nama' => 'Cabang A', 'kota' => 'Kota A']);
        $this->b = Branch::create(['kode' => 'BBB001', 'nama' => 'Cabang B', 'kota' => 'Kota B']);
        $this->c = Branch::create(['kode' => 'CCC001', 'nama' => 'Cabang C', 'kota' => 'Kota C']);
    }

    private function buatPaket(Branch $asal, Branch $tujuan, bool $isMember = false): Shipment
    {
        $cust = Customer::create(['nama' => 'Pelanggan Uji', 'telepon' => '081234567890']);
        if ($isMember) {
            $cust->forceFill(['is_member' => true])->save();
        }

        return app(ShipmentService::class)->create([
            'service_id' => Service::where('kode', 'REGULER')->value('id'),
            'dest_branch_id' => $tujuan->id,
            'penerima_nama' => 'Sari Utami',
            'penerima_alamat' => 'Jl. Rahasia No. 1',
            'berat_aktual' => '1.30', 'panjang' => 0, 'lebar' => 0, 'tinggi' => 0, 'nilai_barang' => 0,
            'member' => 1,
        ], $cust, $asal->id);
    }

    private function petugas(Branch $branch): User
    {
        return User::create([
            'nama' => 'Petugas '.$branch->kode, 'email' => strtolower($branch->kode).'@uji.test',
            'password' => 'rahasia-uji-123', 'role' => 'cabang', 'branch_id' => $branch->id,
        ]);
    }

    public function test_admin_dapat_mengelola_cabang_dan_pengguna_cabang_dilarang(): void
    {
        $admin = User::create([
            'nama' => 'Admin Uji', 'email' => 'admin@uji.test',
            'password' => 'rahasia-uji-123', 'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('branches.index'))
            ->assertOk()
            ->assertSee('Daftar cabang');

        $this->post(route('branches.store'), ['kode' => 'DDD001', 'nama' => 'Cabang D', 'kota' => 'Kota D'])
            ->assertRedirect(route('branches.index'));
        $branch = Branch::where('kode', 'DDD001')->firstOrFail();

        $this->put(route('branches.update', $branch), ['kode' => 'DDD001', 'nama' => 'Cabang D Baru', 'kota' => 'Kota Baru'])
            ->assertRedirect(route('branches.index'));
        $this->assertSame('Cabang D Baru', $branch->fresh()->nama);

        $this->delete(route('branches.destroy', $branch))->assertRedirect(route('branches.index'));
        $this->assertDatabaseMissing('branches', ['id' => $branch->id]);

        $this->actingAs($this->petugas($this->a))
            ->get(route('branches.index'))
            ->assertForbidden();
    }

    public function test_cabang_yang_digunakan_paket_tidak_dapat_dihapus(): void
    {
        $this->buatPaket($this->a, $this->b);
        $admin = User::create([
            'nama' => 'Admin Uji', 'email' => 'admin-hapus@uji.test',
            'password' => 'rahasia-uji-123', 'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->delete(route('branches.destroy', $this->a))
            ->assertSessionHasErrors('cabang');

        $this->assertDatabaseHas('branches', ['id' => $this->a->id]);
    }

    public function test_halaman_lacak_terbuka(): void
    {
        $this->get('/lacak')->assertOk()->assertSee('Lacak paket');
    }

    public function test_lacak_menampilkan_riwayat_tanpa_data_pribadi(): void
    {
        $p = $this->buatPaket($this->a, $this->b);

        $this->get('/lacak?resi='.$p->resi)
            ->assertOk()
            ->assertSee($p->resi)
            ->assertSee('S*** U****')
            ->assertDontSee('Sari Utami')
            ->assertDontSee('Jl. Rahasia');
    }

    public function test_format_resi_tidak_valid_ditolak(): void
    {
        $this->get('/lacak?resi=%27%20OR%201%3D1--')->assertSessionHasErrors('resi');
    }

    public function test_ongkir_dihitung_server(): void
    {
        $id = Service::where('kode', 'REGULER')->value('id');

        $this->post('/ongkir', ['service_id' => $id, 'berat_aktual' => '1.30'])
            ->assertRedirect(route('ongkir'))
            ->assertSessionHas('hasil.total', 18000);
    }

    public function test_pencarian_pelanggan_membatasi_hasil(): void
    {
        foreach (range(1, 25) as $index) {
            Customer::create([
                'nama' => 'Cari Pelanggan '.$index,
                'telepon' => '081234'.str_pad((string) $index, 6, '0', STR_PAD_LEFT),
            ]);
        }

        $this->actingAs($this->petugas($this->a))
            ->getJson(route('customers.search', ['q' => 'Cari']))
            ->assertOk()
            ->assertJsonCount(20);
    }

    public function test_estimasi_ongkir_menampilkan_diskon_member_dan_asuransi(): void
    {
        $id = Service::where('kode', 'REGULER')->value('id');

        $this->post('/ongkir', [
            'service_id' => $id, 'berat_aktual' => '1.30', 'nilai_barang' => 2000000, 'member' => 1,
        ])->assertRedirect(route('ongkir'))
            ->assertSessionHas('hasil.biaya_dasar', 18000)
            ->assertSessionHas('hasil.diskon', 1800)
            ->assertSessionHas('hasil.asuransi', 4000)
            ->assertSessionHas('hasil.total', 20200);
    }

    public function test_pelanggan_dengan_status_member_null_diperlakukan_sebagai_non_member(): void
    {
        $customer = Customer::create(['nama' => 'Pelanggan Uji', 'telepon' => '081234567890']);
        $customer->forceFill(['is_member' => null]);

        $shipment = app(ShipmentService::class)->create([
            'service_id' => Service::where('kode', 'REGULER')->value('id'),
            'dest_branch_id' => $this->b->id,
            'penerima_nama' => 'Sari Utami',
            'penerima_alamat' => 'Jl. Rahasia No. 1',
            'berat_aktual' => '1.30', 'panjang' => 0, 'lebar' => 0, 'tinggi' => 0, 'nilai_barang' => 0,
        ], $customer, $this->a->id);

        $this->assertSame(18000, $shipment->ongkir);
        $this->assertSame(18000, $shipment->biaya_dasar);
        $this->assertSame(0, $shipment->diskon);
        $this->assertSame(0, $shipment->asuransi);
    }

    public function test_diskon_member_hanya_diberikan_kepada_pelanggan_member(): void
    {
        $memberShipment = $this->buatPaket($this->a, $this->b, true);
        $nonMemberShipment = $this->buatPaket($this->b, $this->c);

        $this->assertSame(1800, $memberShipment->diskon);
        $this->assertSame(16200, $memberShipment->ongkir);
        $this->assertSame(0, $nonMemberShipment->diskon);
        $this->assertSame(18000, $nonMemberShipment->ongkir);
    }

    public function test_form_memuat_token_csrf(): void
    {
        $this->get('/login')->assertOk()->assertSee('name="_token"', false);
        $this->get('/ongkir')->assertOk()->assertSee('name="_token"', false);
    }

    public function test_middleware_csrf_aktif_di_grup_web(): void
    {
        // Laravel mematikan CSRF otomatis saat testing, jadi yang dicek adalah konfigurasinya.
        $web = app('router')->getMiddlewareGroups()['web'];
        $kelas = array_filter([
            ValidateCsrfToken::class,
            'Illuminate\Foundation\Http\Middleware\PreventRequestForgery', // nama baru (Laravel 13)
        ], 'class_exists');

        $this->assertNotEmpty(array_intersect($kelas, $web), 'Middleware CSRF harus ada di grup web.');
    }

    public function test_header_keamanan_terpasang(): void
    {
        $this->get('/lacak')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_tamu_dialihkan_ke_login(): void
    {
        $this->get('/shipments')->assertRedirect(route('login'));
    }

    public function test_dashboard_menampilkan_statistik_total_untuk_admin(): void
    {
        $this->buatPaket($this->a, $this->b);
        $shipment = $this->buatPaket($this->b, $this->c);
        $shipment->update(['status_terakhir' => ShipmentStatus::Terkirim]);
        $admin = User::create([
            'nama' => 'Admin Uji', 'email' => 'dashboard-admin@uji.test',
            'password' => 'rahasia-uji-123', 'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-stat="shipments">2</p>', false)
            ->assertSee('data-stat="customers">2</p>', false)
            ->assertSee('data-stat="branches">3</p>', false)
            ->assertSee('data-status="DITERIMA">1</span>', false)
            ->assertSee('data-status="TERKIRIM">1</span>', false)
            ->assertSee('data-status="DIPROSES">0</span>', false);
    }

    public function test_dashboard_petugas_cabang_hanya_menghitung_data_cabangnya(): void
    {
        $this->buatPaket($this->a, $this->b);
        $this->buatPaket($this->b, $this->c);

        $this->actingAs($this->petugas($this->a))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-stat="shipments">1</p>', false)
            ->assertSee('data-stat="customers">1</p>', false)
            ->assertSee('data-stat="branches">1</p>', false)
            ->assertSee('data-status="DITERIMA">1</span>', false)
            ->assertSee('data-status="TERKIRIM">0</span>', false);
    }

    public function test_cabang_tidak_boleh_melihat_paket_cabang_lain(): void
    {
        $paketAB = $this->buatPaket($this->a, $this->b);

        $this->actingAs($this->petugas($this->c))->get(route('shipments.show', $paketAB))->assertForbidden();
        $this->actingAs($this->petugas($this->a))
            ->get(route('shipments.show', $paketAB))
            ->assertOk()
            ->assertSee('Biaya dasar')
            ->assertSee('Diskon member (10%)')
            ->assertSee('Asuransi (0,2%)');
        $this->actingAs($this->petugas($this->b))->get(route('shipments.show', $paketAB))->assertOk();
    }

    public function test_status_diperbarui_dan_riwayat_bertambah(): void
    {
        $p = $this->buatPaket($this->a, $this->b);

        $this->actingAs($this->petugas($this->a))
            ->post(route('shipments.status', $p), ['status' => 'DALAM_PERJALANAN', 'catatan' => 'Berangkat'])
            ->assertRedirect();

        $this->assertSame('DALAM_PERJALANAN', $p->fresh()->status_terakhir->value);
        $this->assertSame(2, $p->events()->count());
    }

    public function test_api_lacak_json(): void
    {
        $p = $this->buatPaket($this->a, $this->b);

        $this->getJson('/api/v1/lacak/'.$p->resi)->assertOk()->assertJsonPath('data.resi', $p->resi);
        $this->getJson('/api/v1/lacak/TIDAKADA123')->assertNotFound();
    }
}
