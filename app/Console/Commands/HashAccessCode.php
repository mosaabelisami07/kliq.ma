<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;

#[Signature('kliq:hash-code')]
#[Description('Hash an admin access code for the ADMIN_ACCESS_CODE .env value')]
class HashAccessCode extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $code = password(label: 'Access code', required: true, hint: 'At least 8 characters.', validate: fn (string $value): ?string => mb_strlen($value) < 8 ? 'Use at least 8 characters.' : null);

        $this->newLine();
        $this->line('Add this line to your .env file (keep the single quotes):');
        $this->newLine();
        $this->line("ADMIN_ACCESS_CODE='".Hash::make($code)."'");
        $this->newLine();

        return self::SUCCESS;
    }
}
