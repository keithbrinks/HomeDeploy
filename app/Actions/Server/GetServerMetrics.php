<?php

namespace App\Actions\Server;

class GetServerMetrics
{
    public function execute(): array
    {
        return [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'uptime' => $this->getUptime(),
            'load_average' => $this->getLoadAverage(),
        ];
    }

    private function getCpuUsage(): float
    {
        $load = sys_getloadavg();
        $cpuCount = $this->getCpuCount();
        
        return round(($load[0] / $cpuCount) * 100, 2);
    }

    private function getCpuCount(): int
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            $output = shell_exec('sysctl -n hw.ncpu');
        } else {
            $output = shell_exec('nproc');
        }

        return (int) trim($output ?: '1');
    }

    private function getMemoryUsage(): array
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            return $this->getMacMemoryUsage();
        }

        return $this->getLinuxMemoryUsage();
    }

    private function getMacMemoryUsage(): array
    {
        $pageSize = (int) shell_exec('pagesize');
        $vmStat = shell_exec('vm_stat');
        
        preg_match('/Pages free:\s+(\d+)\./', $vmStat, $free);
        preg_match('/Pages active:\s+(\d+)\./', $vmStat, $active);
        preg_match('/Pages inactive:\s+(\d+)\./', $vmStat, $inactive);
        preg_match('/Pages wired down:\s+(\d+)\./', $vmStat, $wired);
        
        $freePages = (int) ($free[1] ?? 0);
        $activePages = (int) ($active[1] ?? 0);
        $inactivePages = (int) ($inactive[1] ?? 0);
        $wiredPages = (int) ($wired[1] ?? 0);
        
        $totalBytes = (int) shell_exec('sysctl -n hw.memsize');
        $usedBytes = ($activePages + $wiredPages) * $pageSize;
        $freeBytes = $totalBytes - $usedBytes;
        
        return [
            'total' => $this->formatBytes($totalBytes),
            'used' => $this->formatBytes($usedBytes),
            'free' => $this->formatBytes($freeBytes),
            'percent' => round(($usedBytes / $totalBytes) * 100, 2),
        ];
    }

    private function getLinuxMemoryUsage(): array
    {
        $meminfo = file_get_contents('/proc/meminfo');
        
        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
        
        $totalKb = (int) ($total[1] ?? 0);
        $availableKb = (int) ($available[1] ?? 0);
        $usedKb = $totalKb - $availableKb;
        
        return [
            'total' => $this->formatBytes($totalKb * 1024),
            'used' => $this->formatBytes($usedKb * 1024),
            'free' => $this->formatBytes($availableKb * 1024),
            'percent' => round(($usedKb / $totalKb) * 100, 2),
        ];
    }

    private function getDiskUsage(): array
    {
        $path = base_path();
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percent' => round(($used / $total) * 100, 2),
        ];
    }

    private function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            $bootTime = shell_exec('sysctl -n kern.boottime | awk \'{print $4}\' | sed \'s/,//\'');
            $uptime = time() - (int) trim($bootTime);
        } else {
            $uptime = (int) shell_exec('cat /proc/uptime | cut -d " " -f1');
        }

        $days = floor($uptime / 86400);
        $hours = floor(($uptime % 86400) / 3600);
        $minutes = floor(($uptime % 3600) / 60);

        if ($days > 0) {
            return "{$days}d {$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    private function getLoadAverage(): array
    {
        $load = sys_getloadavg();

        return [
            '1min' => round($load[0], 2),
            '5min' => round($load[1], 2),
            '15min' => round($load[2], 2),
        ];
    }

    private function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
