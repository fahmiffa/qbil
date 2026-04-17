<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Package;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExcelImportService
{
    private $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    public function import(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File tidak ditemukan: $filePath");
        }

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $stats = [
            'success' => 0,
            'errors' => 0,
            'messages' => []
        ];

        // Start from row 1 (index 1) to skip headers
        foreach ($rows as $idx => $row) {
            if ($idx === 0) continue;
            if (empty(array_filter($row))) continue;

            try {
                // Mapping based on user request
                $idPelanggan = $row[3] ?? null;
                $name = $row[5] ?? null;
                $phone = $row[10] ?? null;
                $address = $row[9] ?? null;
                $keterangan = $row[16] ?? null; // Keterangan/Speed
                $packageName = $row[15] ?? 'Basic';
                $bandwidth = $row[16] ?? '1M/1M';
                $ipFull = $row[28] ?? null;
                $connType = $row[31] ?? 'Static';
                $statusStr = $row[37] ?? 'On';
                $coordinates = $row[38] ?? null;
                $tanggalTagihan = $row[25] ?? null;

                if (!$name || !$ipFull) {
                    continue;
                }

                // Clean IP (remove /32 etc)
                $ipAddress = explode('/', $ipFull)[0];

                // Parse due_date (index 25: Tanggal Tagihan)
                $dueDate = null;
                if ($tanggalTagihan) {
                    try {
                        $dueDate = Carbon::parse($tanggalTagihan);
                    } catch (\Exception $e) {
                        $dueDate = null;
                    }
                }

                // Map status
                $status = (strtolower($statusStr) === 'on' || strtolower($statusStr) === 'active') ? 'active' : 'suspended';

                // Map coordinates
                $lat = null;
                $lng = null;
                if ($coordinates && str_contains($coordinates, ',')) {
                    $parts = explode(',', $coordinates);
                    $lat = trim($parts[0]);
                    $lng = trim($parts[1]);
                }

                // Handle Package
                $package = Package::where('name', $packageName)->first();
                if (!$package) {
                    $package = Package::where('speed_upload', $this->getUpload($bandwidth))
                        ->where('speed_download', $this->getDownload($bandwidth))
                        ->first();
                    
                    if (!$package) {
                        $package = Package::create([
                            'user_id' => auth()->id(),
                            'name' => $packageName,
                            'tipe' => 'QUEUE',
                            'speed_upload' => $this->getUpload($bandwidth),
                            'speed_download' => $this->getDownload($bandwidth),
                            'price' => $this->cleanPrice($row[18] ?? 0),
                            'mikrotik_profile' => 'default'
                        ]);
                    }
                }

                DB::transaction(function () use ($idPelanggan, $name, $phone, $address, $keterangan, $status, $package, $ipAddress, $lat, $lng, $dueDate) {
                    $customer = Customer::updateOrCreate(
                        ['name' => $name, 'user_id' => auth()->id()],
                        [
                            'id_pelanggan' => $idPelanggan,
                            'phone' => $phone,
                            'address' => $address,
                            'keterangan' => $keterangan,
                            'status' => $status,
                            'due_date' => $dueDate,
                            'package_id' => $package->id,
                            'service_type' => 'static', // Changed from simple_queue to static
                            'ip_address' => $ipAddress,
                            'latitude' => $lat,
                            'longitude' => $lng,
                        ]
                    );

                    // Sync to Mikrotik (Focus on Simple Queues)
                    $rateLimit = $package->speed_upload . '/' . $package->speed_download;
                    $this->mikrotikService->updateSimpleQueue($name, $name, $ipAddress, $rateLimit);

                    if ($status === 'suspended') {
                        $this->mikrotikService->disableSimpleQueue($name);
                    } else {
                        $this->mikrotikService->enableSimpleQueue($name);
                    }
                });

                $stats['success']++;
            } catch (\Exception $e) {
                $stats['errors']++;
                $stats['messages'][] = "Row $idx ($name): " . $e->getMessage();
            }
        }

        return $stats;
    }

    private function getUpload($bandwidth)
    {
        if (str_contains($bandwidth, '/')) {
            return explode('/', $bandwidth)[0];
        }
        return '1M';
    }

    private function getDownload($bandwidth)
    {
        if (str_contains($bandwidth, '/')) {
            return explode('/', $bandwidth)[1];
        }
        return '1M';
    }

    private function cleanPrice($price)
    {
        if (is_numeric($price)) return $price;
        return (int) preg_replace('/[^0-9]/', '', $price);
    }
}
