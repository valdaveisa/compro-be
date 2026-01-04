<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Sistem Manajemen Proyek</title>
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
                <a href="#" class="admin-btn">Hubungi Admin</a>
            </div>

            <div class="login-form-container">
                <h1 class="login-title">Masuk</h1>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="form-input" placeholder="nama@email.com">
                        @error('email')
                            <div class="error-message" style="margin-top: 5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <div class="form-input-group">
                            <input id="password" type="password" name="password" required class="form-input" placeholder="**********">
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-footer">
                        <div class="error-message">
                            @if($errors->any() && !$errors->has('email'))
                                Kata sandi salah.
                            @endif
                        </div>

                    </div>

                    <button type="submit" class="submit-btn">
                        MASUK
                    </button>


                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }
    </script>
</body>
</html>
