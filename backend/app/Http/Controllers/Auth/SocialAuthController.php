<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    private function frontendUrl(): string
    {
        return rtrim((string) (config('services.frontend_url') ?: 'https://kalapak-team.space'), '/');
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        $frontendUrl = $this->frontendUrl();

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            report($e);
            return redirect($frontendUrl . '/auth/login?error=google_auth_failed');
        }

        try {
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
                if (!$user->avatar && $googleUser->getAvatar()) {
                    $user->update(['avatar' => $googleUser->getAvatar()]);
                }
                if (!$user->username) {
                    $base = Str::before((string) $user->email, '@') ?: Str::slug((string) $user->name, '_') ?: 'user';
                    $user->update(['username' => User::generateUniqueUsername($base)]);
                }
            } else {
                $memberRole = Role::firstOrCreate(
                    ['name' => 'member'],
                    [
                        'display_name' => 'Member',
                        'description' => 'Team member with profile access',
                    ]
                );
                $email = (string) $googleUser->getEmail();
                $usernameBase = Str::before($email, '@') ?: Str::slug((string) $googleUser->getName(), '_') ?: 'user';
                $user = User::create([
                    'name' => $googleUser->getName() ?: 'Google User',
                    'username' => User::generateUniqueUsername($usernameBase),
                    'email' => $email,
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Str::random(64),
                    'role_id' => $memberRole->id,
                    'email_verified_at' => now(),
                ]);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            return redirect($frontendUrl . '/auth/google/callback?token=' . $token);
        } catch (\Throwable $e) {
            report($e);
            return redirect($frontendUrl . '/auth/login?error=google_auth_failed');
        }
    }

    public function redirectToGithub(): RedirectResponse
    {
        return Socialite::driver('github')->stateless()->redirect();
    }

    public function handleGithubCallback(): RedirectResponse
    {
        $frontendUrl = $this->frontendUrl();

        try {
            $githubUser = Socialite::driver('github')->stateless()->user();
        } catch (\Exception $e) {
            report($e);
            return redirect($frontendUrl . '/auth/login?error=github_auth_failed');
        }

        try {
            $user = User::where('github_id', $githubUser->getId())
                ->orWhere('email', $githubUser->getEmail())
                ->first();

            if ($user) {
                if (!$user->github_id) {
                    $user->update(['github_id' => $githubUser->getId()]);
                }
                if (!$user->avatar && $githubUser->getAvatar()) {
                    $user->update(['avatar' => $githubUser->getAvatar()]);
                }
                if (!$user->username) {
                    $base = $githubUser->getNickname()
                        ?: Str::before((string) $user->email, '@')
                        ?: Str::slug((string) $user->name, '_')
                        ?: 'user';
                    $user->update(['username' => User::generateUniqueUsername($base)]);
                }
            } else {
                $memberRole = Role::firstOrCreate(
                    ['name' => 'member'],
                    [
                        'display_name' => 'Member',
                        'description' => 'Team member with profile access',
                    ]
                );
                $email = (string) $githubUser->getEmail();
                $usernameBase = $githubUser->getNickname()
                    ?: Str::before($email, '@')
                    ?: Str::slug((string) ($githubUser->getName() ?? 'githubuser'), '_');
                $user = User::create([
                    'name' => $githubUser->getName() ?? $githubUser->getNickname() ?? 'GitHub User',
                    'username' => User::generateUniqueUsername($usernameBase ?: 'user'),
                    'email' => $email,
                    'github_id' => $githubUser->getId(),
                    'avatar' => $githubUser->getAvatar(),
                    'password' => Str::random(64),
                    'role_id' => $memberRole->id,
                    'email_verified_at' => now(),
                ]);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            return redirect($frontendUrl . '/auth/github/callback?token=' . $token);
        } catch (\Throwable $e) {
            report($e);
            return redirect($frontendUrl . '/auth/login?error=github_auth_failed');
        }
    }
}
