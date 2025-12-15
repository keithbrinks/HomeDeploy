<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    protected $signature = 'user:create {--name=} {--email=} {--password=}';

    protected $description = 'Create a new user';

    public function handle(): void
    {
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');

        if (! $name || ! $email || ! $password) {
            $this->error('Name, email, and password are required.');
            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('User already exists.');
            return;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info('User created successfully.');
    }
}
