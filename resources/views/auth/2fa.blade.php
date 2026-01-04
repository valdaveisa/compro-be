<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Authentication - Sistem Manajemen Proyek</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <!-- Left Section -->
        <div class="left-section">
            <div class="branding">

                <div class="app-name">
                    <span>Sistem</span>
                    <span>Manajemen Proyek</span>
                </div>
            </div>
            
            <div class="hero-text">
                Kelola proyek<br>
                Kolaborasi<br>
                Eksekusi<br>
                Capai target
            </div>
        </div>

        <!-- Right Section -->
        <div class="right-section">
            <div class="top-bar">
                <div class="lang-selector">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                    <span>Indonesia</span>
                </div>
            </div>

            <div class="login-form-container">
                <h1 class="login-title">Two-Factor Authentication</h1>

                <p style="margin-bottom: 20px; color: #666; line-height: 1.5;">
                    @if($mode == 'setup')
                        <strong>Konfigurasi Awal:</strong><br>
                        Scan QR Code berikut menggunakan aplikasi Authenticator Anda (Google Authenticator, Authy, dll).
                    @else
                        Masukkan kode 6 digit dari aplikasi Authenticator Anda untuk melanjutkan.
                    @endif
                </p>

                @if($mode == 'setup')
                    <div style="text-align: center; margin-bottom: 20px; background: #fff; padding: 10px; border-radius: 8px; display: inline-block;">
                        {!! $QR_Image !!}
                        <div style="font-size: 12px; margin-top: 10px; color: #555; word-break: break-all;">
                            Secret: {{ $secret }}
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('2fa.verify') }}">
                    @csrf

                    <div class="form-group">
                        <label for="one_time_password" class="form-label">Kode Authenticator</label>
                        <input id="one_time_password" type="text" name="one_time_password" required autofocus class="form-input" placeholder="123456" autocomplete="off" />
                        @error('one_time_password')
                            <div class="error-message" style="margin-top: 5px;">{{ $message }}</div>
                        @enderror
                        @if(session('error'))
                            <div class="error-message" style="margin-top: 5px;">{{ session('error') }}</div>
                        @endif
                    </div>

                    <button type="submit" class="submit-btn" style="margin-top: 20px;">
                        VERIFIKASI
                    </button>
                </form>

                <div class="no-account" style="margin-top: 20px;">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: #666; text-decoration: underline; cursor: pointer; padding: 0; font-size: 0.9em;">
                            Logout / Batalkan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
