@extends('layouts.app')
@section('title','Pengaturan Sistem')

@section('content')
<div class="container-fluid px-4 py-3">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary mb-0"><i class="bi bi-gear me-2"></i>Pengaturan Sistem</h3>
  </div>

  @if(session('ok'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('ok') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
      <form method="POST" action="{{ route('pengaturan.update') }}" class="row g-3">
        @csrf

        <div class="col-md-3">
          <label class="form-label fw-semibold">Jam Masuk (Sen–Kam & Sab)</label>
          <input type="time" name="jam_masuk_weekday" value="{{ old('jam_masuk_weekday', $jamMasukWeekday) }}"
                 class="form-control" required>
          @error('jam_masuk_weekday') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Grace (menit)</label>
          <input type="number" name="grace_minutes" min="0" max="120"
                 value="{{ old('grace_minutes', $graceMinutes) }}" class="form-control" required>
          @error('grace_minutes') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Auto Pulang (Sen–Kam & Sab)</label>
          <input type="time" name="auto_pulang_weekday" value="{{ old('auto_pulang_weekday', $autoPulangWeekday) }}"
                 class="form-control" required>
          @error('auto_pulang_weekday') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Auto Pulang (Jumat)</label>
          <input type="time" name="auto_pulang_friday" value="{{ old('auto_pulang_friday', $autoPulangFriday) }}"
                 class="form-control" required>
          @error('auto_pulang_friday') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="col-12 text-end">
          <button class="btn btn-primary rounded-pill px-4"><i class="bi bi-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
