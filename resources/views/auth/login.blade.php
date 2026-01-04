@extends('layouts.app')

@section('content')
<style>
    body, html {
        height: 100%;
        margin: 0;
        font-family: 'Inter', sans-serif;
        box-sizing: border-box;
    }
    *, *:before, *:after {
        box-sizing: inherited;
    }
    .login-container {
        background-image: url('{{ asset('images/login-bg.jpg') }}');
        background-size: cover;
        background-position: center;
        height: 100vh;
        width: 100%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.85); /* Slightly transparent dark overlay */
        z-index: 1;
    }
    .content-wrapper {
        position: relative;
        z-index: 2;
        display: flex;
        width: 100%;
        max-width: 1200px;
        padding: 0 40px;
        justify-content: space-between;
        align-items: center;
    }
    .brand-text-section {
        max-width: 600px;
        color: white;
    }
    .brand-text-section h1 {
        font-size: 4rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 20px;
        color: #fff;
    }
    .login-card {
        background: transparent;
        padding: 0;
        border-radius: 0;
        width: 450px; /* Widened for better proportion */
    }
    .login-header-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: white;
        margin-bottom: 30px; /* Increased spacing */
        margin-top: 0;
        line-height: 1.2;
    }
    .form-group {
        margin-bottom: 25px; /* Increased spacing between fields */
    }
    .form-label {
        color: #fff;
        display: block;
        margin-bottom: 10px; /* Increased spacing */
        font-size: 1rem;
        font-weight: 500;
        margin-left: 5px; /* Slight indentation for neatness relative to pill curve */
    }
    .form-input {
        width: 100%;
        height: 55px;
        padding: 0 25px;
        display: block;
        border-radius: 55px;
        border: none;
        font-size: 1rem;
        outline: none;
        background: #fff;
        color: #333;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        box-sizing: border-box !important; /* Force width consistency */
        margin: 0; /* Reset margins */
    }
    .form-input:focus {
        box-shadow: 0 0 0 3px rgba(246, 224, 94, 0.5);
    }
    .password-input-wrapper {
        position: relative;
        width: 100%;
        box-sizing: border-box !important;
    }
    .toggle-password {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #718096;
        display: flex;
        align-items: center;
        padding: 0;
        z-index: 10;
    }
    .submit-btn {
        width: 100%;
        height: 55px;
        padding: 0;
        display: block;
        background-color: #F6E05E;
        color: #1A202C;
        font-weight: 700;
        border: none;
        border-radius: 55px;
        font-size: 1.1rem;
        cursor: pointer;
        margin-top: 35px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        line-height: 55px; /* Vertically center text */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s;
        box-sizing: border-box !important; /* Force width consistency */
        margin-left: 0;
        margin-right: 0;
    }
    .submit-btn:hover {
        background-color: #ECC94B;
        transform: translateY(-2px);
    }
    .app-logo-corner {
        position: absolute;
        top: 30px;
        left: 40px;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 15px;
        color: white;
    }
    .logo-pill {
        background: white;
        color: #1A202C;
        padding: 8px 20px;
        border-radius: 30px;
        font-weight: bold;
    }
    .app-name-small {
        display: flex;
        flex-direction: column;
        line-height: 1.1;
        font-size: 0.9rem;
    }
    .contact-admin-btn {
        position: absolute;
        top: 30px;
        right: 40px;
        z-index: 10;
        background: #ECC94B;
        color: #1A202C;
        padding: 8px 20px;
        border-radius: 30px;
        font-weight: bold;
        text-decoration: none;
        font-size: 0.85rem;
    }

    form {
        width: 100%;
        margin: 0;
        padding: 0;
    }
</style>

<div class="login-container">
    <div class="overlay"></div>



    <!-- Top Right Contact Admin (Reference shows it, but user said "remove contact admin")
         Wait, user said "tidak usah ada hubungi admin" in prompts. But reference has it. 
         Constraint: "jadikan gambar pertama ini sebagai referensi login page" BUT "tidak usah ada hubungi admin dan lupa kata sandi"
         So I will OMIT the contact admin button.
    -->

    <div class="content-wrapper">
        <!-- Left Text -->
        <div class="brand-text-section">
            <h1>Kelola proyek<br>Kolaborasi<br>Eksekusi<br>Capai target</h1>
        </div>

        <!-- Right Form -->
        <div class="login-card">
            <h2 class="login-header-title">Masuk</h2>
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required placeholder="Masukkan Email">
                </div>

                <div class="form-group">
                    <label class="form-label">Kata Sandi</label>
                    <div class="password-input-wrapper">
                        <input type="password" name="password" id="password" class="form-input" placeholder="Masukkan Kata Sandi" required>
                         <button type="button" class="toggle-password" onclick="togglePassword()">
                            <!-- Simple Eye Icon or Text -->
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <!-- No Forgot Password -->

                <div class="form-group" style="margin-bottom: 0;">
                    <button type="submit" class="submit-btn">MASUK</button>
                </div>
                
                <!-- No "Hubungi Admin" bottom link -->
            </form>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const x = document.getElementById("password");
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }
</script>
@endsection
