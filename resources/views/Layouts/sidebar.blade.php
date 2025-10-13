<aside class="sidebar bg-white shadow-sm vh-100 position-fixed">
    <div class="sidebar-header text-center py-3 border-bottom">
        <h5 class="fw-bold text-primary mb-0">SMP Negeri 1 Sedati</h5>
        <small class="text-muted">Sistem Presensi</small>
    </div>

    <ul class="menu list-unstyled mt-3 px-2">

        {{-- Dashboard --}}
        <li class="menu-item mb-1">
            <a href="{{ route('dashboard') }}" 
               class="menu-link d-flex align-items-center px-3 py-2 rounded-3 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-3 fs-5"></i>
                <span class="fw-medium">Dashboard</span>
            </a>
        </li>

        {{-- Manajemen Presensi --}}
        <li class="menu-item mb-1">
            <button class="menu-link d-flex align-items-center justify-content-between px-3 py-2 rounded-3 w-100 {{ request()->is('absensi*') ? 'active' : '' }}"
                    data-bs-toggle="collapse" data-bs-target="#menuPresensi" aria-expanded="{{ request()->is('absensi*') ? 'true' : 'false' }}">
                <div class="d-flex align-items-center">
                    <i class="bi bi-calendar-check me-3 fs-5"></i>
                    <span class="fw-medium">Manajemen Presensi</span>
                </div>
                <i class="bi bi-caret-down-fill small"></i>
            </button>

            <ul class="collapse {{ request()->is('absensi*') ? 'show' : '' }}" id="menuPresensi">

                {{-- Input Manual --}}
                <li>
                    <a href="{{ route('absensi.input') }}" 
                       class="submenu-link {{ request()->routeIs('absensi.input') ? 'active' : '' }}">
                       Input Manual
                    </a>
                </li>

                {{-- Edit Data --}}
                <li>
                    <a href="{{ route('absensi.edit') }}" 
                       class="submenu-link {{ request()->routeIs('absensi.edit') ? 'active' : '' }}">
                       Edit Data
                    </a>
                </li>

                {{-- Log --}}
                <li>
                    <a href="{{ route('absensi.log') }}" 
                       class="submenu-link {{ request()->routeIs('absensi.log') ? 'active' : '' }}">
                       Log
                    </a>
                </li>
            </ul>
        </li>

        {{-- Manajemen Data Master --}}
        <li class="menu-item mb-1">
            <button class="menu-link d-flex align-items-center justify-content-between px-3 py-2 rounded-3 w-100 {{ request()->is('siswa*') || request()->is('kelas*') || request()->is('kartu*') ? 'active' : '' }}"
                    data-bs-toggle="collapse" data-bs-target="#menuMaster" aria-expanded="{{ request()->is('siswa*') || request()->is('kelas*') || request()->is('kartu*') ? 'true' : 'false' }}">
                <div class="d-flex align-items-center">
                    <i class="bi bi-people-fill me-3 fs-5"></i>
                    <span class="fw-medium">Data Master</span>
                </div>
                <i class="bi bi-caret-down-fill small"></i>
            </button>

            <ul class="collapse {{ request()->is('siswa*') || request()->is('kelas*') || request()->is('kartu*') ? 'show' : '' }}" id="menuMaster">
                <li><a href="{{ route('siswa.index') }}" class="submenu-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}">Siswa</a></li>
                <li><a href="{{ route('kelas.index') }}" class="submenu-link {{ request()->routeIs('kelas.*') ? 'active' : '' }}">Kelas</a></li>
                <li><a href="{{ route('kartu.index') }}" class="submenu-link {{ request()->routeIs('kartu.*') ? 'active' : '' }}">Kartu RFID</a></li>
            </ul>
        </li>

        {{-- Laporan --}}
        <li class="menu-item mb-1">
            <a href="{{ route('laporan.index') }}" 
               class="menu-link d-flex align-items-center px-3 py-2 rounded-3 {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text me-3 fs-5"></i>
                <span class="fw-medium">Laporan</span>
            </a>
        </li>

        {{-- Pengaturan --}}
        <li class="menu-item mt-2">
            <a href="{{ route('pengaturan.index') }}" 
               class="menu-link d-flex align-items-center px-3 py-2 rounded-3 {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}">
                <i class="bi bi-gear-fill me-3 fs-5"></i>
                <span class="fw-medium">Pengaturan</span>
            </a>
        </li>
    </ul>
</aside>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    .sidebar {
        width: 250px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        background-color: #fff;
        border-right: 1px solid #eee;
    }

    .menu-link {
        color: #333;
        text-decoration: none;
        transition: all 0.25s ease;
    }

    .menu-link:hover {
        background-color: #f4f6ff;
        color: #0d6efd;
    }

    .menu-link.active {
        background: linear-gradient(135deg, #0d6efd, #004ecb);
        color: #fff !important;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(0, 85, 255, 0.2);
    }

    .submenu-link {
        display: block;
        padding: 8px 20px 8px 55px;
        text-decoration: none;
        color: #666;
        font-size: 13.5px;
        transition: all 0.2s ease;
    }

    .submenu-link:hover {
        color: #0d6efd;
        background-color: #f8f9fa;
        border-radius: 8px;
    }

    .submenu-link.active {
        color: #0d6efd;
        font-weight: 600;
    }

    .menu-item button {
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }

    .sidebar-header h5 {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
    }

    .fw-medium {
        font-weight: 500;
    }
</style>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
@endpush
