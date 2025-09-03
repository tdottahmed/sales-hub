<?php

namespace Database\Seeders;

use App\Models\ApplicationSetup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $applicationInfo = [
            ['type' => 'app_name', 'value' => 'Laravel'],
            ['type' => 'app_version', 'value' => '1.0.0'],
            ['type' => 'app_description', 'value' => 'A Laravel application'],
            ['type' => 'app_url', 'value' => 'http://localhost'],
            ['type' => 'app_logo', 'value' => 'path/to/logo.png'],
            ['type' => 'app_favicon', 'value' => 'path/to/favicon.ico'],
            ['type' => 'login_banner', 'value' => 'path/to/login_banner.jpg'],
        ];

        foreach ($applicationInfo as $info) {
            ApplicationSetup::create([
                'type' => $info['type'],
                'value' => $info['value']
            ]);
        }
    }
}
