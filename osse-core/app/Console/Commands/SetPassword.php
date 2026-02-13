<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set a users password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all(['id', 'username']);
        $this->table(['ID', 'Username'], $users->toArray());

        $username = $this->askWithCompletion('Enter a username', $users->pluck('username'));

        $userExists = $users->pluck('username')->map(fn ($u) => Str::lower($u))->contains(Str::lower($username));
        if (!$userExists) {
            $this->error('User not found. Please check spelling and try again.');
            return;
        }

        $password = $this->secret('Enter the new password (password will not be shown).');
        if (empty($password)) {
            $this->error('Password was not entered. Please user a valid password.');
            return;
        }

        $user = User::query()->firstWhere('username', $username);
        if (empty($user)) {
            $this->error('User not found. Please check spelling and try again.');
            return;
        }

        $user->update(['password' => $user->password]);

        $this->info('Password set!');
    }
}
