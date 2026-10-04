<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Mengisi katalog PRIM dengan data yang tampil pada desain Figma.
 *
 * Nama layanan, kelompok varian paket, harga, tag periode, dan pita diskon
 * disalin dari frame "Layanan" agar tampilan hasil implementasi dapat
 * dibandingkan langsung dengan desain. Angka harga dalam rupiah.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();

        foreach ($this->catalog() as $definition) {
            $provider = Provider::updateOrCreate(
                ['slug' => $definition['provider_slug']],
                [
                    'name' => $definition['provider'],
                    'website' => $definition['website'],
                    'description' => 'Penyedia resmi layanan '.$definition['name'].'.',
                ]
            );

            $service = Service::updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'provider_id' => $provider->id,
                    'category_id' => $categories[$definition['category']]->id,
                    'name' => $definition['name'],
                    'tagline' => $definition['tagline'],
                    'description' => $definition['tagline'].' Tersedia beberapa pilihan varian paket dan periode tagihan yang dapat disesuaikan dengan kebutuhanmu.',
                    'website' => $definition['website'],
                    'is_active' => true,
                ]
            );

            // Bersihkan paket lama agar hasil seeder selalu sama dengan desain.
            $service->plans()->delete();

            foreach ($definition['variants'] as $index => $variant) {
                Plan::create([
                    'service_id' => $service->id,
                    'name' => $variant['group'],
                    'variant_group' => $variant['group'],
                    'price' => $variant['price'],
                    'compare_at_price' => $variant['compare_at'] ?? null,
                    'discount_percent' => $variant['discount'] ?? 0,
                    'duration_days' => $variant['days'],
                    'periods_label' => $variant['periods'],
                    'max_devices' => $variant['devices'],
                    'description' => $variant['group'].' — '.$definition['name'],
                    'features' => $variant['features'],
                    'is_active' => true,
                    'is_preorder' => $variant['preorder'] ?? false,
                    'stock' => $variant['stock'] ?? 0,
                ]);
            }
        }
    }

    /**
     * Kategori layanan sesuai label pada desain Figma.
     *
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $definitions = [
            'streaming' => [
                'name' => 'Streaming',
                'icon' => 'film',
                'description' => 'Layanan tontonan film, serial, dan siaran langsung.',
            ],
            'musik' => [
                'name' => 'Musik',
                'icon' => 'musical-note',
                'description' => 'Layanan streaming musik dan audio.',
            ],
            'ai' => [
                'name' => 'AI',
                'icon' => 'sparkles',
                'description' => 'Asisten dan perkakas kecerdasan buatan.',
            ],
            'produktivitas' => [
                'name' => 'Produktivitas',
                'icon' => 'briefcase',
                'description' => 'Perangkat kerja digital untuk desain, dokumen, dan kolaborasi.',
            ],
            'penyimpanan' => [
                'name' => 'Penyimpanan',
                'icon' => 'cloud',
                'description' => 'Layanan penyimpanan awan dan berkas.',
            ],
            'edukasi' => [
                'name' => 'Edukasi',
                'icon' => 'academic-cap',
                'description' => 'Platform belajar dan latihan bahasa.',
            ],
        ];

        $categories = [];

        foreach ($definitions as $key => $definition) {
            $categories[$key] = Category::updateOrCreate(['slug' => $key], $definition);
        }

        return $categories;
    }

    /**
     * Daftar layanan beserta varian paketnya, mengikuti frame "Layanan".
     *
     * @return list<array<string, mixed>>
     */
    private function catalog(): array
    {
        $streaming = 'streaming';
        $musik = 'musik';
        $ai = 'ai';
        $produktivitas = 'produktivitas';
        $penyimpanan = 'penyimpanan';
        $edukasi = 'edukasi';

        $features = [
            'Akun legal bergaransi',
            'Bisa klaim garansi selama masa aktif',
            'Panduan pemakaian dari admin',
            'Dukungan melalui WhatsApp',
        ];

        return [
            [
                'slug' => 'netflix',
                'name' => 'Netflix',
                'provider' => 'Netflix',
                'provider_slug' => 'netflix',
                'website' => 'https://www.netflix.com',
                'category' => $streaming,
                'tagline' => 'Nonton film dan serial tanpa batas',
                'variants' => [
                    ['group' => '1 Perangkat', 'price' => 47500, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 1, 'features' => $features],
                    ['group' => '2 Perangkat', 'price' => 95000, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 2, 'features' => $features],
                    ['group' => '5 Perangkat', 'price' => 195000, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 5, 'features' => $features],
                ],
            ],
            [
                'slug' => 'spotify-premium',
                'name' => 'Spotify Premium',
                'provider' => 'Spotify',
                'provider_slug' => 'spotify',
                'website' => 'https://www.spotify.com',
                'category' => $musik,
                'tagline' => 'Musik tanpa iklan dan bisa diunduh',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 43300, 'days' => 30, 'periods' => '1, 2, 3, 6, 12 bln', 'devices' => 1, 'features' => $features],
                    ['group' => 'Promo 3 & 6 Bulan', 'price' => 43334, 'days' => 90, 'periods' => '3, 6 bln', 'devices' => 1, 'features' => $features],
                    ['group' => 'Promo 12 Bulan', 'price' => 43417, 'days' => 365, 'periods' => '12 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'spotify-platinum',
                'name' => 'Spotify Platinum',
                'provider' => 'Spotify',
                'provider_slug' => 'spotify-platinum',
                'website' => 'https://www.spotify.com',
                'category' => $musik,
                'tagline' => 'Kualitas audio tertinggi dan akun host',
                'variants' => [
                    ['group' => 'Reguler', 'price' => 73000, 'days' => 30, 'periods' => '1, 2, 3, 6, 12 bln', 'devices' => 1, 'features' => $features],
                    ['group' => 'User Host', 'price' => 39967, 'days' => 90, 'periods' => '3 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'capcut-pro',
                'name' => 'CapCut Pro',
                'provider' => 'CapCut',
                'provider_slug' => 'capcut',
                'website' => 'https://www.capcut.com',
                'category' => $produktivitas,
                'tagline' => 'Sunting video tanpa batas dan tanpa tanda air',
                'variants' => [
                    ['group' => 'Bulanan Desktop', 'price' => 54500, 'days' => 30, 'periods' => '2 bln', 'devices' => 1, 'features' => $features],
                    ['group' => 'Tahunan Desktop', 'price' => 399000, 'days' => 365, 'periods' => '1 bln', 'devices' => 1, 'features' => $features],
                    ['group' => 'Bulanan Mobile', 'price' => 72000, 'days' => 30, 'periods' => '2 bln', 'devices' => 1, 'features' => $features],
                    ['group' => 'Tahunan Mobile', 'price' => 567000, 'days' => 365, 'periods' => '1 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'disney-plus-hotstar',
                'name' => 'Disney Plus Hotstar',
                'provider' => 'Disney+ Hotstar',
                'provider_slug' => 'disney-plus-hotstar',
                'website' => 'https://www.hotstar.com',
                'category' => $streaming,
                'tagline' => 'Film Disney, Marvel, dan serial Asia',
                'variants' => [
                    ['group' => '1 Perangkat', 'price' => 36000, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 1, 'features' => $features, 'discount' => 10, 'compare_at' => 40000],
                    ['group' => '12 Bulan', 'price' => 24416, 'days' => 365, 'periods' => '12 bln', 'devices' => 1, 'features' => $features, 'preorder' => true],
                ],
            ],
            [
                'slug' => 'chatgpt-plus',
                'name' => 'ChatGPT Plus',
                'provider' => 'OpenAI',
                'provider_slug' => 'openai',
                'website' => 'https://chat.openai.com',
                'category' => $ai,
                'tagline' => 'Akses model AI terbaru tanpa antre',
                'variants' => [
                    ['group' => '1 Perangkat', 'price' => 79900, 'days' => 30, 'periods' => '1, 3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'bundling-hemat-6k',
                'name' => 'Bundling Hemat 6K',
                'provider' => 'PRIM Bundle',
                'provider_slug' => 'prim-bundle-6k',
                'website' => 'https://prim.example.id',
                'category' => $streaming,
                'tagline' => 'Paket gabungan Spotify dan Netflix',
                'variants' => [
                    ['group' => 'Promo BH6K', 'price' => 118500, 'days' => 30, 'periods' => '1, 3 bln', 'devices' => 2, 'features' => $features, 'discount' => 1, 'compare_at' => 119700],
                ],
            ],
            [
                'slug' => 'bundling-hemat-7k',
                'name' => 'Bundling Hemat 7K',
                'provider' => 'PRIM Bundle',
                'provider_slug' => 'prim-bundle-7k',
                'website' => 'https://prim.example.id',
                'category' => $streaming,
                'tagline' => 'Paket gabungan Disney+ Hotstar dan Netflix',
                'variants' => [
                    ['group' => 'Promo BH7K', 'price' => 81500, 'days' => 30, 'periods' => '1 bln', 'devices' => 2, 'features' => $features, 'discount' => 8, 'compare_at' => 88600],
                ],
            ],
            [
                'slug' => 'apple-one',
                'name' => 'Apple One',
                'provider' => 'Apple',
                'provider_slug' => 'apple',
                'website' => 'https://www.apple.com/apple-one/',
                'category' => $streaming,
                'tagline' => 'Musik, tontonan, dan penyimpanan dalam satu paket',
                'variants' => [
                    ['group' => 'Reguler Bulanan', 'price' => 46000, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 1, 'features' => $features],
                    ['group' => 'Host Bulanan', 'price' => 39800, 'days' => 30, 'periods' => '3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'apple-one-premier',
                'name' => 'Apple One Premier',
                'provider' => 'Apple',
                'provider_slug' => 'apple-premier',
                'website' => 'https://www.apple.com/apple-one/',
                'category' => $streaming,
                'tagline' => 'Paket terlengkap Apple untuk keluarga',
                'variants' => [
                    ['group' => 'Bulanan Reguler', 'price' => 69500, 'days' => 30, 'periods' => '1, 3, 6 bln', 'devices' => 1, 'features' => $features],
                    ['group' => 'Bulanan Host', 'price' => 63800, 'days' => 30, 'periods' => '1, 3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'viu-premium',
                'name' => 'Viu Premium',
                'provider' => 'Viu',
                'provider_slug' => 'viu',
                'website' => 'https://www.viu.com',
                'category' => $streaming,
                'tagline' => 'Serial Asia dan drama Korea tanpa iklan',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 27500, 'days' => 30, 'periods' => '1, 2, 3 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'viu-premium-plus',
                'name' => 'Viu Premium Plus',
                'provider' => 'Viu',
                'provider_slug' => 'viu-plus',
                'website' => 'https://www.viu.com',
                'category' => $streaming,
                'tagline' => 'Viu dengan unduhan dan kualitas terbaik',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 48000, 'days' => 30, 'periods' => '1, 2, 3 bln', 'devices' => 2, 'features' => $features],
                ],
            ],
            [
                'slug' => 'microsoft-365',
                'name' => 'Microsoft 365',
                'provider' => 'Microsoft',
                'provider_slug' => 'microsoft',
                'website' => 'https://www.microsoft.com/microsoft-365',
                'category' => $produktivitas,
                'tagline' => 'Word, Excel, dan PowerPoint versi lengkap',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 41000, 'days' => 30, 'periods' => '1, 3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'google-gemini',
                'name' => 'Google Gemini',
                'provider' => 'Google',
                'provider_slug' => 'google-gemini',
                'website' => 'https://gemini.google.com',
                'category' => $ai,
                'tagline' => 'Asisten AI Google untuk kerja dan belajar',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 76500, 'days' => 30, 'periods' => '1 bln', 'devices' => 1, 'features' => $features, 'discount' => 1, 'compare_at' => 77273],
                    ['group' => 'Bulanan', 'price' => 75817, 'days' => 90, 'periods' => '3, 6 bln', 'devices' => 1, 'features' => $features, 'discount' => 1, 'compare_at' => 76583],
                    ['group' => 'Tahunan', 'price' => 75317, 'days' => 365, 'periods' => '12 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'duolingo',
                'name' => 'Duolingo',
                'provider' => 'Duolingo',
                'provider_slug' => 'duolingo',
                'website' => 'https://www.duolingo.com',
                'category' => $edukasi,
                'tagline' => 'Belajar bahasa tanpa iklan dan tanpa batas hati',
                'variants' => [
                    ['group' => 'Tahunan', 'price' => 41000, 'days' => 365, 'periods' => '12 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'vision-plus-ultimate',
                'name' => 'Vision Plus Ultimate',
                'provider' => 'Vision+',
                'provider_slug' => 'vision-plus',
                'website' => 'https://www.visionplus.id',
                'category' => $streaming,
                'tagline' => 'Tontonan lokal, olahraga, dan film internasional',
                'variants' => [
                    ['group' => '1 Perangkat', 'price' => 25500, 'days' => 30, 'periods' => '1, 3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'wetv',
                'name' => 'WeTV',
                'provider' => 'WeTV',
                'provider_slug' => 'wetv',
                'website' => 'https://wetv.vip',
                'category' => $streaming,
                'tagline' => 'Drama China dan Asia dengan subtitle Indonesia',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 25000, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'prime-video',
                'name' => 'Prime Video',
                'provider' => 'Amazon',
                'provider_slug' => 'amazon',
                'website' => 'https://www.primevideo.com',
                'category' => $streaming,
                'tagline' => 'Film dan serial original Amazon',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 32500, 'days' => 30, 'periods' => '1, 2, 3 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'google-one',
                'name' => 'Google One',
                'provider' => 'Google',
                'provider_slug' => 'google-one',
                'website' => 'https://one.google.com',
                'category' => $penyimpanan,
                'tagline' => 'Penyimpanan awan untuk Foto dan Drive',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 41000, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 1, 'features' => $features],
                    ['group' => 'Tahunan', 'price' => 40000, 'days' => 365, 'periods' => '12 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'canva-pro',
                'name' => 'Canva Pro',
                'provider' => 'Canva',
                'provider_slug' => 'canva',
                'website' => 'https://www.canva.com',
                'category' => $produktivitas,
                'tagline' => 'Desain grafis dengan seluruh aset premium',
                'variants' => [
                    ['group' => 'Bulanan Host', 'price' => 73000, 'days' => 30, 'periods' => '1, 3, 6 bln', 'devices' => 1, 'features' => $features],
                    ['group' => 'Bulanan Reguler', 'price' => 79500, 'days' => 30, 'periods' => '1, 3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'max',
                'name' => 'Max',
                'provider' => 'HBO Max',
                'provider_slug' => 'hbo-max',
                'website' => 'https://www.max.com',
                'category' => $streaming,
                'tagline' => 'Serial HBO, Warner Bros, dan DC',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 39000, 'days' => 30, 'periods' => '1, 3, 6 bln', 'devices' => 1, 'features' => $features],
                    ['group' => 'Tahunan', 'price' => 39000, 'days' => 365, 'periods' => '12 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'iqiyi',
                'name' => 'iQIYI',
                'provider' => 'iQIYI',
                'provider_slug' => 'iqiyi',
                'website' => 'https://www.iq.com',
                'category' => $streaming,
                'tagline' => 'Drama dan animasi Asia berkualitas',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 19500, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'apple-music',
                'name' => 'Apple Music',
                'provider' => 'Apple',
                'provider_slug' => 'apple-music',
                'website' => 'https://www.apple.com/apple-music/',
                'category' => $musik,
                'tagline' => 'Musik lossless dan spatial audio',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 25000, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'zoom-pro',
                'name' => 'Zoom Pro',
                'provider' => 'Zoom',
                'provider_slug' => 'zoom',
                'website' => 'https://zoom.us',
                'category' => $produktivitas,
                'tagline' => 'Rapat daring tanpa batas waktu',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 68000, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'catchplay',
                'name' => 'CATCHPLAY',
                'provider' => 'CATCHPLAY',
                'provider_slug' => 'catchplay',
                'website' => 'https://www.catchplay.com',
                'category' => $streaming,
                'tagline' => 'Film Hollywood dan Asia terbaru',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 25000, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'vidio-premier',
                'name' => 'Vidio Premier',
                'provider' => 'Vidio',
                'provider_slug' => 'vidio',
                'website' => 'https://www.vidio.com',
                'category' => $streaming,
                'tagline' => 'Liga sepak bola dan tontonan lokal',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 35000, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
            [
                'slug' => 'youku',
                'name' => 'Youku',
                'provider' => 'Youku',
                'provider_slug' => 'youku',
                'website' => 'https://www.youku.tv',
                'category' => $streaming,
                'tagline' => 'Drama dan variety show Tiongkok',
                'variants' => [
                    ['group' => 'Bulanan', 'price' => 25000, 'days' => 30, 'periods' => '1, 2, 3, 6 bln', 'devices' => 1, 'features' => $features],
                ],
            ],
        ];
    }
}
