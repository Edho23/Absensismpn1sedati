<aside class="sidebar bg-white shadow-sm">
    <div class="sidebar-header text-center py-3 border-bottom">
        <h5 class="fw-bold text-primary mb-0">SMP Negeri 1 Sedati</h5>
        <small class="text-muted">Sistem Presensi</small>
    </div>

    <ul class="menu list-unstyled mt-3 px-2">
        {{-- Dashboard --}}
        <li class="menu-item mb-1">
            <a href="{{ route('dashboard') }}"
               class="menu-link d-flex align-items-center px-3 py-2 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-3 fs-5"></i>
                <span class="fw-medium">Dashboard</span>
            </a>
        </li>

        {{-- Manajemen Presensi --}}
        <li class="menu-item mb-1">
            <button class="menu-link d-flex align-items-center justify-content-between px-3 py-2 w-100 {{ request()->is('absensi*') ? 'active' : '' }}"
                    data-bs-toggle="collapse" data-bs-target="#menuPresensi"
                    aria-expanded="{{ request()->is('absensi*') ? 'true' : 'false' }}">
                <div class="d-flex align-items-center">
                    <i class="bi bi-calendar-check me-3 fs-5"></i>
                    <span class="fw-medium">Manajemen Presensi</span>
                </div>
                <i class="bi bi-caret-down-fill small"></i>
            </button>

            <ul class="collapse {{ request()->is('absensi*') ? 'show' : '' }} submenu" id="menuPresensi">
                <li>
                    <a href="{{ route('absensi.input') }}"
                       class="submenu-link {{ request()->routeIs('absensi.input') ? 'active' : '' }}">
                        Input Manual
                    </a>
                </li>
                <li>
                    <a href="{{ route('absensi.edit') }}"
                       class="submenu-link {{ request()->routeIs('absensi.edit') ? 'active' : '' }}">
                        Edit Data
                    </a>
                </li>
                <li>
                    <a href="{{ route('absensi.log') }}"
                       class="submenu-link {{ request()->routeIs('absensi.log') ? 'active' : '' }}">
                        Log
                    </a>
                </li>
            </ul>
        </li>

        {{-- Data Master --}}
        <li class="menu-item mb-1">
            <button class="menu-link d-flex align-items-center justify-content-between px-3 py-2 w-100 {{ request()->is('siswa*') || request()->is('kelas*') || request()->is('kartu*') ? 'active' : '' }}"
                    data-bs-toggle="collapse" data-bs-target="#menuMaster"
                    aria-expanded="{{ request()->is('siswa*') || request()->is('kelas*') || request()->is('kartu*') ? 'true' : 'false' }}">
                <div class="d-flex align-items-center">
                    <i class="bi bi-people-fill me-3 fs-5"></i>
                    <span class="fw-medium">Data Master</span>
                </div>
                <i class="bi bi-caret-down-fill small"></i>
            </button>
            <ul class="collapse {{ request()->is('siswa*') || request()->is('kelas*') || request()->is('kartu*') ? 'show' : '' }} submenu" id="menuMaster">
                <li><a href="{{ route('siswa.index') }}" class="submenu-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}">Siswa</a></li>
                <li><a href="{{ route('kelas.index') }}" class="submenu-link {{ request()->routeIs('kelas.*') ? 'active' : '' }}">Kelas</a></li>
                <li><a href="{{ route('kartu.index') }}" class="submenu-link {{ request()->routeIs('kartu.*') ? 'active' : '' }}">Kartu RFID</a></li>
            </ul>
        </li>

        {{-- Laporan --}}
        <li class="menu-item mb-1">
            <a href="{{ route('laporan.index') }}"
               class="menu-link d-flex align-items-center px-3 py-2 {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text me-3 fs-5"></i>
                <span class="fw-medium">Laporan</span>
            </a>
        </li>

        {{-- Pengaturan --}}
        <li class="menu-item mt-2">
            <a href="{{ route('pengaturan.index') }}"
               class="menu-link d-flex align-items-center px-3 py-2 {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}">
                <i class="bi bi-gear-fill me-3 fs-5"></i>
                <span class="fw-medium">Pengaturan</span>
            </a>
        </li>
    </ul>
</aside>
