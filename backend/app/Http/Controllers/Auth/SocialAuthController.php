<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'https://kalapak-team.space');
            return redirect($frontendUrl . '/auth/login?error=google_auth_failed');
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Update google_id if user exists by email but hasn't linked Google yet
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
            // Update avatar from Google if user doesn't have one
            if (!$user->avatar && $googleUser->getAvatar()) {
                $user->update(['avatar' => $googleUser->getAvatar()]);
            }
        } else {
            // Create new user
            $memberRole = Role::where('name', 'member')->first();
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => null,
                'role_id' => $memberRole->id,
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;
        $frontendUrl = env('FRONTEND_URL', 'https://kalapak-team.space');

        return redirect($frontendUrl . '/auth/google/callback?token=' . $token);
    }
}
