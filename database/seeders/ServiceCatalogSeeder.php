<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServicePackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Full Body Massage',
                'description' => 'Rasakan harmoni menyeluruh di seluruh tubuh Anda dengan pijatan lembut. Nikmati relaksasi total, terapi berendam kaki dan memperbaiki postur tubuh, serta membantu mengurangi stres.',
                'packages' => [
                    ['duration_minutes' => 60, 'price' => 100000],
                    ['duration_minutes' => 90, 'price' => 140000],
                    ['duration_minutes' => 120, 'price' => 180000],
                ],
            ],
            [
                'name' => 'Hot Stone Massage',
                'description' => 'Nikmati sensasi hangat yang menenangkan tubuh Anda dengan Hot Stone Massage. Batu panas yang dipadukan dengan teknik pijat terapeutik membantu melepaskan ketegangan dan memberi relaksasi mendalam.',
                'packages' => [
                    ['duration_minutes' => 60, 'price' => 130000],
                    ['duration_minutes' => 90, 'price' => 180000],
                    ['duration_minutes' => 120, 'price' => 230000],
                ],
            ],
            [
                'name' => 'Thai Massage',
                'description' => 'Rasakan pijatan tradisional Thai Massage untuk kelenturan tubuh dan energi. Gerakan peregangan serta tekanan lembut membantu menyegarkan tubuh dan pikiran.',
                'packages' => [
                    ['duration_minutes' => 60, 'price' => 120000],
                    ['duration_minutes' => 90, 'price' => 160000],
                    ['duration_minutes' => 120, 'price' => 210000],
                ],
            ],
            [
                'name' => 'Deep Tissue Massage',
                'description' => 'Teknik pijat bertekanan untuk meredakan ketegangan otot mendalam dan membantu tubuh pulih dengan nyaman.',
                'packages' => [
                    ['duration_minutes' => 60, 'price' => 110000],
                    ['duration_minutes' => 90, 'price' => 150000],
                    ['duration_minutes' => 120, 'price' => 200000],
                ],
            ],
            [
                'name' => 'Swedish Massage',
                'description' => 'Rileks dengan pijatan klasik yang lembut, meningkatkan sirkulasi darah dan memberi rasa nyaman menyeluruh.',
                'packages' => [
                    ['duration_minutes' => 60, 'price' => 110000],
                    ['duration_minutes' => 90, 'price' => 150000],
                    ['duration_minutes' => 120, 'price' => 190000],
                ],
            ],
            [
                'name' => 'Traditional Massage',
                'description' => 'Perawatan tradisional dengan minyak pilihan dan sentuhan hangat untuk menyegarkan dan memulihkan stamina tubuh.',
                'packages' => [
                    ['duration_minutes' => 60, 'price' => 90000],
                    ['duration_minutes' => 90, 'price' => 130000],
                    ['duration_minutes' => 120, 'price' => 170000],
                ],
            ],
        ];

        foreach ($services as $serviceData) {
            $service = Service::firstOrCreate(
                ['slug' => Str::slug($serviceData['name'])],
                [
                    'name' => $serviceData['name'],
                    'description' => $serviceData['description'],
                    'is_active' => true,
                ]
            );

            foreach ($serviceData['packages'] as $pkg) {
                ServicePackage::firstOrCreate(
                    [
                        'service_id' => $service->id,
                        'duration_minutes' => $pkg['duration_minutes'],
                    ],
                    [
                        'price' => $pkg['price'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
