<?php

namespace App\Services\Label;

use App\Models\Shipment;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Picqer\Barcode\BarcodeGeneratorSVG;

/**
 * Label resi PDF (100 x 150 mm) berisi barcode Code128 + QR menuju halaman lacak.
 * Barcode & QR dibuat sebagai SVG dan disisipkan sebagai data URI (tanpa file sementara).
 */
class LabelService
{
    public function render(Shipment $shipment): DomPdf
    {
        $shipment->loadMissing(['service:id,nama', 'customer:id,nama,telepon', 'originBranch:id,nama,kota', 'destBranch:id,nama,kota']);

        return Pdf::loadView('pdf.label', [
            'shipment' => $shipment,
            'barcode' => $this->dataUri($this->barcodeSvg($shipment->resi)),
            'qr' => $this->dataUri($this->qrSvg(route('lacak', ['resi' => $shipment->resi]))),
        ])->setPaper([0, 0, 283.46, 425.20]); // 100 x 150 mm dalam poin
    }

    public function barcodeSvg(string $resi): string
    {
        return (new BarcodeGeneratorSVG())->getBarcode($resi, BarcodeGeneratorSVG::TYPE_CODE_128, 2, 60);
    }

    public function qrSvg(string $data): string
    {
        $renderer = new ImageRenderer(new RendererStyle(180, 0), new SvgImageBackEnd());

        return (new Writer($renderer))->writeString($data);
    }

    private function dataUri(string $svg): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
