@extends('layouts.app')

@section('content')
<div class="container-custom">
    <div class="page-header">
        <h1 class="page-title">Manajemen User (Admin)</h1>
        <a href="{{ route('dashboard') }}" class="btn-action">Kembali ke Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background:#48BB78; color:#fff; padding:10px; border-radius:6px; margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="dashboard-container">
        <table class="custom-table" style="width:100%; text-align:left; color:#fff;">
            <thead>
                <tr style="border-bottom: 1px solid #2D3748;">
                    <th style="padding:15px;">Nama</th>
                    <th style="padding:15px;">Email</th>
                    <th style="padding:15px;">Role</th>
                    <th style="padding:15px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr style="border-bottom: 1px solid #2D3748;">
                    <td style="padding:15px;">{{ $user->name }} <br> <span style="font-size:0.8rem; color:#A0AEC0;">{{ $user->username }}</span></td>
                    <td style="padding:15px;">{{ $user->email }}</td>
                    <td style="padding:15px;">
                        <span class="badge {{ $user->role === 'admin' ? 'bg-red' : 'bg-blue' }}" 
                              style="padding:5px 10px; border-radius:4px; font-size:0.8rem; background: {{ $user->role === 'admin' ? '#F56565' : '#4299E1' }};">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td style="padding:15px;">
                        <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="role" onchange="this.form.submit()" class="form-select" style="padding:5px; font-size:0.85rem;">
                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
    .container-custom {
        max-width: 1200px; margin: 0 auto; padding: 40px 20px;
    }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .page-title { font-size: 1.5rem; font-weight: 700; color: #fff; margin:0; }
    .btn-action {
        background: #1A202C; color: #A0AEC0; border: 1px solid #2D3748;
        padding: 8px 16px; border-radius: 6px; cursor: pointer; text-decoration: none; font-weight: 600; font-size: 0.85rem;
    }
    .dashboard-container {
        background: #151a23; border: 1px solid #232a3b; border-radius: 12px; padding: 20px; overflow-x: auto;
    }
    .form-select {
        background: #1A202C; color: #fff; border: 1px solid #2D3748; border-radius: 4px; outline:none;
    }
</style>
@endsection
