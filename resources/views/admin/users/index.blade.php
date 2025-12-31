@extends('layouts.app')

@section('content')
<div class="container-custom">
    <div class="page-header">
        <h1 class="page-title">Manajemen Akun</h1>
        <a href="{{ route('dashboard') }}" class="btn-action">
            ← Kembali ke Dashboard
        </a>
    </div>

    <div style="background: #2D3748; border-radius: 12px; padding: 20px; border: 1px solid #4A5568;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="font-size: 1.1rem; color: #E2E8F0;">
                Total Akun: <span style="font-weight: bold; color:white;">{{ $users->count() }}</span>
            </div>
            <button onclick="openModal('modalAddUser')" class="btn-warning" style="padding: 10px 20px; font-weight: bold; border-radius: 6px; cursor: pointer; border: none; display: flex; align-items: center; gap: 8px;">
                <span>+</span> Tambah Akun
            </button>
        </div>

        @if(session('success'))
            <div style="background: #48BB78; color: #fff; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background: #F56565; color: #fff; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                {{ session('error') }}
            </div>
        @endif

        <!-- Table -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                <thead style="color: #A0AEC0; font-size: 0.9rem; text-align: left;">
                    <tr>
                        <th style="padding: 15px; border-bottom: 1px solid #4A5568;">Nama</th>
                        <th style="padding: 15px; border-bottom: 1px solid #4A5568;">Email</th>
                        <th style="padding: 15px; border-bottom: 1px solid #4A5568;">Role</th>
                        <th style="padding: 15px; border-bottom: 1px solid #4A5568;">Aksi</th>
                    </tr>
                </thead>
                <tbody style="color: #fff;">
                    @foreach($users as $user)
                    <tr style="border-bottom: 1px solid #4A5568;">
                        <td style="padding: 15px; border-bottom: 1px solid #4A5568;">{{ $user->name }}</td>
                        <td style="padding: 15px; border-bottom: 1px solid #4A5568;">{{ $user->email }}</td>
                        <td style="padding: 15px; border-bottom: 1px solid #4A5568;">
                            @php
                                $roleColor = '#4299E1'; // Default User/Member (Blue)
                                $roleLabel = 'Anggota Tim';
                                if($user->role === 'admin') { $roleColor = '#38B2AC'; $roleLabel = 'Administrator'; } // Teal
                                if($user->role === 'pm') { $roleColor = '#ECC94B'; $roleLabel = 'Manajer Proyek'; } // Yellow
                            @endphp
                            <span style="background: {{ $roleColor }}; color: #1A202C; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; display:inline-block; min-width: 100px; text-align:center;">
                                {{ $roleLabel }}
                            </span>
                        </td>
                        <td style="padding: 15px; border-bottom: 1px solid #4A5568;">
                            <div style="display: flex; gap: 10px;">
                                <button onclick='openEditUserModal(@json($user))' style="background: #3182CE; color: white; border: none; padding: 8px 20px; border-radius: 6px; font-weight: bold; cursor: pointer;">Edit</button>
                                
                                <form action="{{ route('admin.users.reset2FA', $user->id) }}" method="POST" onsubmit="return confirm('Reset 2FA untuk user ini? Mereka harus scan QR code ulang setelah ini.');">
                                    @csrf
                                    <button type="submit" style="background: #D69E2E; color: white; border: none; padding: 8px 20px; border-radius: 6px; font-weight: bold; cursor: pointer;">Reset 2FA</button>
                                </form>
                                
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus User ini?');">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" style="background: #E53E3E; color: white; border: none; padding: 8px 20px; border-radius: 6px; font-weight: bold; cursor: pointer;">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL ADD USER -->
<div id="modalAddUser" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Akun Pengguna</h3>
            <button onclick="closeModal('modalAddUser')" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <p style="color: #A0AEC0; font-size: 0.9rem; margin-bottom: 20px;">Isi formulir di bawah dengan benar.</p>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nomor Telepon *</label>
                        <input type="text" name="phone_number" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-control">
                            <option value="">--Pilih Role--</option>
                            <option value="admin">Administrator</option>
                            <option value="pm">Project Manager</option>
                            <option value="member">Anggota Tim</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" onclick="closeModal('modalAddUser')" style="background: #E2E8F0; color: #1A202C; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer;">Batal</button>
                    <button type="submit" class="btn-warning" style="padding: 10px 30px; font-weight: bold; border-radius: 6px; border: none; cursor: pointer;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT USER -->
