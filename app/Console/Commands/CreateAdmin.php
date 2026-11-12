<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('kliq:admin {--name= : Display name} {--email= : Login email}')]
#[Description('Create an administrator, or grant admin access and reset the password of an existing user')]
class CreateAdmin extends Command
{
    /**
     * Execute the console command.
     *
     * The password is always typed at a hidden prompt, never passed as an
     * argument, so it does not end up in shell history.
     */
    public function handle(): int
    {
        $email = $this->option('email') ?: text(
            label: 'Admin email',
            required: true,
            validate: fn (string $value): ?string => filter_var($value, FILTER_VALIDATE_EMAIL) ? null : 'Enter a valid email address.',
        );

        $user = User::query()->where('email', $email)->first();

        $name = $this->option('name') ?: ($user->name ?? text(label: 'Name', default: 'KLIQ Admin', required: true));

        $password = password(
            label: 'Password',
            required: true,
            hint: 'At least 12 characters, with letters and numbers.',
            validate: fn (string $value): ?string => Validator::make(
                ['password' => $value],
                ['password' => [Password::min(12)->letters()->numbers()]],
            )->errors()->first('password') ?: null,
        );

        $confirmation = password(label: 'Confirm password', required: true);

        if (! hash_equals($password, $confirmation)) {
            $this->components->error('The passwords do not match.');

            return self::FAILURE;
        }

        $user ??= new User(['email' => $email]);
        $user->fill(['name' => $name, 'password' => $password]);
        $user->is_admin = true;
        $user->email_verified_at ??= now();
        $user->save();

        $this->components->info("Administrator {$email} is ready. Sign in at ".route('admin.login'));

        return self::SUCCESS;
    }
}
