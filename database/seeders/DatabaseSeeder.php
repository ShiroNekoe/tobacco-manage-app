<?php

namespace Database\Seeders;

use App\Imports\ProcessingReportImporter;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
                // 1. System Settings
        SystemSetting::set('company_name', 'PT. Tobacco Enterprise Indonesia');

        // 2. Seed 2 Roles (Admin & Karyawan)
        $admin = User::updateOrCreate(['email' => 'admin@tobacco.com'], [
            'name' => 'Administrator Utama',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $karyawan = User::updateOrCreate(['email' => 'karyawan@tobacco.com'], [
            'name' => 'Karyawan Lapangan',
            'role' => 'karyawan',
            'password' => Hash::make('password'),
        ]);

        // 3. Master Data: Customers
        $customer1 = Customer::updateOrCreate(['code' => 'CUST-FNG'], [
            'name' => 'PT. Falih Nur Gemilang',
            'code' => 'CUST-FNG',
            'contact_person' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Raya Industri No. 88, Surabaya',
        ]);

        $customer2 = Customer::updateOrCreate(['code' => 'CUST-LTA'], [
            'name' => 'CV. Lombok Tobacco Agritech',
            'code' => 'CUST-LTA',
            'contact_person' => 'Wayan Gusti',
            'phone' => '089876543210',
            'address' => 'Jl. Tanam Tembakau No. 12, Selong',
        ]);

        // 4. Master Data: Product Types
        $prod1 = ProductType::updateOrCreate(['code' => 'PAITON-P10T5'], [
            'code' => 'PAITON-P10T5',
            'name' => 'PAITON P10T5',
        ]);

        $prod2 = ProductType::updateOrCreate(['code' => 'LOMBOK-P9K5'], [
            'code' => 'LOMBOK-P9K5',
            'name' => 'LOMBOK P9K5',
        ]);

        // 5. Master Data: Origins
        $origin1 = Origin::updateOrCreate(['region_name' => 'PAITON'], ['region_name' => 'PAITON']);
        $origin2 = Origin::updateOrCreate(['region_name' => 'LOMBOK'], ['region_name' => 'LOMBOK']);
        $origin3 = Origin::updateOrCreate(['region_name' => 'JEMBER'], ['region_name' => 'JEMBER']);

        // 6. Master Data: Delivery Notes
        $dn1 = DeliveryNote::updateOrCreate(['dn_number' => 'DN-2026-0801'], [
            'dn_number' => 'DN-2026-0801',
            'customer_id' => $customer1->id,
            'delivery_date' => Carbon::now()->subDays(2),
            'status' => 'received',
        ]);

        // 7. Seed Sample Batch 1 (Locked Process Certificate Example)
        $batch1 = Batch::create([
            'batch_code' => 'BATCH-20260803-001',
            'customer_id' => $customer1->id,
            'delivery_note_id' => $dn1->id,
            'product_type_id' => $prod1->id,
            'origin_id' => $origin1->id,
            'pack_type' => 'Bale',
            'date_of_receipt' => Carbon::now()->subDays(2),
            // DN Headings
            'dn_total_pack' => 10,
            'dn_gross_weight' => 520.00,
            'dn_tare_weight' => 20.00,
            'dn_netto_weight' => 500.00,
            // MRL Headings
            'mrl_total_pack' => 10,
            'mrl_gross_weight' => 518.00,
            'mrl_tare_weight' => 20.00,
            'mrl_netto_weight' => 498.00,
            'discrepancy_dn_vs_mrl_kg' => 2.00, // 500 - 498
            // Separation Outputs
            'separation_product_kg' => 410.00,
            'separation_bits_stem_kg' => 45.00,
            'separation_dust_kg' => 20.00,
            'separation_waste_kg' => 23.00, // 498 - (410 + 45 + 20)
            // Percentages
            'yield_product_pct' => 82.33,
            'yield_bits_stem_pct' => 9.04,
            'yield_dust_pct' => 4.02,
            'yield_waste_pct' => 4.61,
            'status' => 'locked',
            'created_by_user_id' => $karyawan->id,
            'locked_at' => Carbon::now()->subDay(),
        ]);

        // Seed 10 Sack Weighing Items for Batch 1
        for ($i = 1; $i <= 10; $i++) {
            WeighingItem::create([
                'batch_id' => $batch1->id,
                'sack_number' => $i,
                'gross_kg' => $i === 10 ? 50.00 : 52.00,
                'tare_kg' => 2.00,
                'netto_kg' => $i === 10 ? 48.00 : 50.00,
                'remark' => $i === 10 ? 'Remnant' : 'Normal',
            ]);
        }

        // 8. Seed Sample Batch 2 (Active Draft for Karyawan Speed Entry testing)
        $batch2 = Batch::create([
            'batch_code' => 'BATCH-20260803-002',
            'customer_id' => $customer1->id,
            'delivery_note_id' => $dn1->id,
            'product_type_id' => $prod2->id,
            'origin_id' => $origin2->id,
            'pack_type' => 'Sack',
            'date_of_receipt' => Carbon::now(),
            'dn_total_pack' => 15,
            'dn_gross_weight' => 750.00,
            'dn_tare_weight' => 30.00,
            'dn_netto_weight' => 720.00,
            'mrl_total_pack' => 0,
            'mrl_gross_weight' => 0,
            'mrl_tare_weight' => 0,
            'mrl_netto_weight' => 0,
            'status' => 'draft',
            'created_by_user_id' => $karyawan->id,
        ]);
        $importer = new ProcessingReportImporter();
        $importer->import(true);
    }
}
