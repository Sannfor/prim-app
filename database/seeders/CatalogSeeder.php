<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Mengisi katalog PRIM dengan data contoh yang realistis.
 *
 * Catatan: seluruh penyedia layanan di sini bersifat FIKTIF dan dibuat untuk
 * keperluan demonstrasi akademik, bukan layanan digital yang sebenarnya.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();
        $providers = $this->seedProviders();

        foreach ($this->catalog() as $service) {
            $category = $categories[$service['category']];
            $provider = $providers[$service['provider']];

            $record = Service::updateOrCreate(
                ['slug' => $service['slug']],
                [
                    'provider_id' => $provider->id,
                    'category_id' => $category->id,
                    'name' => $service['name'],
                    'tagline' => $service['tagline'],
                    'description' => $service['description'],
                    'website' => $provider->website,
                    'is_active' => true,
                ]
            );

            foreach ($service['plans'] as $plan) {
                Plan::updateOrCreate(
                    [
                        'service_id' => $record->id,
                        'name' => $plan['name'],
                    ],
                    [
                        'price' => $plan['price'],
                        'duration_days' => $plan['duration_days'],
                        'max_devices' => $plan['max_devices'],
                        'description' => $plan['description'],
                        'features' => $plan['features'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    /**
     * Kategori layanan sesuai ruang lingkup pada dokumen SRS.
     *
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $definitions = [
            'hiburan' => [
                'name' => 'Hiburan & Streaming',
                'icon' => 'film',
                'description' => 'Layanan tontonan dan audio digital: film, serial, dan musik tanpa batas.',
            ],
            'produktivitas' => [
                'name' => 'Produktivitas & Kreativitas',
                'icon' => 'briefcase',
                'description' => 'Perangkat kerja digital untuk kolaborasi, desain, dan pengelolaan dokumen.',
            ],
            'edukasi' => [
                'name' => 'Edukasi & Pembelajaran',
                'icon' => 'book-open',
                'description' => 'Platform kursus daring dan sumber belajar bersertifikat.',
            ],
        ];

        $categories = [];

        foreach ($definitions as $key => $definition) {
            $categories[$key] = Category::updateOrCreate(
                ['slug' => $key],
                $definition
            );
        }

        return $categories;
    }

    /**
     * Penyedia layanan fiktif untuk keperluan demonstrasi.
     *
     * @return array<string, Provider>
     */
    private function seedProviders(): array
    {
        $definitions = [
            'nusantara' => [
                'name' => 'Nusantara Stream',
                'website' => 'https://nusantarastream.example.id',
                'description' => 'Penyedia layanan streaming film dan serial dengan konten lokal maupun internasional.',
            ],
            'sonata' => [
                'name' => 'Sonata Music',
                'website' => 'https://sonatamusic.example.id',
                'description' => 'Penyedia layanan streaming musik dan podcast dengan katalog lagu yang luas.',
            ],
            'cendekia' => [
                'name' => 'Cendekia Learn',
                'website' => 'https://cendekialearn.example.id',
                'description' => 'Penyedia platform pembelajaran daring dan kursus bersertifikat.',
            ],
        ];

        $providers = [];

        foreach ($definitions as $key => $definition) {
            $providers[$key] = Provider::updateOrCreate(
                ['slug' => $key],
                $definition
            );
        }

        return $providers;
    }

    /**
     * Daftar layanan beserta paketnya.
     *
     * @return list<array<string, mixed>>
     */
    private function catalog(): array
    {
        return [
            [
                'slug' => 'nusantara-film-premium',
                'name' => 'Nusantara Film Premium',
                'provider' => 'nusantara',
                'category' => 'hiburan',
                'tagline' => 'Nonton film dan serial tanpa batas',
                'description' => 'Akses ribuan film, serial, dan dokumenter dari dalam dan luar negeri. Tersedia kualitas hingga 4K dengan pilihan subtitle bahasa Indonesia.',
                'plans' => [
                    [
                        'name' => 'Basic 1 Bulan',
                        'price' => 39000,
                        'duration_days' => 30,
                        'max_devices' => 1,
                        'description' => 'Satu perangkat, kualitas hingga 720p.',
                        'features' => ['Kualitas hingga 720p', '1 perangkat bersamaan', 'Subtitle bahasa Indonesia'],
                    ],
                    [
                        'name' => 'Standard 1 Bulan',
                        'price' => 59000,
                        'duration_days' => 30,
                        'max_devices' => 2,
                        'description' => 'Dua perangkat, kualitas hingga 1080p.',
                        'features' => ['Kualitas hingga 1080p', '2 perangkat bersamaan', 'Unduh offline', 'Tanpa iklan'],
                    ],
                    [
                        'name' => 'Premium 6 Bulan',
                        'price' => 299000,
                        'duration_days' => 180,
                        'max_devices' => 4,
                        'description' => 'Empat perangkat, kualitas 4K, hemat 6 bulan.',
                        'features' => ['Kualitas hingga 4K', '4 perangkat bersamaan', 'Unduh offline', 'Tanpa iklan', 'Bonus konten eksklusif'],
                    ],
                ],
            ],
            [
                'slug' => 'sonata-music-unlimited',
                'name' => 'Sonata Music Unlimited',
                'provider' => 'sonata',
                'category' => 'hiburan',
                'tagline' => 'Musik dan podcast tanpa iklan',
                'description' => 'Dengarkan jutaan lagu dan podcast tanpa iklan, dengan mode offline dan kualitas audio hingga lossless.',
                'plans' => [
                    [
                        'name' => 'Individu 1 Bulan',
                        'price' => 29000,
                        'duration_days' => 30,
                        'max_devices' => 1,
                        'description' => 'Satu akun pribadi, kualitas tinggi.',
                        'features' => ['Tanpa iklan', 'Unduh offline', 'Kualitas audio tinggi'],
                    ],
                    [
                        'name' => 'Duo 3 Bulan',
                        'price' => 79000,
                        'duration_days' => 90,
                        'max_devices' => 2,
                        'description' => 'Dua akun, cocok untuk pasangan.',
                        'features' => ['Tanpa iklan', '2 akun terpisah', 'Unduh offline', 'Kualitas lossless'],
                    ],
                    [
                        'name' => 'Keluarga 12 Bulan',
                        'price' => 249000,
                        'duration_days' => 365,
                        'max_devices' => 6,
                        'description' => 'Enam akun untuk satu keluarga, hemat setahun.',
                        'features' => ['Tanpa iklan', '6 akun terpisah', 'Kontrol orang tua', 'Kualitas lossless'],
                    ],
                ],
            ],
            [
                'slug' => 'cendekia-kelas-online',
                'name' => 'Cendekia Kelas Online',
                'provider' => 'cendekia',
                'category' => 'edukasi',
                'tagline' => 'Kursus daring bersertifikat',
                'description' => 'Ikuti kelas pemrograman, desain, dan bisnis dari praktisi industri. Setiap kelas dilengkapi studi kasus dan sertifikat penyelesaian.',
                'plans' => [
                    [
                        'name' => 'Reguler 1 Bulan',
                        'price' => 99000,
                        'duration_days' => 30,
                        'max_devices' => 1,
                        'description' => 'Akses satu kelas pilihan.',
                        'features' => ['1 kelas pilihan', 'Sertifikat penyelesaian', 'Forum diskusi'],
                    ],
                    [
                        'name' => 'Pro 3 Bulan',
                        'price' => 249000,
                        'duration_days' => 90,
                        'max_devices' => 2,
                        'description' => 'Akses seluruh kelas selama tiga bulan.',
                        'features' => ['Seluruh kelas', 'Sertifikat penyelesaian', 'Forum diskusi', 'Tugas diperiksa mentor'],
                    ],
                    [
                        'name' => 'Karier 12 Bulan',
                        'price' => 799000,
                        'duration_days' => 365,
                        'max_devices' => 3,
                        'description' => 'Semua kelas plus pendampingan karier.',
                        'features' => ['Seluruh kelas', 'Sertifikat penyelesaian', 'Pendampingan karier', 'Sesi konsultasi mentor', 'Portofolio proyek'],
                    ],
                ],
            ],
            [
                'slug' => 'nusantara-dokumenter',
                'name' => 'Nusantara Dokumenter',
                'provider' => 'nusantara',
                'category' => 'edukasi',
                'tagline' => 'Dokumenter sains dan sejarah',
                'description' => 'Koleksi dokumenter bertema sains, sejarah, dan budaya Nusantara yang dikurasi untuk pelajar dan pengajar.',
                'plans' => [
                    [
                        'name' => 'Sekolah 3 Bulan',
                        'price' => 149000,
                        'duration_days' => 90,
                        'max_devices' => 5,
                        'description' => 'Paket untuk satu kelas.',
                        'features' => ['5 perangkat bersamaan', 'Materi ajar pendukung', 'Kualitas hingga 1080p'],
                    ],
                    [
                        'name' => 'Institusi 12 Bulan',
                        'price' => 499000,
                        'duration_days' => 365,
                        'max_devices' => 20,
                        'description' => 'Paket untuk satu sekolah.',
                        'features' => ['20 perangkat bersamaan', 'Materi ajar pendukung', 'Laporan penggunaan', 'Dukungan prioritas'],
                    ],
                ],
            ],
            [
                'slug' => 'sonata-podcast-plus',
                'name' => 'Sonata Podcast Plus',
                'provider' => 'sonata',
                'category' => 'produktivitas',
                'tagline' => 'Podcast bisnis dan pengembangan diri',
                'description' => 'Koleksi podcast eksklusif seputar bisnis, karier, dan produktivitas, lengkap dengan transkrip dan catatan ringkas.',
                'plans' => [
                    [
                        'name' => 'Bulanan',
                        'price' => 19000,
                        'duration_days' => 30,
                        'max_devices' => 1,
                        'description' => 'Akses seluruh episode eksklusif.',
                        'features' => ['Episode eksklusif', 'Transkrip lengkap', 'Tanpa iklan'],
                    ],
                    [
                        'name' => 'Tahunan',
                        'price' => 179000,
                        'duration_days' => 365,
                        'max_devices' => 2,
                        'description' => 'Hemat lebih dari 20 persen.',
                        'features' => ['Episode eksklusif', 'Transkrip lengkap', 'Tanpa iklan', 'Catatan ringkas AI', 'Akses komunitas'],
                    ],
                ],
            ],
            [
                'slug' => 'cendekia-kelas-pemrograman',
                'name' => 'Cendekia Pemrograman Web',
                'provider' => 'cendekia',
                'category' => 'edukasi',
                'tagline' => 'Belajar membuat aplikasi web dari nol',
                'description' => 'Jalur belajar terstruktur untuk menjadi pengembang web: HTML, CSS, JavaScript, basis data, hingga deployment aplikasi.',
                'plans' => [
                    [
                        'name' => 'Dasar 2 Bulan',
                        'price' => 149000,
                        'duration_days' => 60,
                        'max_devices' => 1,
                        'description' => 'Materi dasar hingga JavaScript.',
                        'features' => ['HTML dan CSS', 'Dasar JavaScript', 'Latihan mingguan', 'Sertifikat penyelesaian'],
                    ],
                    [
                        'name' => 'Lengkap 6 Bulan',
                        'price' => 399000,
                        'duration_days' => 180,
                        'max_devices' => 2,
                        'description' => 'Jalur lengkap hingga deployment.',
                        'features' => ['Seluruh materi', 'Proyek akhir', 'Review kode mentor', 'Sertifikat penyelesaian', 'Persiapan portofolio'],
                    ],
                ],
            ],
        ];
    }
}
