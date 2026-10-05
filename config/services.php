<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Midtrans
    |--------------------------------------------------------------------------
    |
    | Kunci API Midtrans. Selama MIDTRANS_SERVER_KEY kosong, aplikasi memakai
    | mode simulasi sehingga seluruh alur pembayaran tetap dapat didemonstrasikan
    | tanpa akun Midtrans.
    |
    | Cara mengaktifkan pembayaran sungguhan:
    |   1. Buat akun di dashboard.midtrans.com (mode Sandbox).
    |   2. Salin Server Key dan Client Key dari menu Settings > Access Keys.
    |   3. Isi baris berikut pada berkas .env:
    |        MIDTRANS_MODE=snap
    |        MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxx
    |        MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxx
    |   4. Daftarkan alamat notifikasi pada dashboard Midtrans:
    |        https://domain-anda/midtrans/notification
    |
    */

    'midtrans' => [
        'mode' => env('MIDTRANS_MODE', 'simulation'),

        'server_key' => env('MIDTRANS_SERVER_KEY'),

        'client_key' => env('MIDTRANS_CLIENT_KEY'),

        // Ubah menjadi true hanya setelah beralih ke kunci produksi.
        'production' => env('MIDTRANS_PRODUCTION', false),
    ],

];
