<?php

return [

    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'secret_key' => env('SUPABASE_SECRET_KEY'),
        'bucket' => env('SUPABASE_BUCKET', 'kalapak-assets'),
    ],

    'turnstile' => [
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'https://kalapakspace-backend.onrender.com/auth/google/callback'),
    ],

];
