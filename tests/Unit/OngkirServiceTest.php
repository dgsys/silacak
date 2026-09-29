<?php

namespace Tests\Unit;

use App\Services\Ongkir\OngkirService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OngkirServiceTest extends TestCase
{
    private OngkirService $svc;

    protected function setUp(): void
    {
        $this->svc = new OngkirService();
    }

    public static function beratAktual(): array
    {
        return [
            '1,3 kg jadi 2 (bug floor lama)' => ['1.30', 2],
            '1,00 kg tetap 1' => ['1.00', 1],
            '1,01 kg jadi 2' => ['1.01', 2],
            '0,01 kg jadi 1' => ['0.01', 1],
        ];
    }

    #[DataProvider('beratAktual')]
    public function test_berat_aktual_dibulatkan_ke_atas(string $input, int $expected): void
    {
        $this->assertSame($expected, $this->svc->beratAktualKg($input));
    }

    public function test_berat_volumetrik(): void
    {
        $this->assertSame(10, $this->svc->beratVolumetrikKg(50, 40, 30));
        $this->assertSame(1, $this->svc->beratVolumetrikKg(30, 20, 10));
        $this->assertSame(2, $this->svc->beratVolumetrikKg(31, 20, 10));
    }

    public function test_berat_tagih_memakai_nilai_terbesar(): void
    {
        $this->assertSame(10, $this->svc->beratTagih('3.00', 50, 40, 30));
        $this->assertSame(10, $this->svc->beratTagih('4.00', 0, 0, 0, 10)); // Kargo min. 10 kg
    }

    public function test_reguler_1_3_kg(): void
    {
        $h = $this->svc->hitung(9000, 1, '1.30', 20, 15, 10, 500000, false);
        $this->assertSame(18000, $h['total']);
    }

    public function test_express_member_dengan_asuransi(): void
    {
        $h = $this->svc->hitung(15000, 1, '3.00', 50, 40, 30, 2000000, true);
        $this->assertSame(150000, $h['biaya_dasar']);
        $this->assertSame(15000, $h['diskon']);
        $this->assertSame(4000, $h['asuransi']);
        $this->assertSame(139000, $h['total']);
    }

    public function test_kargo_min_10_kg(): void
    {
        $this->assertSame(60000, $this->svc->hitung(6000, 10, '4.00', 0, 0, 0, 0, false)['total']);
    }

    public function test_batas_asuransi(): void
    {
        $this->assertSame(0, $this->svc->asuransi(1_000_000));
        $this->assertSame(2000, $this->svc->asuransi(1_000_001));
    }

    public function test_same_day_hanya_butuh_tarif_baru(): void
    {
        $this->assertSame(50000, $this->svc->hitung(25000, 1, '2.00', 0, 0, 0, 0, false)['total']);
    }

    public function test_nilai_negatif_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->svc->beratAktualKg(-1);
    }
}
