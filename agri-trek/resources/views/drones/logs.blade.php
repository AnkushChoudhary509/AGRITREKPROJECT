@extends('layouts.app')
@section('title', $drone->name . ' – Logs')
@section('page-title', 'Drone Telemetry Logs')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('drones.show', $drone) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">{{ $drone->name }} – Flight Logs</h5>
    <span class="badge bg-{{ $drone->status === 'active' ? 'success' : 'warning' }} ms-1">{{ ucfirst($drone->status) }}</span>
</div>

<div class="card">
    <div class="card-header bg-dark text-white">
        <i class="bi bi-clock-history me-2"></i>Telemetry Log History ({{ $logs->total() }} records)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Timestamp</th><th>Latitude</th><th>Longitude</th>
                        <th>Speed (km/h)</th><th>Altitude (m)</th><th>Direction (°)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="text-muted small">{{ $logs->firstItem() + $loop->index }}</td>
                        <td class="small">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                        <td class="small font-monospace">{{ number_format($log->latitude, 7) }}</td>
                        <td class="small font-monospace">{{ number_format($log->longitude, 7) }}</td>
                        <td><span class="badge bg-primary">{{ $log->speed }}</span></td>
                        <td><span class="badge bg-warning text-dark">{{ $log->altitude }}</span></td>
                        <td class="small text-muted">{{ $log->direction }}°</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>No telemetry logs yet
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
