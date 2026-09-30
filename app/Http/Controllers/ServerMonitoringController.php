<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ServerMonitoringController extends Controller
{
    public function __invoke(Request $request): View
    {
        $memoryLimit = $this->parseIniBytes(ini_get('memory_limit'));
        $phpMemoryUsed = memory_get_usage(true);
        $diskTotal = @disk_total_space(base_path());
        $diskFree = @disk_free_space(base_path());
        $diskAvailable = is_float($diskTotal) && is_float($diskFree);
        $systemMemory = $this->systemMemory();
        $loadAverage = function_exists('sys_getloadavg') ? sys_getloadavg() : false;

        return view('monitoring.server', [
            'sampledAt' => now(),
            'phpMemory' => [
                'used' => $phpMemoryUsed,
                'peak' => memory_get_peak_usage(true),
                'limit' => $memoryLimit,
                'limitLabel' => ini_get('memory_limit') === '-1'
                    ? 'Tidak dibatasi'
                    : ($memoryLimit === null ? 'Tidak diketahui' : null),
                'percent' => $memoryLimit ? min(100, round($phpMemoryUsed / $memoryLimit * 100, 1)) : null,
            ],
            'systemMemory' => $systemMemory,
            'disk' => [
                'total' => $diskAvailable ? $diskTotal : null,
                'free' => $diskAvailable ? $diskFree : null,
                'used' => $diskAvailable ? max(0, $diskTotal - $diskFree) : null,
                'percent' => $diskAvailable && $diskTotal > 0
                    ? round(($diskTotal - $diskFree) / $diskTotal * 100, 1)
                    : null,
            ],
            'loadAverage' => is_array($loadAverage) ? $loadAverage : null,
        ]);
    }

    private function systemMemory(): ?array
    {
        $meminfo = @file_get_contents('/proc/meminfo');
        if ($meminfo === false
            || ! preg_match('/^MemTotal:\s+(\d+)\s+kB$/m', $meminfo, $totalMatch)
        ) {
            return null;
        }

        preg_match('/^MemAvailable:\s+(\d+)\s+kB$/m', $meminfo, $availableMatch);
        $available = isset($availableMatch[1])
            ? (int) $availableMatch[1]
            : $this->fallbackAvailableMemory($meminfo);
        $total = (int) $totalMatch[1];

        return [
            'total' => $total * 1024,
            'available' => $available * 1024,
            'used' => max(0, $total - $available) * 1024,
            'percent' => $total > 0 ? round(($total - $available) / $total * 100, 1) : null,
        ];
    }

    private function fallbackAvailableMemory(string $meminfo): int
    {
        preg_match_all('/^(MemFree|Buffers|Cached):\s+(\d+)\s+kB$/m', $meminfo, $matches, PREG_SET_ORDER);

        return array_sum(array_map(fn (array $match): int => (int) $match[2], $matches));
    }

    private function parseIniBytes(string|false $value): ?int
    {
        if (! is_string($value) || ! preg_match('/^(\d+)\s*([KMG])?$/i', trim($value), $matches)) {
            return null;
        }

        $power = match (strtoupper($matches[2] ?? '')) {
            'G' => 3,
            'M' => 2,
            'K' => 1,
            default => 0,
        };

        return (int) $matches[1] * (1024 ** $power);
    }
}