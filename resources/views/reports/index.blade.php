@extends('layouts.app')

@section('content')
<div class="container-custom">
    <div class="page-header">
        <h1 class="page-title">Dashboard Pelaporan Proyek</h1>
        
        <div style="display:flex; gap:10px; align-items:center;">
            <form id="filterForm" action="{{ route('reports.index') }}" method="GET">
                <select name="project_id" onchange="document.getElementById('filterForm').submit()" class="form-select">
                    <option value="">Semua Proyek</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ (isset($selectedProject) && $selectedProject->id == $p->id) ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('reports.export', ['project_id' => request('project_id')]) }}" class="btn-submit" style="font-size:0.9rem; text-decoration:none; display:inline-flex; align-items:center;">Export Laporan</a>
            <a href="{{ route('dashboard') }}" class="btn-action">Kembali</a>
        </div>
    </div>

    <!-- Main Dashboard Container -->
    <div class="dashboard-container">
        <h2 style="font-size:1.2rem; font-weight:700; color:#fff; margin-bottom:20px;">Ringkasan Kinerja Proyek</h2>

        <!-- Metrics Row -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Total Proyek</div>
                <div class="metric-value">{{ $totalProjects }}</div>
            </div>
            <div class="metric-card" style="border-left: 3px solid #48BB78;">
                <div class="metric-label" style="color:#68D391;">
                    {{ isset($selectedProject) ? 'TUGAS SELESAI' : 'PROYEK SELESAI' }}
                </div>
                <div class="metric-value">
                    {{ $finishedCount }} 
                    @if(isset($selectedProject))
                        <span style="font-size:1rem; color:#A0AEC0; font-weight:normal;">({{ $totalProjects > 0 ? round(($finishedCount/($selectedProject->tasks->count() > 0 ? $selectedProject->tasks->count() : 1))*100) : 0 }}%)</span>
                    @else
                        <span style="font-size:1rem; color:#A0AEC0; font-weight:normal;">({{ $totalProjects > 0 ? round(($finishedCount/$totalProjects)*100) : 0 }}%)</span>
                    @endif
                </div>
            </div>
            <div class="metric-card" style="border-left: 3px solid #F56565;">
                <div class="metric-label" style="color:#FC8181;">
                     {{ isset($selectedProject) ? 'TUGAS BELUM SELESAI' : 'PROYEK BELUM SELESAI' }}
                </div>
                <div class="metric-value">{{ $unfinishedCount }}</div>
            </div>
            <div class="metric-card" style="border-left: 3px solid #F6E05E;">
                <div class="metric-label" style="color:#F6E05E;">
                    {{ isset($selectedProject) ? 'TASK COMPLETION RATE' : 'PROJECT COMPLETION RATE' }}
                </div>
                <div class="metric-value">
                    @if(isset($selectedProject))
                        {{ $burndownRate }}%
                    @else
                        {{ $totalProjects > 0 ? round(($finishedCount / $totalProjects) * 100) : 0 }}%
                    @endif
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-grid">
            <!-- Workload Chart -->
            <div class="chart-panel workload">
                <h3 class="chart-title">Workload Anggota Tim (Tugas Aktif)</h3>
                <div class="chart-container">
                    <canvas id="workloadChart"></canvas>
                </div>
            </div>

            <!-- Status Pie Chart with Custom Legend -->
            <div class="chart-panel status">
                <h3 class="chart-title">{{ $chart2Title }}</h3>
                <div class="pie-chart-wrapper">
                    <!-- Pie -->
                    <div class="pie-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <!-- Custom Legend -->
                    <div class="legend-container">
                        @php
                            $colorMap = [
                                'todo' => '#A0AEC0',        // Gray
                                'planned' => '#A0AEC0',     
                                'in_progress' => '#4299E1', // Blue
                                'active' => '#4299E1',
                                'review' => '#ED8936',      // Orange
                                'on_hold' => '#F56565',     // Red
                                'done' => '#48BB78'         // Green
                            ];
                        @endphp
                        @foreach($statusLabels as $index => $label)
                            <div class="legend-item">
                                <span class="legend-dot" style="background: {{ $colorMap[$label] ?? '#ccc' }};"></span> 
                                <div>
                                    {{ ucfirst(str_replace('_', ' ', $label)) }} <br>
                                    <span class="legend-sub">({{ $statusValues[$index] ?? 0 }} {{ isset($selectedProject) ? 'Tugas' : 'Proyek' }})</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Theme Colors
    const colors = {
        primary: '#ECC94B',
        blue: '#4299E1',
        green: '#48BB78',
        red: '#F56565',
        gray: '#A0AEC0',
        grid: '#2D3748'
    };

    // Workload Chart
    const ctxWorkload = document.getElementById('workloadChart').getContext('2d');
    new Chart(ctxWorkload, {
        type: 'bar',
        data: {
            labels: @json($workloadLabels),
            datasets: [{
                label: 'Jumlah Tugas Aktif',
                data: @json($workloadValues),
                backgroundColor: ['#F56565', '#4299E1', '#48BB78', '#4299E1', '#ECC94B'], 
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: colors.grid },
                    ticks: { color: colors.gray, stepSize: 1 }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: colors.gray }
                }
            },
            plugins: {
                legend: { display: false }
            },
            maxBarThickness: 40
        }
    });

    @php
        $chartLabels = array_map(fn($l) => ucfirst(str_replace('_', ' ', $l)), $statusLabels);
        $chartColors = array_map(fn($l) => $colorMap[$l] ?? '#ccc', $statusLabels);
    @endphp

    // Status Chart
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',

        data: {
            labels: @json($chartLabels),
            datasets: [{
                data: @json($statusValues),
                backgroundColor: @json($chartColors), 
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false } 
            },
            layout: {
                padding: 10
            },
            cutout: '65%'
        }
    });
