<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class OltManager extends Component
{
    public $onuList = [];
    public $isLoading = false;
    public $error = null;
    public $lastUpdated = null;
    public $searchQuery = '';
    public $filterStatus = 'all'; // all, online, offline

    // Config - bisa disesuaikan dari AppSetting ke depannya
    public $oltIp = '192.168.10.80';
    public $community = 'public';

    public function mount()
    {
        $this->checkFeature();
        $this->isLoading = true; // tampilkan skeleton saat pertama kali
    }

    protected function checkFeature(): void
    {
        abort_unless(auth()->user()->hasFeature('olt'), 403);
    }

    public function fetchData(): void
    {
        $this->isLoading = true;
        $this->error = null;

        try {
            $raw = shell_exec(
                "snmpwalk -v2c -c {$this->community} {$this->oltIp} 1.3.6.1.4.1.25355 2>&1"
            );

            if (empty($raw)) {
                $this->error = 'Tidak ada respon dari OLT. Pastikan SNMP aktif dan IP terjangkau.';
                $this->onuList = [];
            } else {
                $this->onuList = $this->parse($raw);
                $this->lastUpdated = now()->format('H:i:s');
            }
        } catch (\Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            $this->onuList = [];
        }

        $this->isLoading = false;
    }

    /**
     * Parse raw snmpwalk output into structured ONU list.
     * Format OID: 1.3.6.1.4.1.25355.X.Y.Z = value
     */
    protected function parse(?string $raw): array
    {
        if (!$raw) return [];

        $lines = explode("\n", trim($raw));
        $data = [];
        $onuMap = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Parse: OID = TYPE: VALUE
            if (!preg_match('/^([^\s]+)\s+=\s+(.+)$/', $line, $m)) continue;

            $oid   = $m[1];
            $value = $m[2];

            // Strip type prefix (STRING: "val", INTEGER: 1, etc.)
            $value = preg_replace('/^[A-Za-z0-9]+:\s*/', '', $value);
            $value = trim($value, '"');

            // Extract sub-OID index (last number(s) = ONU index)
            // OID ends with ...X.index or ...X.index.sub
            $parts = explode('.', $oid);
            $index = end($parts); // last segment as ONU index

            // Classify known OIDs (25355 sub-tree, common GPON OIDs)
            $baseOid = implode('.', array_slice($parts, 0, -1));

            // Map sub-OIDs to field names (approximate; adjust to actual MIB)
            $fieldMap = [
                '1.3.6.1.4.1.25355.2.1.1.1' => 'index',
                '1.3.6.1.4.1.25355.2.1.1.2' => 'serial_number',
                '1.3.6.1.4.1.25355.2.1.1.3' => 'description',
                '1.3.6.1.4.1.25355.2.1.1.4' => 'status',        // 1=online, 2=offline
                '1.3.6.1.4.1.25355.2.1.1.5' => 'distance',
                '1.3.6.1.4.1.25355.2.1.1.6' => 'rx_power',
                '1.3.6.1.4.1.25355.2.1.1.7' => 'tx_power',
                '1.3.6.1.4.1.25355.2.1.1.8' => 'olt_rx_power',
                '1.3.6.1.4.1.25355.2.1.1.9' => 'port',
            ];

            if (isset($fieldMap[$baseOid])) {
                $field = $fieldMap[$baseOid];
                if (!isset($onuMap[$index])) {
                    $onuMap[$index] = ['id' => $index];
                }
                $onuMap[$index][$field] = $value;
            } else {
                // Store raw for unknown OIDs
                if (!isset($onuMap[$index])) {
                    $onuMap[$index] = ['id' => $index];
                }
                $onuMap[$index]['raw'][] = ['oid' => $oid, 'value' => $value];
            }
        }

        // If we couldn't map structured fields, return raw line data instead
        if (empty($onuMap)) {
            // Return raw output as single entry for debugging
            return [['id' => 'raw', 'raw_output' => $raw]];
        }

        return array_values($onuMap);
    }

    public function getFilteredOnuListProperty(): array
    {
        $list = $this->onuList;

        if ($this->filterStatus !== 'all') {
            $statusValue = $this->filterStatus === 'online' ? '1' : '2';
            $list = array_filter($list, fn($onu) => ($onu['status'] ?? '') === $statusValue);
        }

        if ($this->searchQuery) {
            $q = strtolower($this->searchQuery);
            $list = array_filter($list, function ($onu) use ($q) {
                return str_contains(strtolower($onu['serial_number'] ?? ''), $q)
                    || str_contains(strtolower($onu['description'] ?? ''), $q)
                    || str_contains(strtolower($onu['id'] ?? ''), $q);
            });
        }

        return array_values($list);
    }

    public function render()
    {
        return view('livewire.olt-manager', [
            'filteredOnus' => $this->filteredOnuList,
            'totalOnline'  => count(array_filter($this->onuList, fn($o) => ($o['status'] ?? '') === '1')),
            'totalOffline' => count(array_filter($this->onuList, fn($o) => ($o['status'] ?? '') === '2')),
        ])->layout('layouts.app', ['header' => 'OLT Monitor']);
    }
}