<div id="modalEditUser" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Akun Pengguna</h3>
            <button onclick="closeModal('modalEditUser')" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formEditUser" method="POST">
                @csrf
                @method('PUT') 
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" name="phone_number" id="editPhone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role" id="editRole" class="form-control">
                            <option value="admin">Administrator</option>
                            <option value="pm">Project Manager</option>
                            <option value="member">Anggota Tim</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #4A5568;">
                    <p style="color: #A0AEC0; font-size: 0.8rem; margin-bottom: 10px;">Kosongkan jika tidak ingin mengganti password</p>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" onclick="closeModal('modalEditUser')" style="background: #E2E8F0; color: #1A202C; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer;">Batal</button>
                    <button type="submit" class="btn-warning" style="padding: 10px 30px; font-weight: bold; border-radius: 6px; border: none; cursor: pointer;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .container-custom { max-width: 1200px; margin: 0 auto; padding: 40px 20px; font-family: 'Inter', sans-serif; }
    
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .page-title { font-size: 1.5rem; font-weight: 700; color: #fff; margin: 0; }
    .btn-action {
        background: #1A202C; color: #A0AEC0; border: 1px solid #2D3748;
        padding: 8px 16px; border-radius: 6px; cursor: pointer; text-decoration: none; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; transition: all 0.2s;
    }
    .btn-action:hover { background: #2D3748; color: #fff; border-color: #4A5568; }

    .btn-warning { background-color: #ECC94B; color: #1A202C; transition: background 0.2s; }
    .btn-warning:hover { background-color: #D69E2E; }
    
    /* Reusing modal styles from dashboard */
    .modal { display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; }
    .modal.active { display: flex; opacity: 1; }
    .modal-content { background-color: #2D3748; border-radius: 12px; padding: 0; width: 90%; max-width: 600px; border: 1px solid #4A5568; animation: slideUp 0.3s ease-out forwards; }
    .modal-header { padding: 20px 24px; border-bottom: 1px solid #4A5568; display: flex; justify-content: space-between; align-items: center; }
    .modal-title { font-size: 1.2rem; font-weight: 700; color: #fff; margin: 0; }
    .close-btn { background: none; border: none; color: #A0AEC0; font-size: 1.5rem; cursor: pointer; }
    .modal-body { padding: 24px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
    .form-group { margin-bottom: 0; }
    .form-label { display: block; margin-bottom: 8px; font-weight: 500; color: #E2E8F0; font-size: 0.9rem; }
    .form-control { width: 100%; padding: 10px 12px; border-radius: 6px; border: 1px solid #4A5568; background-color: #1A202C; color: #E2E8F0; font-size: 0.95rem; box-sizing: border-box; }
    
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<script>
    function openModal(id){ document.getElementById(id).classList.add('active'); }
    function closeModal(id){ document.getElementById(id).classList.remove('active'); }
    window.onclick = function(event) { if (event.target.classList.contains('modal')) { event.target.classList.remove('active'); } }

    function openEditUserModal(user) {
        document.getElementById('editName').value = user.name;
        document.getElementById('editEmail').value = user.email;
        document.getElementById('editPhone').value = user.phone_number || '';
        document.getElementById('editRole').value = user.role;
        
        let url = "{{ route('admin.users.update', ':id') }}";
        url = url.replace(':id', user.id);
        document.getElementById('formEditUser').action = url;
        
        openModal('modalEditUser');
    }
</script>
@endsection
