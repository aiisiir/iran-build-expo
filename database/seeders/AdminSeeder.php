<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** The first admin comes from ADMIN_EMAIL / ADMIN_PASSWORD (never committed); otherwise a random password is printed once. */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@example.com');

        if (User::withTrashed()->where('email', $email)->exists()) {
            return;
        }

        $password = (string) env('ADMIN_PASSWORD', '');
        $generated = $password === '';
        $password = $generated ? Str::password(20, symbols: false) : $password;

        User::create(['name' => 'Administrator', 'email' => $email, 'password' => $password, 'role' => Role::Admin, 'position' => 'مدیر سایت']);

        $this->command?->warn($generated ? "Admin created: {$email} / {$password}  (shown once, change it now)" : "Admin created: {$email}");
    }
}
