<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Isi basis data dengan katalog PRIM dan data demonstrasi.
     */
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
