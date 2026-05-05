<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Invoice;
use App\Models\Asset;
use App\Models\Feature;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Demo User
        $user = User::updateOrCreate(
            ['email' => 'demo@qbill.com'],
            [
                'name' => 'Demo User (View Only)',
                'password' => Hash::make('demo1234'),
                'role' => 2, // 2 for Demo/View-only
                'phone' => '6285640431181',
            ]
        );

        // Assign all features to demo user
        $features = Feature::all();
        $user->features()->sync($features->pluck('id'));

        // 2. Create some Packages
        $packages = [
            ['name' => 'Hemat 5Mbps', 'price' => 100000, 'tipe' => 'STATIC'],
            ['name' => 'Reguler 10Mbps', 'price' => 150000, 'tipe' => 'STATIC'],
            ['name' => 'Premium 20Mbps', 'price' => 250000, 'tipe' => 'PPPOE'],
            ['name' => 'Extreme 50Mbps', 'price' => 450000, 'tipe' => 'PPPOE'],
        ];

        foreach ($packages as $pkg) {
            Package::updateOrCreate(
                ['name' => $pkg['name'], 'user_id' => $user->id],
                $pkg
            );
        }

        $allPackages = Package::where('user_id', $user->id)->get();

        // 3. Create some Assets
        $assets = [
            ['name' => 'OLT-PUSAT-01', 'category' => 'OLT', 'address' => 'Jl. Merdeka No. 1'],
            ['name' => 'ODP-KOTA-A1', 'category' => 'ODP', 'address' => 'Blok A1'],
            ['name' => 'ODP-KOTA-B2', 'category' => 'ODP', 'address' => 'Blok B2'],
        ];

        foreach ($assets as $ast) {
            Asset::updateOrCreate(
                ['name' => $ast['name'], 'user_id' => $user->id],
                $ast
            );
        }

        $allAssets = Asset::where('user_id', $user->id)->get();

        // 4. Create 200 Customers
        $faker = \Faker\Factory::create('id_ID');
        
        // Clear existing demo customers to avoid duplicates if re-run
        Customer::where('user_id', $user->id)->delete();

        for ($i = 1; $i <= 200; $i++) {
            $package = $allPackages->random();
            $status = $faker->randomElement(['active', 'active', 'active', 'suspended']);
            
            $customer = Customer::create([
                'user_id' => $user->id,
                'id_pelanggan' => date('ymd') . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => $faker->name,
                'phone' => '628' . $faker->numerify('##########'),
                'address' => $faker->address,
                'status' => $status,
                'package_id' => $package->id,
                'service_type' => strtolower($package->tipe),
                'due_date' => Carbon::now()->addDays(rand(-10, 20)),
                'asset_id' => $allAssets->random()->id,
                'username' => $package->tipe == 'PPPOE' ? $faker->userName : null,
                'password' => 'pass123',
                'ip_address' => $package->tipe == 'STATIC' ? '10.10.10.' . ($i % 254 + 1) : null,
            ]);

            // 5. Create Invoices for each customer
            // Current month invoice
            $invoiceStatus = $faker->randomElement(['paid', 'unpaid', 'unpaid']);
            Invoice::create([
                'id' => (string) Str::uuid(),
                'customer_id' => $customer->id,
                'package_id' => $package->id,
                'invoice_number' => 'INV/' . date('Ym') . '/' . $customer->id_pelanggan,
                'billing_period' => date('Y-m'),
                'amount' => $package->price,
                'unique_code' => 0,
                'total_amount' => $package->price,
                'status' => $invoiceStatus,
                'due_date' => $customer->due_date,
                'paid_at' => $invoiceStatus == 'paid' ? Carbon::now() : null,
            ]);

            // Past month invoice
            Invoice::create([
                'id' => (string) Str::uuid(),
                'customer_id' => $customer->id,
                'package_id' => $package->id,
                'invoice_number' => 'INV/' . Carbon::now()->subMonth()->format('Ym') . '/' . $customer->id_pelanggan,
                'billing_period' => Carbon::now()->subMonth()->format('Y-m'),
                'amount' => $package->price,
                'unique_code' => 0,
                'total_amount' => $package->price,
                'status' => 'paid',
                'due_date' => $customer->due_date->copy()->subMonth(),
                'paid_at' => Carbon::now()->subMonth(),
            ]);
        }
    }
}
