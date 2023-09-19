<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateUserAndToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-user-and-token {name} {email} {token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new user and create them an API token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::factory()->create([
            'name' => $this->argument('name'),
            'email' => $this->argument('email')
        ]);

        $token = $user->createToken($this->argument('token'));

        $this->info("User: {$user->name} created with Token: {$token->plainTextToken}");
    }
}
