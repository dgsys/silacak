<?php

namespace App\Services\Ongkir;

use InvalidArgumentException;

/**
 * Perhitungan ongkos kirim SiLacak.
 *
 * Semua uang & berat memakai bilangan bulat (tanpa float) agar tidak ada selisih pembulatan.
 * Tarif per kg dan berat minimum dibaca dari tabel services, sehingga layanan baru
 * (mis. Same Day Rp25.000/kg) cukup ditambah sebagai baris data tanpa mengubah kode ini.
 */
final class OngkirService
{
    public const DIVISOR_VOLUMETRIK = 6000;   // P x L x T / 6.000 (cm -> kg)
    public const DISKON_MEMBER_PERSEN = 10;   // diskon 10% dari biaya kirim
    public const ASURANSI_PERMIL = 2;         // 0,2% = 2 per mil
    public const BATAS_ASURANSI = 1_000_000;  // asuransi bila nilai barang > Rp1.000.000

    /** Berat volumetrik, dibulatkan ke atas per kg. */
    public function beratVolumetrikKg(int $panjang, int $lebar, int $tinggi): int
    {
        $this->pastikanTidakNegatif($panjang, $lebar, $tinggi);

        return intdiv($panjang * $lebar * $tinggi + self::DIVISOR_VOLUMETRIK - 1, self::DIVISOR_VOLUMETRIK);
    }

    /**
     * Berat aktual dibulatkan KE ATAS per kg (ceil).
     * Perbaikan bug lama: 1,3 kg dulu ditagih 1 kg (floor), kini 2 kg.
     * Input dibulatkan ke 0,01 kg dulu (sesuai kolom decimal(8,2)) agar bebas galat float.
     */
    public function beratAktualKg(string|int|float $aktual): int
    {
        if ($aktual < 0) {
            throw new InvalidArgumentException('Berat tidak boleh negatif.');
        }
        $centi = (int) round(((float) $aktual) * 100);

        return intdiv($centi + 99, 100);
    }

    /**
     * Berat tagih = nilai terbesar antara berat aktual dan volumetrik (dibulatkan ke atas),
     * tidak kurang dari berat minimum layanan (mis. Kargo min. 10 kg).
     * ceil(max(a, b)) == max(ceil(a), ceil(b)), jadi keduanya boleh dibulatkan lebih dulu.
     */
    public function beratTagih(string|int|float $aktual, int $panjang, int $lebar, int $tinggi, int $minKg = 1): int
    {
        return max(
            $this->beratAktualKg($aktual),
            $this->beratVolumetrikKg($panjang, $lebar, $tinggi),
            $minKg,
            1
        );
    }

    public function biayaDasar(int $beratTagih, int $tarifPerKg): int
    {
        return $beratTagih * $tarifPerKg;
    }

    /** Diskon member 10% dari biaya kirim (pembulatan setengah ke atas). */
    public function diskonMember(int $biayaDasar, bool $member): int
    {
        return $member ? intdiv($biayaDasar * self::DISKON_MEMBER_PERSEN + 50, 100) : 0;
    }

    /** Asuransi 0,2% dari nilai barang, hanya bila nilai barang > Rp1.000.000. */
    public function asuransi(int $nilaiBarang): int
    {
        if ($nilaiBarang <= self::BATAS_ASURANSI) {
            return 0;
        }

        return intdiv($nilaiBarang * self::ASURANSI_PERMIL + 500, 1000);
    }

    /**
     * @return array{berat_aktual_kg:int,berat_volumetrik_kg:int,berat_tagih:int,tarif_per_kg:int,biaya_dasar:int,diskon:int,asuransi:int,total:int}
     */
    public function hitung(
        int $tarifPerKg,
        int $minKg,
        string|int|float $beratAktual,
        int $panjang,
        int $lebar,
        int $tinggi,
        int $nilaiBarang,
        bool $member,
    ): array {
        $this->pastikanTidakNegatif($tarifPerKg, $minKg, $nilaiBarang);

        $aktualKg = $this->beratAktualKg($beratAktual);
        $volKg = $this->beratVolumetrikKg($panjang, $lebar, $tinggi);
        $tagih = $this->beratTagih($beratAktual, $panjang, $lebar, $tinggi, $minKg);
        $dasar = $this->biayaDasar($tagih, $tarifPerKg);
        $diskon = $this->diskonMember($dasar, $member);
        $asuransi = $this->asuransi($nilaiBarang);

        return [
            'berat_aktual_kg' => $aktualKg,
            'berat_volumetrik_kg' => $volKg,
            'berat_tagih' => $tagih,
            'tarif_per_kg' => $tarifPerKg,
            'biaya_dasar' => $dasar,
            'diskon' => $diskon,
            'asuransi' => $asuransi,
            'total' => $dasar - $diskon + $asuransi,
        ];
    }

    private function pastikanTidakNegatif(int ...$nilai): void
    {
        foreach ($nilai as $n) {
            if ($n < 0) {
                throw new InvalidArgumentException('Nilai tidak boleh negatif.');
            }
        }
    }
}