</script>

<style>
    /* Global Styles */
    .container-custom { width: 95%; max-width: 1300px; margin: 0 auto; padding: 40px 20px; box-sizing: border-box; } 
    
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .page-title { font-size: 1.6rem; font-weight: 800; color: #fff; margin: 0; }

    .btn-action {
        background: #1A202C; color: #A0AEC0; border: 1px solid #2D3748;
        padding: 8px 16px; border-radius: 6px; cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center; font-weight: 600; font-size: 0.85rem;
        transition: all 0.2s;
    }
    .btn-action:hover { background: #2D3748; color: #fff; border-color: #4A5568; }

    .btn-submit {
        background: #ECC94B; color: #1A202C; padding: 8px 16px; border:none; border-radius:6px; font-weight:bold; cursor:pointer;
        transition: transform 0.2s;
    }
    .btn-submit:hover { transform: translateY(-1px); }
    
    .form-select {
        background: #151a23; color: #fff; border: 1px solid #2D3748;
        padding: 8px 12px; border-radius: 6px; outline: none; cursor: pointer; font-size: 0.9rem;
    }

    /* Main Dashboard Card */
    .dashboard-container {
        background: #151a23;
        border: 1px solid #232a3b;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }

    /* Metrics Grid */
    .metrics-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .metric-card {
        flex: 1;
        min-width: 200px;
        background: #1A202C;
        border: 1px solid #2D3748;
        border-radius: 8px;
        padding: 20px;
    }
    
    .metric-label { font-size: 0.85rem; color: #A0AEC0; margin-bottom: 10px; font-weight: 600; text-transform: uppercase; }
    .metric-value { font-size: 2rem; font-weight: 800; color: #fff; line-height: 1; margin: 0; }

    /* Charts Layout */
    .charts-grid {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
    }
    
    .chart-panel {
        background: #1A202C;
        border: 1px solid #2D3748;
        border-radius: 8px;
        padding: 20px;
        min-width: 300px;
        box-sizing: border-box;
        overflow: hidden; 
    }

    .chart-panel.workload { flex: 2; }
    .chart-panel.status { flex: 1.2; } /* Slightly larger to fit legend */

    .chart-title { font-size: 1rem; color: #fff; font-weight: 700; margin-bottom: 20px; margin-top: 0; }
    
    .chart-container {
        position: relative; 
        height: 300px; 
        width: 100%; 
        overflow: hidden;
    }
    
    /* Pie Chart Specifics */
    .pie-chart-wrapper {
        display: flex;
        align-items: center;
        height: 300px;
        gap: 20px;
    }
    .pie-container {
        flex: 1;
        position: relative;
        height: 100%;
        min-width: 150px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .legend-container {
        flex: 0 0 140px; /* Fixed width for legend */
    }

    /* Custom Legend */
    .legend-item { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; color: #fff; font-size: 0.9rem; font-weight: 500; }
    .legend-dot { width: 10px; height: 10px; border-radius: 2px; margin-top: 5px; flex-shrink: 0; }
    .legend-sub { font-size: 0.75rem; color: #718096; display: block; margin-top: 2px; }

    @media (max-width: 1024px) {
        .charts-grid { flex-direction: column; }
        .chart-panel { width: 100%; flex: none; }
        .pie-chart-wrapper { justify-content: center; }
    }
    
    @media (max-width: 480px) {
        .pie-chart-wrapper { flex-direction: column; height: auto; padding-bottom: 20px; }
        .pie-container { height: 250px; width: 100%; }
        .legend-container { width: 100%; margin-top: 20px; }
    }
</style>
@endsection
