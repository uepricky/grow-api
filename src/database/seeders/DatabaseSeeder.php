<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            WorkingTimeUnitsSeeder::class,
            DigitsSeeder::class,
            RoundingMethodsSeeder::class,
            ConsumptionTaxTypeSeeder::class,
            SysMenuCategorySeeder::class,
            SysPaymentMethodCategorySeeder::class,
            PermissionV2PermissionSeeder::class,
        ]);
    }
}
