<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Failed attempts allowed per email + IP before a lockout.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Lockout length in seconds.
     */
    private const DECAY_SECONDS = 300;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'access_code' => [static::accessCodeRequired() ? 'required' : 'nullable', 'string', 'max:255'],
        ];
    }

    public static function accessCodeRequired(): bool
    {
        return filled(config('kliq.admin.access_code'));
    }

    /**
     * Sign in an administrator. Every failure gives the same message, so the
     * response never reveals which of the credentials was wrong.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $codeIsValid = $this->accessCodeIsValid();
        $user = User::query()->where('email', $this->string('email')->value())->first();
        $credentialsAreValid = Auth::getProvider()->validateCredentials(
            $user ?? new User(['password' => Str::random(40)]),
            ['password' => $this->string('password')->value()],
        );

        if (! $codeIsValid || ! $user || ! $credentialsAreValid || ! $user->isAdmin()) {
            RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Compare the access code with ADMIN_ACCESS_CODE, which may be stored as
     * a bcrypt/argon hash or as a plain value.
     */
    private function accessCodeIsValid(): bool
    {
        $expected = (string) config('kliq.admin.access_code');

        if ($expected === '') {
            return true;
        }

        $given = $this->string('access_code')->value();

        if (password_get_info($expected)['algo'] !== null) {
            return password_verify($given, $expected);
        }

        return hash_equals($expected, $given);
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
        ]);
    }

    private function throttleKey(): string
    {
        return 'admin-login|'.Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
