<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 8mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #000; }
    .head { border-bottom: 2px solid #000; padding-bottom: 4px; margin-bottom: 6px; }
    .head strong { font-size: 12pt; }
    .svc { float: right; border: 2px solid #000; padding: 1px 8px; font-weight: bold; font-size: 11pt; }
    .center { text-align: center; }
    .resi { font-size: 13pt; font-weight: bold; letter-spacing: 1px; margin: 2px 0 6px; }
    table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    td { vertical-align: top; padding: 3px 0; }
    .box { border: 1px solid #000; padding: 4px 6px; margin-top: 6px; }
    .small { font-size: 7.5pt; }
</style>
</head>
<body>
    <div class="head">
        <span class="svc">{{ strtoupper($shipment->service->nama) }}</span>
        <strong>PT Sinar Logistik Nusantara</strong><br>
        <span class="small">Cabang asal: {{ $shipment->originBranch->nama }}</span>
    </div>

    <div class="center">
        <img src="{{ $barcode }}" alt="Barcode" width="230" height="60">
        <div class="resi">{{ $shipment->resi }}</div>
    </div>

    <div class="box">
        <strong>Penerima</strong><br>
        {{ $shipment->penerima_nama }}<br>
        {{ $shipment->penerima_alamat }}<br>
        Tujuan: {{ $shipment->destBranch->nama }} ({{ $shipment->destBranch->kota }})
    </div>

    <table>
        <tr>
            <td style="width:60%">
                <strong>Pengirim</strong><br>
                {{ $shipment->customer->nama }}<br>
                {{ $shipment->customer->telepon }}<br><br>
                Berat tagih: <strong>{{ $shipment->berat_tagih }} kg</strong><br>
                Dibuat: {{ $shipment->created_at->format('d/m/Y H:i') }}
            </td>
            <td class="center">
                <img src="{{ $qr }}" alt="QR" width="100" height="100"><br>
                <span class="small">Pindai untuk melacak</span>
            </td>
        </tr>
    </table>
</body>
</html>
