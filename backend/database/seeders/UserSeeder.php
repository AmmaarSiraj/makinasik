<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data persis dari SQL Dump Anda
        // Password hash ($2b$10$...) adalah format Bcrypt yang kompatibel dengan Laravel
        $users = [
            [
                'username' => 'admin_user',
                'email' => 'admin@example.com',
                'password' => '$2b$10$/tPjJleLSYysp/eERYMH/.vvp/lcZRHWNsVUT6bZcNNf0n1YPWvbu',
                'role' => 'admin',
                'created_at' => '2025-11-16 08:32:44'
            ],
            [
                'username' => 'user_biasa_UPDATED',
                'email' => 'user_updated@example.com',
                'password' => '$2b$10$CG6KHz2M1DFddRImZaH9K.l2cgTr/yfz4BR9gYmnD1XaaLhJLjbWW',
                'role' => 'user',
                'created_at' => '2025-11-16 08:34:31'
            ],
            [
                'username' => 'userr',
                'email' => 'user@gmail.com',
                'password' => '$2b$10$7ok4KAO6WsGbLRUiMgfnG.sXynPFvE7Osj9JM4WlajnDCX9/P3u62',
                'role' => 'user',
                'created_at' => '2025-11-17 02:21:12'
            ],
            [
                'username' => 'pambudi',
                'email' => 'pambudi@gmail.com',
                'password' => '$2b$10$xQ/6v2vjXarFU/ZHruDeJe7B.3Iku9/439lx59wau5l2D2xmXglQu',
                'role' => 'user',
                'created_at' => '2025-11-18 06:25:08'
            ],
            [
                'username' => 'user2',
                'email' => 'user2@gmail.com',
                'password' => '$2b$10$uvdh5kM0yj3D1o5Nt0C4d.CAjqxOznmROxyP3yGHw4Ce4D.VQD2eC',
                'role' => 'user',
                'created_at' => '2025-11-18 15:50:12'
            ],
            [
                'username' => 'ammaar',
                'email' => 'user12@gmail.com',
                'password' => '$2b$10$6cpdEXyweGSwh8HD2Lq.pOWzmVSAE6vrr3phoJ3RQXpjMcwt.vQcq',
                'role' => 'user',
                'created_at' => '2025-11-23 10:38:00'
            ],
            [
                'username' => 'siraj',
                'email' => 'admin2@gmail.com',
                'password' => '$2b$10$XoluQu6wsi5WZVmcA2xL.uttaXD35lXePZ5Ol7wsdUqO30opSRu8K',
                'role' => 'user',
                'created_at' => '2025-11-23 10:38:00'
            ],
            [
                'username' => 'al',
                'email' => 'al@yahoo.com',
                'password' => '$2b$10$QjXIxq9t/infUkNeAM53Mu1omoaZhBu55HMEPdikCmlvD/hU/4QRC',
                'role' => 'user',
                'created_at' => '2025-11-23 10:38:00'
            ],
        ];

        DB::table('user')->insert($users);
    }
}