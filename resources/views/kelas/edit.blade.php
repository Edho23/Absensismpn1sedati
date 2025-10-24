{{-- resources/views/kelas/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Kelas')

@section('content')
<div class="container-fluid px-4 py-3">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary mb-0">✏️ Edit Data Kelas</h3>
    <a href="{{ route('kelas.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
      <form action="{{ route('kelas.update', $kelas->id) }}" method="POST" class="row g-3">
        @csrf
        @method('PUT')

        <div class="col-md-6">
          <label class="form-label fw-semibold">Nama Kelas</label>
          <input type="text" name="nama_kelas" class="form-control rounded-pill"
                 value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Wali Kelas</label>
          <input type="text" name="wali_kelas" class="form-control rounded-pill"
                 value="{{ old('wali_kelas', $kelas->wali_kelas) }}" required>
        </div>

        <div class="col-12 text-end">
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-save"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
