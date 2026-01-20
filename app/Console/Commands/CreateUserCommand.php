<?php

namespace App\Console\Commands;

use Exception;
use App\Models\User;
use App\Enums\User\Role;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Console\Commands\Traits\HasValidator;
use App\Repositories\Contracts\UserRepositoryInterface;
use function Laravel\Prompts\{
    info,
    text,
    error,
    select,
    confirm,
    password
};

class CreateUserCommand extends Command
{
    use HasValidator;

    protected $signature = 'user:create';

    protected $description = 'Create a system user (Admin / Provider / Customer).';

    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $data = [];

        $data['name'] = text(
            label: 'Name:',
            required: true,
            validate: fn ($value) =>
                $this->validate($value, 'name', ['required', 'string', 'max:255'])
        );

        $data['email'] = text(
            label: 'Email:',
            required: true,
            validate: fn ($value) =>
                $this->validate($value, 'email', [
                    'required',
                    'email',
                    'unique:users,email'
                ])
        );

        $data['role'] = select(
            label: 'Select user role',
            options: [Role::class]
        );

        $data['phone'] = text(
            label: 'Phone (optional):',
            required: false
        );

        $passwordRules = Password::min(8);
        $hint = "Minimum of 8 characters.";

        if (app()->environment('production')) {
            $passwordRules = $passwordRules
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised();

            $hint .= "\nMust contain upper/lowercase, number, and symbol.";
        }

        $rawPassword = password(
            label: 'Password (leave blank to auto-generate)',
            validate: fn ($value) =>
                blank($value)
                    ? null
                    : $this->validate($value, 'password', [$passwordRules]),
            hint: $hint
        );

        if (blank($rawPassword)) {
            $rawPassword = Str::password(12);
        }

        $data['password'] = Hash::make($rawPassword);

        if (confirm('Auto verify email?', default: true)) {
            $data['email_verified_at'] = now();
        }

        info('==============================');
        info('Review user details:');
        info("Name     : {$data['name']}");
        info("Email    : {$data['email']}");
        info("Role     : {$data['role']}");
        info("Phone    : {$data['phone']}");
        info("Mobile   : {$data['mobile']}");
        info("Address  : {$data['address']}");
        info("Password : {$rawPassword}");
        info('==============================');

        if (!confirm('Create this user?', default: false)) {
            info('User creation cancelled.');
            return self::SUCCESS;
        }

        try {
            $user = new User();
            $user->fill($data);

            $this->userRepository->save($user);

            info("User successfully created! (ID: {$user->id})");
            return self::SUCCESS;

        } catch (Exception $e) {
            report($e);
            error('Failed to create user: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
