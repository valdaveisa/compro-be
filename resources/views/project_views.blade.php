@extends('layouts.app')

@section('content')
<div style="background-color: #0b0e11; min-height: 100vh; color: #fff; padding: 20px;">
    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 700; margin: 0;">{{ $project->name }}</h1>
            <div style="color: #A0AEC0; font-size: 0.9rem; margin-top: 5px;">
                Status: <span class="badge badge-{{ $project->status }}">{{ ucfirst($project->status) }}</span> | 
                Deadline: {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '-' }}
            </div>
        </div>
    </div>

    <!-- Main Navigation (Context Switch) -->
    <div style="border-bottom: 2px solid #2D3748; display: flex; gap: 20px; margin-bottom: 20px; margin-top: 20px;">
        <a href="{{ route('dashboard', ['project_id' => $project->id]) }}" style="padding: 10px 0; color: #A0AEC0; text-decoration: none; font-weight: 600; border-bottom: 2px solid transparent; transition: color 0.2s;">
            Overview
        </a>
        <div style="padding: 10px 0; border-bottom: 2px solid #ECC94B; color: #ECC94B; font-weight: 600; cursor: default;">
            Visualisasi (Gantt, Kanban, Calendar)
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab-item active" onclick="switchTab('gantt')">Timeline (Gantt)</div>
        <div class="tab-item" onclick="switchTab('kanban')">Kanban Board</div>
        <div class="tab-item" onclick="switchTab('calendar')">Calendar View</div>
    </div>

    <!-- Gantt View -->
    <div id="view-gantt" class="view-section active">
        <div class="card" style="padding: 20px;">
            <h3 style="margin-top:0; margin-bottom:20px;">Gantt Chart (Simulasi)</h3>
            <div style="overflow-x: auto;">
                <div style="min-width: 800px;">
                    @php
                        $startDate = $project->start_date ? \Carbon\Carbon::parse($project->start_date) : now();
                        $endDate = $project->end_date ? \Carbon\Carbon::parse($project->end_date) : now()->addDays(30);
                        $totalDays = $startDate->diffInDays($endDate) ?: 1;
                    @endphp

                    <!-- Gantt Header -->
                    <div style="display: grid; grid-template-columns: 200px repeat(14, 1fr); gap: 1px; background: #2D3748; padding: 10px; border-radius: 6px;">
                        <div style="font-weight:bold; color:#ECC94B;">Tugas / Timeline</div>
                        @for($i=0; $i<14; $i++)
                            @php
                                $headerDate = $startDate->copy()->addDays(($totalDays / 14) * $i);
                            @endphp
                            <div style="text-align:center; font-size:0.7rem; color:#A0AEC0;">{{ $headerDate->format('d M') }}</div>
                        @endfor
                    </div>
                    
                    <!-- Gantt Rows -->
                    @foreach($project->tasks as $task)
                        @php
                            // Normalize dates to start of day for consistent full-day blocks
                            $taskStart = $task->created_at ? \Carbon\Carbon::parse($task->created_at)->startOfDay() : $startDate->copy()->startOfDay();
                            
                            // If deadline exists, include that full day (addDay->startOfDay). 
                            // If no deadline, extend to project end date (to make it visible/long as requested).
                            $taskEnd = $task->due_date 
                                ? \Carbon\Carbon::parse($task->due_date)->addDay()->startOfDay() 
                                : $endDate->copy()->addDay()->startOfDay();
                            
                            // Clamp start to project start (visual clipping)
                            if ($taskStart->lt($startDate)) $taskStart = $startDate->copy()->startOfDay();
                            
                            // Clamp end to project end
                            if ($taskEnd->gt($endDate)) $taskEnd = $endDate->copy()->addDay()->startOfDay();
                            
                            // Calculate total project duration in days (float to handle partials if any, but we normalized)
                            $projectDuration = $startDate->diffInDays($endDate->copy()->addDay()->startOfDay());
                            if ($projectDuration < 1) $projectDuration = 1;

                            // Calculate offsets
                            $offsetDays = $startDate->diffInDays($taskStart);
                            $durationDays = $taskStart->diffInDays($taskEnd);
                            
                            // Safety minimum
                            if ($durationDays < 1) $durationDays = 1;

                            $leftPercent = ($offsetDays / $projectDuration) * 100;
                            $widthPercent = ($durationDays / $projectDuration) * 100;
                            
                            // Final clamp
                            if (($leftPercent + $widthPercent) > 100) $widthPercent = 100 - $leftPercent;
                        @endphp
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-top: 10px; align-items: center;">
                        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-right: 10px;">{{ $task->title }}</div>
                        <div style="position: relative; height: 30px; background: #1A202C; border-radius: 4px; border:1px solid #2D3748;">
                            @php
                                $color = $task->status == 'done' ? '#48BB78' : ($task->status == 'in_progress' ? '#4299E1' : '#ED8936');
                            @endphp
                            <div style="position: absolute; left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; height: 100%; background: {{ $color }}; border-radius: 4px; display:flex; align-items:center; padding-left:10px; font-size:0.75rem; white-space:nowrap; overflow:hidden;">
                                {{ $task->title }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($project->tasks->count() == 0)
                        <div style="padding:20px; text-align:center; color:#718096;">Belum ada tugas.</div>
                    @endif
                </div>
            </div>
            <div style="margin-top:20px; font-size:0.8rem; color:#718096;"></div>
        </div>
    </div>

    <!-- Kanban View -->
    <div id="view-kanban" class="view-section">
        <div class="row">
            <!-- Todo Column -->
            <div class="col-4">
                <div class="kanban-column">
                    <div class="kanban-header kanban-todo">To Do</div>
                    <div class="kanban-body">
                        @foreach($project->tasks->where('status', 'todo') as $task)
                        <div class="kanban-card">
                            <div class="kanban-card-title">{{ $task->title }}</div>
                            <div class="kanban-card-meta">
                                <span class="badge-sm badge-{{ $task->priority ?? 'medium' }}">{{ ucfirst($task->priority ?? 'Medium') }}</span>
                                <span style="font-size:0.75rem; color:#A0AEC0;">{{ $task->assignee ? $task->assignee->name : 'Unassigned' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
             <!-- In Progress Column -->
             <div class="col-4">
                <div class="kanban-column">
                    <div class="kanban-header kanban-in_progress">In Progress</div>
                    <div class="kanban-body">
                        @foreach($project->tasks->where('status', 'in_progress') as $task)
                        <div class="kanban-card">
                            <div class="kanban-card-title">{{ $task->title }}</div>
                            <div class="kanban-card-meta">
                                <span class="badge-sm badge-{{ $task->priority ?? 'medium' }}">{{ ucfirst($task->priority ?? 'Medium') }}</span>
                                <span style="font-size:0.75rem; color:#A0AEC0;">{{ $task->assignee ? $task->assignee->name : 'Unassigned' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
             <!-- Done Column -->
             <div class="col-4">
                <div class="kanban-column">
                    <div class="kanban-header kanban-done">Done</div>
                    <div class="kanban-body">
                        @foreach($project->tasks->where('status', 'done') as $task)
                        <div class="kanban-card">
                            <div class="kanban-card-title">{{ $task->title }}</div>
                            <div class="kanban-card-meta">
                                <span class="badge-sm badge-{{ $task->priority ?? 'medium' }}">{{ ucfirst($task->priority ?? 'Medium') }}</span>
                                <span style="font-size:0.75rem; color:#A0AEC0;">{{ $task->assignee ? $task->assignee->name : 'Unassigned' }}</span>
                            </div>
                        </div>
                        @endforeach
                         @foreach($project->tasks->where('status', 'review') as $task)
                        <div class="kanban-card">
                            <div class="kanban-card-title">{{ $task->title }} (Review)</div>
                            <div class="kanban-card-meta">
                                <span class="badge-sm badge-{{ $task->priority ?? 'medium' }}">{{ ucfirst($task->priority ?? 'Medium') }}</span>
                                <span style="font-size:0.75rem; color:#A0AEC0;">{{ $task->assignee ? $task->assignee->name : 'Unassigned' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar View -->
    <div id="view-calendar" class="view-section">
        <div class="card" style="padding: 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <button id="btnPrevMonth" class="btn-action" style="padding: 5px 15px;">&laquo; Prev</button>
                <h3 id="calendarTitle" style="margin:0; color:#F6E05E;">Januari 2025</h3>
                <button id="btnNextMonth" class="btn-action" style="padding: 5px 15px;">Next &raquo;</button>
            </div>
            
            <div id="calendarGrid" class="calendar-grid">
                <!-- Grid populated by JS -->
            </div>
        </div>
    </div>

</div>

<style>
    /* Reuse variables and common styles from dashboard */
     :root {
        --primary: #ECC94B;
        --bg-dark: #0b0e11;
        --bg-card: #151a23;
        --text-color: #fff;
    }
    
    /* ... (Rest of styles same as before) ... */
    
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
    .badge-active, .badge-in_progress { background: #3182CE; color: #EBF8FF; }
    .badge-planned, .badge-todo { background: #ED8936; color: #FFFAF0; }
    .badge-done { background: #48BB78; color: #F0FFF4; }
    .badge-on_hold, .badge-review { background: #A0AEC0; color: #F7FAFC; }
    .badge-high { background: #FC8181; color: #FFF5F5; }
    .badge-medium { background: #F6E05E; color: #FFFFF0; color:#744210; }
    .badge-low { background: #68D391; color: #F0FFF4; }
    
    .btn-action {
        background: #1A202C; color: #A0AEC0; border: 1px solid #2D3748; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; transition: all 0.2s;
    }
    .btn-action:hover { background: #2D3748; color: #fff; border-color: #4A5568; }

    .card { background: var(--bg-card); border: 1px solid #2D3748; border-radius: 12px; }
    
    .tabs { display: flex; gap: 20px; border-bottom: 2px solid #2D3748; margin-bottom: 30px; }
    .tab-item {
        padding: 10px 0;
        cursor: pointer;
        color: #A0AEC0;
        font-weight: 600;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: color 0.2s;
    }
    .tab-item:hover { color: #fff; }
    .tab-item.active { color: var(--primary); border-bottom-color: var(--primary); }

    .view-section { display: none; }
    .view-section.active { display: block; animation: fadeIn 0.3s; }

    /* Kanban Styles */
    .row { display: flex; margin: 0 -10px; }
    .col-4 { width: 33.33%; padding: 0 10px; }
    .kanban-column { background: #1A202C; border-radius: 8px; border: 1px solid #2D3748; overflow: hidden; height: 100%; min-height: 400px; }
    .kanban-header { padding: 15px; font-weight: 700; border-bottom: 1px solid #2D3748; }
    .kanban-todo { border-top: 3px solid #ED8936; }
    .kanban-in_progress { border-top: 3px solid #3182CE; }
    .kanban-done { border-top: 3px solid #48BB78; }
    .kanban-body { padding: 15px; }
    .kanban-card { background: #2D3748; padding: 12px; border-radius: 6px; margin-bottom: 10px; cursor: grab; transition: transform 0.1s; }
    .kanban-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.2); }
    .kanban-card-title { font-weight: 600; margin-bottom: 8px; font-size: 0.9rem; }
    .kanban-card-meta { display: flex; justify-content: space-between; align-items: center; }
    .badge-sm { padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; }

    /* Calendar Styles */
    .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); border: 1px solid #2D3748; border-radius: 8px; overflow: hidden; }
    .cal-header { background: #1A202C; padding: 10px; text-align: center; font-weight: 600; border-right: 1px solid #2D3748; border-bottom: 1px solid #2D3748; }
    .cal-header:last-child { border-right: none; }
    .cal-cell { background: #151a23; padding: 10px; border-right: 1px solid #2D3748; border-bottom: 1px solid #2D3748; min-height: 100px; position: relative; }
    .cal-cell:nth-child(7n) { border-right: none; }
    .cal-cell.dim { background: #0b0e11; color: #4A5568; }
    .cal-date { font-weight: 700; margin-bottom: 5px; font-size: 0.9rem; }
    .cal-task { font-size: 0.7rem; padding: 2px 4px; border-radius: 2px; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); }}

</style>
<script>
    function switchTab(tabName) {
        // Tabs
        document.querySelectorAll('.tab-item').forEach(el => el.classList.remove('active'));
        event.target.classList.add('active');
        
        // Views
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
        document.getElementById('view-' + tabName).classList.add('active');
    }

    // Calendar Javascript
    const tasks = @json($project->tasks);
    let currentDate = new Date();

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        // Update Title
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        document.getElementById('calendarTitle').innerText = `${monthNames[month]} ${year}`;

        const grid = document.getElementById('calendarGrid');
        grid.innerHTML = '';

        // Add Headers
        const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        days.forEach(day => {
            const div = document.createElement('div');
            div.className = 'cal-header';
            div.innerText = day;
            grid.appendChild(div);
        });

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const prevMonthDays = new Date(year, month, 0).getDate();

        // Prev Month Padding
        for (let i = firstDay - 1; i >= 0; i--) {
            const dayNum = prevMonthDays - i;
            const cell = document.createElement('div');
            cell.className = 'cal-cell dim';
            cell.innerHTML = `<div class="cal-date">${dayNum}</div>`;
            grid.appendChild(cell);
        }

        // Current Month Days
        for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement('div');
            cell.className = 'cal-cell';
            
            // Filter tasks for this day
            const cellDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayTasks = tasks.filter(t => {
                if (!t.due_date) return false;
                // Handle potential timestamp format (YYYY-MM-DD HH:MM:SS) by taking first 10 chars
                return t.due_date.substring(0, 10) === cellDateStr;
            });

            let taskHtml = '';
            dayTasks.forEach(t => {
                taskHtml += `<div class="cal-task badge-${t.status}">${t.title}</div>`;
            });

            cell.innerHTML = `<div class="cal-date">${day}</div>${taskHtml}`;
            grid.appendChild(cell);
        }
        
        // Next Month Padding (to fill 35 or 42 grid cells)
        const totalCells = firstDay + daysInMonth;
        const nextMonthPadding = (totalCells <= 35) ? 35 - totalCells : 42 - totalCells;
        
        for(let i = 1; i <= nextMonthPadding; i++) {
             const cell = document.createElement('div');
            cell.className = 'cal-cell dim';
            cell.innerHTML = `<div class="cal-date">${i}</div>`;
            grid.appendChild(cell);
        }
    }

    document.getElementById('btnPrevMonth').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    document.getElementById('btnNextMonth').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    // Initial Render
    document.addEventListener('DOMContentLoaded', renderCalendar);
</script>
@endsection
