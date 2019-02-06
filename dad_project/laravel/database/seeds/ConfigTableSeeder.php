<?php

use Illuminate\Database\Seeder;

class ConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $platform_email = 'memorygame.dad.g26@gmail.com';
        $driver = 'smtp';
        $host = 'smtp.gmail.com';
        $port = 465;
        $password = 'pnbbttunjykoowxx';
        $encryption = 'ssl';
        $createdAt = Carbon\Carbon::now()->subMonths(2);

        $configInfo = [
            'platform_email' => $platform_email,
            'platform_email_properties' => '{"driver": "'.$driver.'", "host": "'.$host.'", "port": "'.$port.'", "password": "'.$password.'", "encryption": "'.$encryption.'" }',
            'img_base_path' => 'img/', 
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];

        DB::table('config')->insert($configInfo);
    }

}
