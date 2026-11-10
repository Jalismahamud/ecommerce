<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the first administrator account';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->ask('Administrator name');
        $email = strtolower((string) $this->ask('Administrator email'));

        $validator = Validator::make(
            ['name' => $name, 'email' => $email],
            ['name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'max:255', 'unique:users,email']]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $password = $this->secret('Password (minimum 12 characters)');

        if (strlen((string) $password) < 12 || $password !== $this->secret('Confirm password')) {
            $this->error('Passwords must match and contain at least 12 characters.');

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $user->assignRole('admin');

        $this->info("Administrator account created for {$email}.");

        return self::SUCCESS;
    }
}
