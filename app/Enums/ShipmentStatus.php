<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case Diterima = 'DITERIMA';
    case Diproses = 'DIPROSES';
    case DalamPerjalanan = 'DALAM_PERJALANAN';
    case DiCabangTujuan = 'DI_CABANG_TUJUAN';
    case Diantar = 'DIANTAR';
    case Terkirim = 'TERKIRIM';
    case GagalKirim = 'GAGAL_KIRIM';

    public function label(): string
    {
        return match ($this) {
            self::Diterima => 'Paket diterima',
            self::Diproses => 'Sedang diproses',
            self::DalamPerjalanan => 'Dalam perjalanan',
            self::DiCabangTujuan => 'Tiba di cabang tujuan',
            self::Diantar => 'Sedang diantar kurir',
            self::Terkirim => 'Terkirim',
            self::GagalKirim => 'Gagal kirim',
        };
    }

    /** @return array<string,string> nilai => label */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $c) {
            $out[$c->value] = $c->label();
        }

        return $out;
    }
}
