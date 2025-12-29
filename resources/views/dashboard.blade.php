@extends('layouts.app')

@section('content')
<style>
    :root {
        --bg-body: #111418;
        --bg-card: #1C2333;
        --bg-input: #0b0e14;
        --border-color: #2D3748;
        --color-text: #E2E8F0;
        --color-muted: #A0AEC0;
        --primary: #ECC94B;
        --danger: #F56565;
        --success: #48BB78;
        --info: #4299E1;
        --warning: #ED8936;
    }
    
    body {
        background-color: var(--bg-body) !important;
        color: var(--color-text);
        font-family: 'Inter', sans-serif;
    }
    
    .container-custom {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    
    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .page-title { font-size: 1.8rem; font-weight: 800; color: #fff; margin: 0; }
    
    /* Card Container */
    .main-card {
        background: #151a23;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #232a3b;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
    }

    /* Table Styles */
    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px; /* Gap between rows */
        margin-top: 20px;
    }
    .custom-table th {
        text-align: left;
        color: var(--color-muted);
        font-size: 0.85rem;
        font-weight: 600;
        padding: 10px 20px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .custom-table td {
        background: var(--bg-card);
        padding: 16px 20px;
        color: #fff;
        font-size: 0.95rem;
        vertical-align: middle;
        border: 1px solid transparent;
        transition: border-color 0.2s, transform 0.2s;
    }
    .custom-table tr td:first-child { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
    .custom-table tr td:last-child { border-top-right-radius: 8px; border-bottom-right-radius: 8px; }
    
    .custom-table tr:hover td {
        border-color: #2D3748;
        background: #232a3b;
    }

    /* Column specific */
    .col-name { font-weight: 700; font-size: 1rem; color: #fff; display:block;}
    .col-sub { font-size: 0.8rem; color: #718096; margin-top: 4px; display:block; }
    
    /* Badges */
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-active { background: rgba(72, 187, 120, 0.2); color: #68D391; }
    .badge-done { background: rgba(66, 153, 225, 0.2); color: #63B3ED; }
    .badge-planned { background: rgba(160, 174, 192, 0.2); color: #CBD5E0; }
    .badge-on_hold { background: rgba(237, 137, 54, 0.2); color: #FBD38D; }
    
    /* Action Buttons */
    .btn-action-group { display: flex; gap: 8px; }
    .btn-action {
        background: #1A202C;
        color: var(--color-muted);
        border: 1px solid #2D3748;
        padding: 6px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-action:hover {
        background: #2D3748;
        color: #fff;
        border-color: #4A5568;
    }
    .btn-action.btn-danger {
        background: rgba(245, 101, 101, 0.1);
        color: #FC8181;
        border-color: rgba(245, 101, 101, 0.3);
    }
    .btn-action.btn-danger:hover {
        background: rgba(245, 101, 101, 0.2);
        border-color: #FC8181;
    }

    /* Main Button */
    .btn-main {
        background: var(--primary);
        color: #1A202C;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: bold;
        border: none;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-main:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(236, 201, 75, 0.3);
    }
    
    /* Info Panel (Bottom) */
    .info-panel {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-top: 30px;
    }
    .detail-card {
        background: #151a23;
        border: 1px solid #232a3b;
        border-radius: 12px;
        padding: 24px;
        min-height: 200px;
    }
    
    /* Priority Badges */
    .badge-priority-high { background: rgba(245, 101, 101, 0.2); color: #FC8181; border: 1px solid rgba(245, 101, 101, 0.3); }
    .badge-priority-medium { background: rgba(236, 201, 75, 0.2); color: #ECC94B; border: 1px solid rgba(236, 201, 75, 0.3); }
    .badge-priority-low { background: rgba(66, 153, 225, 0.2); color: #63B3ED; border: 1px solid rgba(66, 153, 225, 0.3); }

    /* Progress Bar */
    .progress-container { width: 100%; background: #2D3748; height: 8px; border-radius: 4px; overflow: hidden; margin-top: 5px; }
    .progress-bar { height: 100%; background: #48BB78; transition: width 0.3s; }

    /* Subtasks */
    .subtask-item { display: flex; align-items: center; justify-content: space-between; padding: 10px; background: #1A202C; border-radius: 6px; margin-bottom: 8px; border: 1px solid #2D3748; }
    .subtask-item.completed { opacity: 0.6; text-decoration: line-through; }
    
    /* Timer */
    .timer-display { font-family: monospace; font-size: 1.5rem; font-weight: bold; color: #fff; margin-bottom: 10px; }
    .timer-active { color: #48BB78; animation: pulse 1s infinite; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }
    
    /* MODAL OVERRIDES */
    .modal {
        display: none; 
        position: fixed; 
        z-index: 999; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        background-color: rgba(0,0,0,0.8); 
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
    }
    .modal.active { display: flex; animation: fadeIn 0.2s; }
    
    * { box-sizing: border-box; }

    .modal-content {
        background-color: #151a23;
        border: 1px solid #2D3748;
        border-radius: 16px;
        width: 100%; /* Fix width to fit container properly if max-width is set */
        max-width: 650px; /* Slightly wider to accommodate gaps */
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    .modal-header {
        padding: 30px 40px;
        border-bottom: 1px solid #2D3748;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-title { font-size: 1.5rem; font-weight: 700; color: #fff; }
    .modal-body { padding: 40px; } /* Consistent deep padding on all sides */
    
    /* ... form styles ... */
    
    /* Utility for grid inputs to ensure sufficient gap */
    .form-grid {
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 30px; /* Increased gap as requested */
    }
    
    .form-label { 
        color: var(--color-muted); 
        font-size: 0.9rem; 
        margin-bottom: 8px; 
        display:block; 
        font-weight: 500;
    }
    .form-control {
        background: var(--bg-input);
        border: 1px solid #2D3748;
        padding: 14px;
        border-radius: 8px;
        color: #fff;
        width: 100%;
        margin-bottom: 24px; /* Increased spacing */
        font-size: 0.95rem;
        /* Ensure calendar and select dropdowns use dark mode defaults (white text/icons) */
        color-scheme: dark; 
    }
    .form-control:focus { 
        outline: none; 
        border-color: #63B3ED; 
        box-shadow: 0 0 0 3px rgba(99, 179, 237, 0.1); 
    }

    /* Remove the invert filter that might be flipping white icons back to black */
    ::-webkit-calendar-picker-indicator {
        cursor: pointer;
        opacity: 0.8;
    }
    ::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }
    
    .btn-submit {
        background: var(--primary);
        color: #1A202C;
        width: 100%;
        padding: 14px; /* Larger button */
        border-radius: 8px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        transition: transform 0.1s;
    }
    .btn-submit:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(236, 201, 75, 0.2);
    }
    
    .btn-cancel {
        background: transparent;
        color: #CBD5E0;
        border: 1px solid #4A5568;
        padding: 14px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.2s;
    }
    .btn-cancel:hover {
        border-color: #A0AEC0;
        color: #fff;
        background: rgba(255,255,255,0.05);
    }

    /* Custom Multi Select Styles */
    .multi-select-container { position: relative; width: 100%; margin-bottom: 24px; }
    .multi-select-trigger {
        background: var(--bg-input);
        border: 1px solid #2D3748;
        padding: 14px;
        border-radius: 8px;
        color: #fff;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        user-select: none;
    }
    .multi-select-trigger:after {
        content: '\25BC';
        font-size: 0.8rem;
        color: #A0AEC0;
    }
    .multi-select-options {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #1A202C;
        border: 1px solid #2D3748;
        border-radius: 8px;
        margin-top: 5px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        padding: 5px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .multi-select-options.active { display: block; }
    .multi-select-option {
        display: flex;
        align-items: center;
        padding: 10px;
        color: #fff;
        cursor: pointer;
        border-radius: 6px;
        transition: background 0.2s;
    }
    .multi-select-option:hover { background: #2D3748; }
    .multi-select-option input { margin-right: 10px; accent-color: var(--primary); transform: scale(1.2); }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; }}
    
    /* Selected Project Section */
    .selected-project-header {
        margin-bottom: 20px;
        border-bottom: 1px solid #2D3748;
        padding-bottom: 20px;
    }
    .selected-title { font-size: 1.5rem; font-weight: 700; color: #fff; }
    
    .task-list-title {
        font-size: 1rem; color: #fff; margin-bottom: 15px; font-weight: bold;
    }
    
     /* Modal Large for Task Detail same as before but customized colors */
    .modal-large { max-width: 900px; width: 95%; }
    .detail-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 0; min-height: 500px; }
    .detail-left { padding: 30px; border-right: 1px solid #2D3748; }
    .detail-right { padding: 30px; background-color: #0b0e14; }

</style>

<div class="container-custom">
    <!-- Header -->
    <div class="page-header">
        <h1 class="page-title">Manajemen Proyek & Tugas</h1>
        <div class="btn-action-group">
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.users.index') }}" class="btn-action" style="font-size: 0.9rem; margin-right: 5px;">👑 Admin Panel</a>
            @endif
            <a href="{{ route('reports.index') }}" class="btn-action" style="font-size: 0.9rem; margin-right: 5px;">📊 Laporan</a>
            <a href="{{ route('notifications.index') }}" class="btn-action" style="font-size: 0.9rem; position: relative; margin-right: 10px;">
                🔔 Notifikasi
                @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                <span style="position: absolute; top: -8px; right: -8px; background: #E53E3E; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.7rem; font-weight: bold; min-width: 18px; text-align: center;">
                    {{ $unreadNotificationsCount }}
                </span>
                @endif
            </a>
            <button class="btn-action" style="font-size: 0.9rem;" onclick="location.reload()">Refresh</button>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-action btn-danger" style="font-size: 0.9rem;">
                    Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Main Card -->
    <div class="main-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px;">
            <div style="color: #718096;">Total Proyek: <span style="color:#fff; font-weight:bold;">{{ $projects->count() }}</span></div>
            <button onclick="openModal('modalCreateProject')" class="btn-main">+ Tambah Proyek</button>
        </div>

        <!-- Project Table -->
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Nama Proyek</th>
                    <th>Deskripsi</th>
                    <th>Tgl Mulai</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                <tr onclick="window.location='{{ route('dashboard', ['project_id' => $project->id]) }}'" style="cursor:pointer;">
                    <td>
                        <span class="col-name">{{ $project->name }}</span>
                        <span class="col-sub">{{ $project->members->where('role_in_project', 'pm')->first()->email ?? '-' }}</span>
                    </td>
                    <td><span style="color: #A0AEC0; font-size:0.9rem;">{{ Str::limit($project->description, 50) }}</span></td>
                    <td>{{ $project->start_date }}</td>
                    <td>{{ $project->end_date }}</td>
                    <td><span class="badge badge-{{ $project->status }}">{{ ucfirst($project->status) }}</span></td>
                    <td onclick="event.stopPropagation()">
                        <div class="btn-action-group">
                            <a href="{{ route('dashboard', ['project_id' => $project->id]) }}" class="btn-action">Lihat</a>
                            <button onclick="openEditProjectModal({{ json_encode($project) }})" class="btn-action">Edit</button>
                            <form action="{{ route('projects.destroy', $project->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus proyek?');">
                                @csrf @method('DELETE')
                                <button class="btn-action btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; color:#718096; padding: 30px;">Belum ada proyek</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Info Panel / Selected Detail -->
    <div class="info-panel">
        <!-- Left: Selected Project Detail -->
        <div class="detail-card">
            @if(isset($selectedProject))
                <div class="selected-project-header">
                    <div style="display:flex; justify-content:space-between; align-items:start;">
                        <div>
                             <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                                <h2 class="selected-title" style="margin:0;">{{ $selectedProject->name }}</h2>
                                <span class="badge badge-{{ $selectedProject->status }}">{{ ucfirst($selectedProject->status) }}</span>
                            </div>
                            <div style="color:#CBD5E0; margin-bottom:15px; max-width:600px; line-height:1.5;">{{ $selectedProject->description }}</div>
                        </div>
                        
                        <div style="text-align:right;">
                            <div style="font-size:0.8rem; color:#A0AEC0; margin-bottom:5px;">Deadline</div>
                            <div style="font-weight:bold; color:#fff; font-size:1.1rem;">{{ $selectedProject->end_date }}</div>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr auto; gap:20px; align-items:end; margin-top:20px; padding-top:20px; border-top:1px solid #2D3748;">
                        <div>
                             <div style="font-size:0.8rem; color:#A0AEC0; margin-bottom:8px;">Anggota Tim</div>
                             <div style="display:flex; flex-wrap:wrap; gap:5px;">
                                @foreach($selectedProject->members as $member)
                                    <span style="background:#2D3748; color:#E2E8F0; padding:4px 10px; border-radius:4px; font-size:0.85rem; border:1px solid #4A5568;">
                                        {{ $member->name }} 
                                        <span style="font-size:0.7rem; opacity:0.7; margin-left:3px;">({{ $member->pivot->role_in_project ?? 'member' }})</span>
                                    </span>
                                @endforeach
                             </div>
                        </div>

                        <div style="display:flex; gap:10px;">
                            <button onclick="openModal('modalCreateTask')" class="btn-main" style="padding: 8px 16px; font-size:0.9rem;">+ Tugas</button>
                            <button onclick="openEditProjectModal({{ json_encode($selectedProject) }})" class="btn-action">Edit</button>
                            <button onclick="openManageMembersModal({{ json_encode($selectedProject) }})" class="btn-action">Anggota</button>
                            <button onclick="openActivityLogModal({{ $selectedProject->id }})" class="btn-action">Log</button>
                            <a href="{{ route('projects.visualize', $selectedProject->id) }}" class="btn-action">Visual</a>
                        </div>
                    </div>
                </div>

                <h3 class="task-list-title">Daftar Tugas</h3>
                 @if($selectedProject->tasks->count() > 0)
                    <div style="display:flex; flex-direction:column; gap:10px;">
                    @foreach($selectedProject->tasks as $task)
                        <div style="background:#0b0e14; padding:15px; border-radius:8px; display:flex; justify-content:space-between; align-items:center; border:1px solid #232a3b; transition:border-color 0.2s; cursor:pointer;" onclick="openTaskDetailModal({{ $task->id }})">
                            <div>
                                <div style="font-weight:bold; color: {{ $task->status == 'done' ? '#718096' : 'white' }}; {{ $task->status == 'done' ? 'text-decoration: line-through;' : '' }}">
                                    {{ $task->title }}
                                </div>
                                <div style="font-size:0.8rem; color:#718096; margin-top:2px;">
                                    <span style="margin-right:10px;">👤 {{ $task->assignee->name ?? '-' }}</span>
                                    <span>📅 {{ $task->due_date }}</span>
                                </div>
                            </div>
                            <div style="display:flex; gap:8px; align-items:center;" onclick="event.stopPropagation()">
                                <span class="badge badge-{{ $task->status }}">{{ ucfirst($task->status) }}</span>
                                <span class="badge badge-priority-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
                                
                                <div class="btn-action-group">
                                    <button onclick="openEditTaskModal({{ $task->id }})" class="btn-action" style="padding:4px 8px;">✎</button>
                                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus?');">
                                        @csrf @method('DELETE')
                                        <button class="btn-action btn-danger" style="padding:4px 8px;">×</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                @else
                    <div style="color:#718096; background:#0b0e14; padding:20px; border-radius:8px; text-align:center; border:1px dashed #2D3748;">Belum ada tugas.</div>
                @endif
            @else
                <div style="color:#718096; text-align:center; padding-top:50px;">
                    <div style="font-size:3rem; margin-bottom:10px; opacity:0.2;">📂</div>
                    Pilih sebuah proyek untuk melihat detail & tugas.
                </div>
            @endif
        </div>

        <!-- Right: Stats -->
        <div class="detail-card">
            <h3 style="color:#A0AEC0; font-size:0.85rem; font-weight:700; text-transform:uppercase; margin-bottom:20px; letter-spacing:0.05em;">Informasi Cepat</h3>
            
            <div style="display:flex; flex-direction:column; gap:15px;">
                <div style="background:#1A202C; padding:15px; border-radius:8px; border:1px solid #2D3748;">
                    <div style="color:#A0AEC0; font-size:0.8rem;">Proyek Aktif</div>
                    <div style="font-size:1.5rem; font-weight:bold; color:#fff;">{{ $projects->where('status','active')->count() }}</div>
                </div>
                <div style="background:#1A202C; padding:15px; border-radius:8px; border:1px solid #2D3748;">
                    <div style="color:#A0AEC0; font-size:0.8rem;">Proyek Selesai</div>
                    <div style="font-size:1.5rem; font-weight:bold; color:#fff;">{{ $projects->where('status','done')->count() }}</div>
                </div>
                 <div style="background:#1A202C; padding:15px; border-radius:8px; border:1px solid #2D3748;">
                    <div style="color:#A0AEC0; font-size:0.8rem;">Total Proyek</div>
                    <div style="font-size:1.5rem; font-weight:bold; color:#fff;">{{ $projects->count() }}</div>
                </div>
            </div>
            
            <div style="margin-top:30px; border-top:1px solid #2D3748; padding-top:20px;">
               <div style="color:#A0AEC0; font-size:0.85rem; margin-bottom:10px;">Shortcut</div>
               <button onclick="openModal('modalCreateProject')" class="btn-action" style="width:100%; justify-content:start; margin-bottom:5px;">+ Proyek Baru</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CREATE PROJECT -->
<div id="modalCreateProject" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Proyek</h3>
            <button onclick="closeModal('modalCreateProject')" class="close-btn" style="color:#fff; background:none; border:none; font-size:1.5rem;">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <div>
                        <label class="form-label">Nama Proyek *</label>
                        <input type="text" name="name" class="form-control" placeholder="Nama Proyek" required>
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="planned">Planned</option>
                            <option value="on_hold">On Hold</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                </div>
                
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
                
                <div class="form-grid">
                    <div>
                        <label class="form-label">Tanggal Mulai *</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Deadline *</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </div>

                <label class="form-label">Anggota Tim (Pilih minimal 1)</label>
                <div class="multi-select-container" id="createProjectMembers">
                    <div class="multi-select-trigger" onclick="toggleMultiSelect('createProjectMembers')">
                        Pilih Anggota...
                    </div>
                    <div class="multi-select-options">
                        <input type="text" class="multi-select-search" placeholder="Cari anggota..." onkeyup="filterMultiSelect(this)" onclick="event.stopPropagation()" style="width: 100%; padding: 8px; margin-bottom: 5px; background: #0b0e14; border: 1px solid #2D3748; color: #fff; border-radius: 4px; box-sizing: border-box;">

                        @foreach($allUsers as $u)
                        <label class="multi-select-option">
                            <input type="checkbox" name="members[]" value="{{ $u->id }}" onchange="updateMultiSelectLabel('createProjectMembers')">
                            {{ $u->name }} ({{ $u->email }})
                        </label>
                        @endforeach
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
                    <button type="button" onclick="closeModal('modalCreateProject')" style="background:transparent; color:#fff; border:1px solid #4A5568; padding:10px 20px; border-radius:6px; cursor:pointer;">Batal</button>
                    <button type="submit" class="btn-submit" style="width:auto; padding:10px 30px;">Simpan Proyek</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT PROJECT -->
<div id="modalEditProject" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Proyek</h3>
            <button onclick="closeModal('modalEditProject')" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formEditProject" method="POST">
                @csrf @method('PUT')
                <div class="form-group"><label class="form-label">Nama Proyek</label><input type="text" name="name" id="editProjectName" class="form-control"></div>
                 <label class="form-label">Status</label>
                 <select name="status" id="editProjectStatus" class="form-control">
                     <option value="active">Active</option>
                     <option value="planned">Planned</option>
                     <option value="on_hold">On Hold</option>
                     <option value="done">Done</option>
                 </select>
                 
                 <label class="form-label">Deskripsi</label>
                 <textarea name="description" id="editProjectDescription" class="form-control" rows="3"></textarea>

                 <div class="form-grid">
                    <div>
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="editProjectStartDate" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Deadline</label>
                        <input type="date" name="end_date" id="editProjectEndDate" class="form-control">
                    </div>
                 </div>

                 <!-- Members Multi Select for Edit -->
                 <label class="form-label">Anggota Tim</label>
                 <div class="multi-select-container" id="editProjectMembers">
                    <div class="multi-select-trigger" onclick="toggleMultiSelect('editProjectMembers')">
                        Pilih Anggota...
                    </div>
                    <div class="multi-select-options">
                        <input type="text" class="multi-select-search" placeholder="Cari anggota..." onkeyup="filterMultiSelect(this)" onclick="event.stopPropagation()" style="width: 100%; padding: 8px; margin-bottom: 5px; background: #0b0e14; border: 1px solid #2D3748; color: #fff; border-radius: 4px; box-sizing: border-box;">

                        @foreach($allUsers as $u)
                        <label class="multi-select-option">
                            <input type="checkbox" name="members[]" value="{{ $u->id }}" id="edit_member_{{ $u->id }}" onchange="updateMultiSelectLabel('editProjectMembers')">
                            {{ $u->name }} ({{ $u->email }})
                        </label>
                        @endforeach
                    </div>
                 </div>

                 <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
                    <button type="button" onclick="closeModal('modalEditProject')" style="background:transparent; color:#fff; border:1px solid #4A5568; padding:10px 20px; border-radius:6px; cursor:pointer;">Batal</button>
                    <button type="submit" class="btn-submit" style="width:auto; padding:10px 30px;">Update Proyek</button>
                 </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL MANAGE MEMBERS -->
<div id="modalManageMembers" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Kelola Anggota Tim</h3>
            <button onclick="closeModal('modalManageMembers')" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <div class="modal-body">
            <div id="manageMembersList" style="display:flex; flex-direction:column; gap:15px;">
                <!-- Populated by JS -->
            </div>
            <div style="margin-top:20px; font-size:0.8rem; color:#718096;">
                * Project Manager (PM) memiliki akses penuh. Member dapat mengelola tugas.
            </div>
        </div>
    </div>
</div>


<!-- MODAL CREATE TASK -->
<div id="modalCreateTask" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3 class="modal-title">Tambah Tugas</h3><button onclick="closeModal('modalCreateTask')" class="close-btn" style="color:#fff; background:none; border:none; font-size:1.5rem;">&times;</button></div>
        <div class="modal-body">
            @if(isset($selectedProject))
            <form action="{{ route('tasks.store', $selectedProject->id) }}" method="POST">
                @csrf
                <label class="form-label">Judul</label><input name="title" class="form-control" required>
                <label class="form-label">Deskripsi</label><textarea name="description" class="form-control"></textarea>
                 <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div><label class="form-label">Prioritas</label><select name="priority" class="form-control"><option value="medium">Medium</option><option value="high">High</option><option value="low">Low</option></select></div>
                    <div><label class="form-label">Deadline</label><input type="date" name="due_date" class="form-control"></div>
                </div>
                <button type="submit" class="btn-submit">Simpan</button>
            </form>
            @endif
        </div>
    </div>
</div>

<!-- MODAL EDIT TASK -->
<div id="modalEditTask" class="modal">
     <div class="modal-content">
        <div class="modal-header"><h3 class="modal-title">Edit Tugas</h3><button onclick="closeModal('modalEditTask')" class="close-btn" style="color:#fff; background:none; border:none; font-size:1.5rem;">&times;</button></div>
        <div class="modal-body">
            <form id="formEditTask" method="POST">
                @csrf @method('PUT')
                <label class="form-label">Judul</label><input name="title" id="editTaskTitle" class="form-control" required>
                <label class="form-label">Deskripsi</label><textarea name="description" id="editTaskDescription" class="form-control"></textarea>
                <label class="form-label">Deadline</label><input type="date" name="due_date" id="editTaskDueDate" class="form-control">
                <label class="form-label">Status</label>
                <select name="status" id="editTaskStatus" class="form-control">
                        <option value="todo">Todo</option>
                        <option value="in_progress">In Progress</option>
                        <option value="review">Review</option>
                        <option value="done">Done</option>
                </select>
                
                <div class="form-grid">
                    <div>
                        <label class="form-label">Prioritas</label>
                        <select name="priority" id="editTaskPriority" class="form-control">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Update</button>
            </form>
        </div>
    </div>
</div>

<!-- MODAL TASK DETAIL (Restyled) -->
<div id="modalTaskDetail" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title" id="detailTaskTitle">Judul Tugas</h3>
            <button onclick="closeModal('modalTaskDetail')" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <div class="detail-layout">
            <div class="detail-left">
                <h4 style="color:#A0AEC0; margin-bottom:10px;">Deskripsi</h4>
                <div id="detailTaskDescription" style="color:#fff; margin-bottom:30px;"></div>
                
                <h4 style="color:#A0AEC0; margin-bottom:10px;">Subtasks</h4>
                <div id="subtaskList" style="margin-bottom:15px;"></div>
                <!-- Simple form to add subtask -->
                <div style="display:flex; gap:10px; margin-bottom:30px;">
                    <input id="newSubtaskTitle" class="form-control" style="margin-bottom:0; padding:8px;" placeholder="New Subtask...">
                    <select id="newSubtaskPriority" class="form-control" style="margin-bottom:0; width:auto; padding:8px;">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                    <button onclick="createSubtask()" class="btn-submit" style="width:auto; padding:8px 16px;">Add</button>
                </div>

                <h4 style="color:#fff; margin-bottom:15px;">Komentar & Diskusi</h4>
                <div id="commentList" style="max-height:300px; overflow-y:auto; margin-bottom:15px;"></div>
                <div style="display:flex; gap:10px; flex-direction:column;">
                    <textarea id="commentContent" class="form-control" rows="2" placeholder="Tulis komentar..."></textarea>
                    <button onclick="submitComment()" class="btn-submit" style="width:auto; align-self:flex-end;">Kirim</button>
                </div>
                
                <div style="margin-top:20px;">
                    <h4 style="color:#A0AEC0;">Lampiran</h4>
                    <div id="attachmentList"></div>
                    <button onclick="document.getElementById('fileInput').click()" class="btn-action" style="margin-top:10px;">+ Upload</button>
                    <input type="file" id="fileInput" hidden onchange="uploadFile()">
                    <span id="uploadStatus" style="color:#A0AEC0; margin-left:10px;"></span>
                </div>
            </div>
            <div class="detail-right">
                <h4 style="color:#A0AEC0; margin-bottom:20px;">Informasi Tugas</h4>
                


                <div style="margin-bottom:15px;">
                    <label class="form-label">Penanggung Jawab</label>
                    <div style="display:flex; align-items:center; gap:10px; color:#fff;">
                        <div id="assigneeAvatar" style="width:30px; height:30px; background:#4A5568; border-radius:50%; display:flex; align-items:center; justify-content:center;">-</div>
                        <div>
                            <div id="assigneeName">-</div>
                            <div id="assigneeEmail" style="font-size:0.8rem; color:#A0AEC0;"></div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-bottom:15px;">
                    <label class="form-label">Status & Prioritas</label>
                    <div style="display:flex; gap:10px; align-items:center; margin-bottom:5px;">
                        <div id="detailTaskStatus" class="badge"></div>
                        <div id="detailTaskPriority" class="badge"></div>
                    </div>
                </div>
                


                <div style="margin-bottom:15px;"><label class="form-label">Deadline</label><div id="detailTaskDueDate" style="color:#fff;"></div></div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ACTIVITY LOG -->
<div id="modalActivityLog" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Aktivitas Proyek</h3>
            <button onclick="closeModal('modalActivityLog')" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <div class="modal-body">
            <div id="activityLogList" style="max-height:400px; overflow-y:auto; padding-right:10px;">
                <div style="text-align:center; color:#718096; padding:20px;">Loading...</div>
            </div>
        </div>
    </div>
</div>

<script>
// ... (Multi Select JS) ...
function toggleMultiSelect(id) {
    const options = document.querySelector('#' + id + ' .multi-select-options');
    options.classList.toggle('active');
}
function updateMultiSelectLabel(id) {
    const container = document.getElementById(id);
    const checkboxes = container.querySelectorAll('input[type="checkbox"]:checked');
    const trigger = container.querySelector('.multi-select-trigger');
    if (checkboxes.length > 0) {
        trigger.innerText = checkboxes.length + ' Anggota';
        trigger.style.color = '#fff';
    } else {
        trigger.innerText = 'Pilih Anggota...';
        trigger.style.color = '#A0AEC0';
    }
}
window.addEventListener('click', function(e) {
    if (!e.target.closest('.multi-select-container')) {
        document.querySelectorAll('.multi-select-options').forEach(el => el.classList.remove('active'));
    }
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});

function filterMultiSelect(input) {
    const filter = input.value.toLowerCase();
    const options = input.parentElement.querySelectorAll('.multi-select-option');
    
    options.forEach(option => {
        const text = option.innerText.toLowerCase();
        if (text.includes(filter)) {
            option.style.display = 'flex';
        } else {
            option.style.display = 'none';
        }
    });
}

function openModal(id){ document.getElementById(id).classList.add('active'); }
function closeModal(id){ document.getElementById(id).classList.remove('active'); }

function openEditProjectModal(project){
    document.getElementById('editProjectName').value = project.name;
    document.getElementById('editProjectStatus').value = project.status;
    document.getElementById('editProjectDescription').value = project.description || '';
    document.getElementById('editProjectStartDate').value = project.start_date ? project.start_date.substring(0, 10) : '';
    document.getElementById('editProjectEndDate').value = project.end_date ? project.end_date.substring(0, 10) : '';
    
    // Select members in the multi-select dropdown
    let memberIds = project.members.map(m => m.id);
    // FIXED: Use correct ID 'editProjectMembers'
    const container = document.getElementById('editProjectMembers'); 
    const checkboxes = container.querySelectorAll('input[type="checkbox"]');
    
    checkboxes.forEach(cb => {
        cb.checked = memberIds.includes(parseInt(cb.value));
    });
    updateMultiSelectLabel('editProjectMembers');

    let url = "{{ route('projects.update', ':id') }}".replace(':id', project.id);
    document.getElementById('formEditProject').action = url;
    openModal('modalEditProject');
}
function openEditTaskModal(taskId){
    // Reset form
    document.getElementById('editTaskTitle').value = 'Loading...';
    document.getElementById('editTaskDescription').value = '';
    
    fetch(`/tasks/${taskId}`).then(res => {
        if(!res.ok) throw new Error(res.statusText);
        return res.json();
    }).then(task => {
        document.getElementById('editTaskTitle').value = task.title;
        document.getElementById('editTaskDescription').value = task.description || '';
        document.getElementById('editTaskDueDate').value = task.due_date ? task.due_date.substring(0, 10) : '';
        document.getElementById('editTaskStatus').value = task.status;
        
        // New Fields
        document.getElementById('editTaskPriority').value = task.priority || 'medium';
    
        let url = "{{ route('tasks.update', ':id') }}".replace(':id', task.id);
        document.getElementById('formEditTask').action = url;
        openModal('modalEditTask');
    }).catch(err => {
        console.error('Error loading task:', err);
        alert('Failed to load task for editing: ' + err.message);
    });
}

// Timer Logic
let timerInterval;
let timerSeconds = 0;

function openTaskDetailModal(taskId){
    openModal('modalTaskDetail');
    document.getElementById('detailTaskTitle').innerText = 'Loading...';
    document.getElementById('detailTaskDescription').innerText = '...';
    document.getElementById('subtaskList').innerHTML = '';
    document.getElementById('commentList').innerHTML = '';
    document.getElementById('attachmentList').innerHTML = '';

    fetch(`/tasks/${taskId}`).then(res => res.json()).then(task => {
        currentTaskId = task.id;
        currentProjectID = task.project_id; 

        document.getElementById('detailTaskTitle').innerText = task.title; 
        document.getElementById('detailTaskDescription').innerText = task.description || '-';
        
        // Badge & Status
        document.getElementById('detailTaskStatus').innerText = task.status;
        document.getElementById('detailTaskStatus').className = 'badge badge-' + task.status;
        
        // Priority Badge
        document.getElementById('detailTaskPriority').innerText = (task.priority || 'medium');
        document.getElementById('detailTaskPriority').className = 'badge badge-priority-' + (task.priority || 'medium');
        
        document.getElementById('detailTaskDueDate').innerText = task.due_date || '-';
        
        if(task.assignee){
            document.getElementById('assigneeName').innerText = task.assignee.name;
            document.getElementById('assigneeEmail').innerText = task.assignee.email;
            document.getElementById('assigneeAvatar').innerText = task.assignee.name.substring(0,2).toUpperCase();
        } else {
            document.getElementById('assigneeName').innerText = 'Unassigned';
            document.getElementById('assigneeEmail').innerText = '';
            document.getElementById('assigneeAvatar').innerText = '?';
        }
        
        loadComments(currentTaskId); // Uses API
        loadAttachments(currentTaskId); // Uses API
        renderSubtasks(task.subtasks); // Uses API data
    }).catch(err => {
        alert('Failed to load task details');
        closeModal('modalTaskDetail');
    });
}

function renderSubtasks(subtasks){
    const list = document.getElementById('subtaskList');
    list.innerHTML = '';
    if(!subtasks || subtasks.length === 0){
        list.innerText = 'No subtasks';
        return;
    }
    subtasks.forEach(s => {
        const isDone = s.status === 'done';
        const style = isDone ? 'text-decoration:line-through; color:#718096;' : 'color:#fff;';
        list.innerHTML += `<div class="subtask-item">
            <div style="display:flex; align-items:center; gap:10px;">
                <input type="checkbox" ${isDone ? 'checked' : ''} disabled> <!-- Basic display -->
                <span style="${style}">${escapeHtml(s.title)}</span>
            </div>
            <span class="badge badge-${s.priority}">${s.priority}</span>
        </div>`;
    });
}

let currentProjectID = null;
function createSubtask(){
    let title = document.getElementById('newSubtaskTitle').value;
    let priority = document.getElementById('newSubtaskPriority').value;
    
    if(!title) return;
    
    if(!currentProjectID) {
        alert('Task data not fully loaded. Please wait.');
        return;
    }

    fetch(`/projects/${currentProjectID}/tasks`, { 
        method: 'POST',
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Content-Type':'application/json'},
        body: JSON.stringify({
            title: title,
            parent_task_id: currentTaskId,
            priority: priority, 
            project_id: currentProjectID
        })
    }).then(res => {
         if(res.ok){
             document.getElementById('newSubtaskTitle').value = '';
             // Reload subtasks
             fetch(`/tasks/${currentTaskId}`).then(r=>r.json()).then(t=>renderSubtasks(t.subtasks));
         } else {
             alert('Failed to create subtask');
         }
    });
}





// ... COPY PASTE PREVIOUS AJAX FUNCTIONS (loadComments, submitComment, etc) AND ADJUST STYLES IN HTML ...
// For brevity, I'm pasting the essential logic. Ideally, keep these robust.



function loadComments(taskId){
    // ... same logic ...
    fetch(`/tasks/${taskId}/comments`).then(res=>res.json()).then(renderComments);
}
function renderComments(comments){
    const list = document.getElementById('commentList');
    list.innerHTML = '';
    comments.forEach(c => {
         // style adjustment
         list.innerHTML += `<div style="margin-bottom:10px; background:#1C2333; padding:10px; border-radius:6px; border:1px solid #2D3748;">
            <div style="font-weight:bold; color:#ECC94B; font-size:0.8rem;">${c.user.name}</div>
            <div style="color:#E2E8F0;">${escapeHtml(c.content)}</div>
         </div>`;
    });
}
function submitComment(){
    const content = document.getElementById('commentContent').value; 
    if(!content) return;
    fetch(`/tasks/${currentTaskId}/comments`, {
        method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'},
        body:JSON.stringify({content})
    }).then(() => { document.getElementById('commentContent').value=''; loadComments(currentTaskId); });
}

function loadAttachments(taskId){
    fetch(`/tasks/${taskId}/attachments`).then(res=>res.json()).then(files=>{
        const list = document.getElementById('attachmentList');
        list.innerHTML = '';
        files.forEach(f => {
            list.innerHTML += `<div style="display:flex; justify-content:space-between; margin-bottom:5px; background:#1C2333; padding:5px; border-radius:4px;">
                <a href="/attachments/${f.id}/download" target="_blank" style="color:#63B3ED;">${f.filename}</a>
                <span onclick="deleteAttachment(${f.id})" style="color:#FC8181; cursor:pointer;">DEL</span>
            </div>`;
        });
    });
}
function uploadFile(){
    const file = document.getElementById('fileInput').files[0];
    if(!file) return;
    const formData = new FormData(); formData.append('file', file);
    document.getElementById('uploadStatus').innerText = 'Uploading...';
    fetch(`/tasks/${currentTaskId}/attachments`, {
        method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:formData
    }).then(()=>{ 
        document.getElementById('uploadStatus').innerText=''; 
        loadAttachments(currentTaskId); 
    });
}
function deleteAttachment(id){
    if(confirm('Delete?')) fetch(`/attachments/${id}`, {method:'DELETE', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>loadAttachments(currentTaskId));
}
function escapeHtml(text) {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function openActivityLogModal(projectId){
    openModal('modalActivityLog');
    document.getElementById('activityLogList').innerHTML = '<div style="text-align:center; color:#718096; padding:20px;">Loading...</div>';
    
    fetch(`/projects/${projectId}/activities`).then(res=>res.json()).then(logs => {
        const list = document.getElementById('activityLogList');
        list.innerHTML = '';
        if(logs.length === 0){
            list.innerHTML = '<div style="text-align:center; color:#718096; padding:20px;">Belum ada aktivitas.</div>';
            return;
        }
        
        logs.forEach(log => {
            const date = new Date(log.created_at).toLocaleString();
            list.innerHTML += `<div style="border-bottom:1px solid #2D3748; padding:15px 0;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span style="font-weight:bold; color:#ECC94B;">${log.user ? log.user.name : 'System'}</span>
                    <span style="font-size:0.8rem; color:#718096;">${date}</span>
                </div>
                <div style="color:#fff;">${log.description}</div>
                <div style="font-size:0.8rem; color:#A0AEC0; margin-top:3px; text-transform:uppercase; letter-spacing:0.05em;">${log.action}</div>
            </div>`;
        });
    }).catch(err => {
         document.getElementById('activityLogList').innerHTML = '<div style="text-align:center; color:#F56565; padding:20px;">Gagal memuat aktivitas.</div>';
    });
}
    function openManageMembersModal(project) {
        openModal('modalManageMembers');
        const list = document.getElementById('manageMembersList');
        list.innerHTML = '';
        
        project.members.forEach(member => {
            const role = member.pivot.role_in_project || 'member';
            const isCreator = project.created_by == member.id;
            
            let roleSelect = '';
            if(isCreator) {
                roleSelect = '<span class="badge badge-active">Project Owner (PM)</span>';
            } else {
                roleSelect = `
                    <select onchange="changeMemberRole(${project.id}, ${member.id}, this.value)" class="form-select" style="background:#151a23; padding:5px; border:1px solid #2D3748; color:#fff; width:auto;">
                        <option value="member" ${role === 'member' ? 'selected' : ''}>Member</option>
                        <option value="pm" ${role === 'pm' ? 'selected' : ''}>PM</option>
                        <option value="qa" ${role === 'qa' ? 'selected' : ''}>QA</option>
                    </select>
                `;
            }

            let removeBtn = '';
            if(!isCreator) {
                removeBtn = `<button onclick="removeMember(${project.id}, ${member.id})" class="btn-action btn-danger" style="padding:5px 10px; font-size:0.8rem;">Remove</button>`;
            }

            list.innerHTML += `
                <div style="display:flex; justify-content:space-between; align-items:center; background:#1A202C; padding:10px; border-radius:6px; border:1px solid #2D3748;">
                    <div>
                        <div style="font-weight:bold; color:#fff;">${member.name}</div>
                        <div style="font-size:0.8rem; color:#A0AEC0;">${member.email}</div>
                    </div>
                    <div style="display:flex; gap:10px; align-items:center;">
                        ${roleSelect}
                        ${removeBtn}
                    </div>
                </div>
            `;
        });
    }

    function changeMemberRole(projectId, userId, newRole) {
        fetch(`/projects/${projectId}/members/${userId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ role: newRole })
        }).then(res => {
            if(res.ok) {
                // Optional: show toast
            } else {
                alert('Gagal update role');
            }
        });
    }

    function removeMember(projectId, userId) {
        if(!confirm('Hapus anggota ini dari proyek?')) return;
        
        fetch(`/projects/${projectId}/members/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(res => {
            if(res.ok) {
                location.reload(); 
            } else {
                alert('Gagal menghapus anggota');
            }
        });
    }
</script>
@endsection
