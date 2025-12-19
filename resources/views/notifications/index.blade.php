@extends('layouts.app')

@section('content')
<div class="container-custom">
    <div class="page-header">
        <h1 class="page-title">Notifikasi & Pengaturan Email</h1>
        <div style="display:flex; gap:10px;">
            <form action="{{ route('notifications.markAllRead') }}" method="POST">
                @csrf
                <button class="btn-action" style="background:#2C5282; border-color:#2B6CB0; color:white;">Tandai semua dibaca</button>
            </form>
            <a href="{{ route('dashboard') }}" class="btn-action">Kembali</a>
        </div>
    </div>

    <!-- Section 1: In-App Notifications -->
    <div class="main-card" style="margin-bottom: 30px;">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom: 20px;">
            <span style="font-size:1.5rem;">🔔</span>
            <h2 style="font-size:1.2rem; margin:0; color:#fff;">Notifikasi (In-App)</h2>
        </div>

        <div class="notification-list">
            @forelse($notifications as $notification)
            <div class="notification-item {{ !$notification->is_read ? 'unread' : '' }}">
                <div class="notif-content">
                    <div class="notif-text">
                        {{ $notification->message }}
                        <!-- Assuming message contains the full text like "Anda di-mention oleh..." -->
                    </div>
                    @if($notification->project_id || $notification->task_id)
                    <div class="notif-meta">
                        @if($notification->task) Tugas: {{ $notification->task->title }} @endif
                        @if($notification->project) | Proyek: {{ $notification->project->name }} @endif
                    </div>
                    @endif
                </div>
                <div class="notif-time">
                    {{ $notification->created_at->diffForHumans() }}
                </div>
            </div>
            @empty
            <div style="text-align:center; padding: 20px; color:#718096;">Tidak ada notifikasi baru.</div>
            @endforelse
        </div>
    </div>

    <!-- Section 2: Settings -->
    <div class="main-card">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom: 20px;">
            <span style="font-size:1.5rem;">⚙️</span>
            <h2 style="font-size:1.2rem; margin:0; color:#fff;">Pengaturan Notifikasi & Email</h2>
        </div>

        <div style="background: #0b0e14; border: 1px solid #2D3748; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            <form action="{{ route('notifications.updateSettings') }}" method="POST">
                @csrf
                <h3 style="color:#fff; font-size:1rem; margin-bottom:10px;">Notifikasi Email</h3>
                <p style="color:#718096; font-size:0.9rem; margin-bottom:20px;">Tentukan kapan Anda ingin menerima notifikasi melalui email.</p>
                
                <div style="display:flex; flex-direction:column; gap:15px;">
                    <label class="setting-item">
                        <span>Tugas baru diberikan kepada saya</span>
                        <input type="checkbox" name="email_new_task" @checked($settings['email_new_task'] ?? true)>
                    </label>
                    <label class="setting-item">
                        <span>Saya di-mention dalam komentar</span>
                        <input type="checkbox" name="email_mention" @checked($settings['email_mention'] ?? true)>
                    </label>
                    <label class="setting-item">
                        <span>Perubahan status pada proyek yang saya ikuti</span>
                        <input type="checkbox" name="email_project_status" @checked($settings['email_project_status'] ?? false)>
                    </label>
                    <label class="setting-item">
                        <span>Pengingat deadline tugas (24 jam sebelumnya)</span>
                        <input type="checkbox" name="email_deadline" @checked($settings['email_deadline'] ?? true)>
                    </label>
                </div>

                <div style="text-align:right; margin-top:20px;">
                    <button type="submit" class="btn-submit" style="width: auto; padding: 10px 30px;">Simpan Pengaturan</button>
                </div>
            </form>
        </div>

        <div style="background: #0b0e14; border: 1px solid #2D3748; border-radius: 8px; padding: 20px;">
            <h3 style="color:#fff; font-size:1rem; margin-bottom:10px;">Integrasi Email (Kirim Tugas via Email)</h3>
            <p style="color:#718096; font-size:0.9rem; margin-bottom:15px;">Anda dapat membuat tugas baru dengan mengirimkan email ke alamat khusus berikut:</p>
            
            <div style="background:#1A202C; padding:10px; border-radius:6px; color:#ECC94B; font-family:monospace; margin-bottom:15px; border:1px solid #2D3748;">
                task-pms@sistemanda.com
            </div>
            
            <p style="color:#718096; font-size:0.85rem;">Subjek email akan menjadi Nama Tugas, dan isi email akan menjadi Deskripsi Tugas.</p>
        </div>
    </div>
</div>

<script>
    function saveSettings() {
        // Simulating an API call
        const btn = document.querySelector('.btn-submit');
        const originalText = btn.innerText;
        
        btn.innerText = 'Menyimpan...';
        btn.disabled = true;
        btn.style.opacity = '0.7';

        setTimeout(() => {
            btn.innerText = originalText;
            btn.disabled = false;
            btn.style.opacity = '1';
            
            // Show toast or alert
            alert('Pengaturan notifikasi berhasil disimpan.');
        }, 1000);
    }
</script>

<style>
    /* Global Theme & Utilities (Copied/Adapted from Dashboard for consistency) */
    :root {
        --bg-body: #111418;
        --bg-card: #1C2333;
        --bg-input: #0b0e14;
        --border-color: #2D3748;
        --color-text: #E2E8F0;
        --color-muted: #A0AEC0;
        --primary: #ECC94B;
    }
    
    body { background-color: var(--bg-body); color: var(--color-text); font-family: 'Inter', sans-serif; }

    .container-custom { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .page-title { font-size: 1.8rem; font-weight: 800; color: #fff; margin: 0; }

    .main-card {
        background: #151a23;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #232a3b;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
    }

    .btn-action {
        background: #1A202C;
        color: var(--color-muted);
        border: 1px solid #2D3748;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    .btn-action:hover {
        background: #2D3748;
        color: #fff;
        border-color: #4A5568;
    }

    .btn-submit {
        background: var(--primary);
        color: #1A202C;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: bold;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        transition: transform 0.2s;
    }
    .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(236, 201, 75, 0.3); }

    /* Notification Specifics */
    .notification-list { display: flex; flex-direction: column; }
    .notification-item {
        padding: 20px;
        border-bottom: 1px solid #2D3748;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        transition: background 0.2s;
        border-radius: 8px; /* Slight radius for hover effect */
        margin-bottom: 5px;
    }
    .notification-item:last-child { border-bottom: none; }
    .notification-item:hover { background: #232a3b; }
    
    .notification-item.unread {
        border-left: 4px solid #ECC94B;
        background: linear-gradient(90deg, rgba(236, 201, 75, 0.05) 0%, transparent 100%);
    }
    
    .notif-text { color: #fff; font-size: 1rem; margin-bottom: 8px; line-height: 1.5; }
    .notif-meta { color: #718096; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; }
    .notif-time { color: #555e6f; font-size: 0.85rem; white-space: nowrap; margin-left: 15px; font-weight: 500;}

    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #1A202C;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .setting-item:hover { opacity: 0.9; }
    .setting-item:last-child { border-bottom: none; }
    .setting-item span { color: #E2E8F0; font-size: 1rem; }
    .setting-item input[type="checkbox"] {
        accent-color: #ECC94B;
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
</style>
@endsection
