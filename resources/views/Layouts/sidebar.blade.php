<aside class="sidebar">
  <ul class="menu">
    <li>
      <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
         href="{{ route('dashboard') }}">
        <span class="menu-ico">📈</span>
        <span>Dashboard</span>
      </a>
    </li>

    {{-- DROPDOWN: Manajemen Absensi --}}
    <li class="menu-dropdown {{ request()->is('absensi*') ? 'open' : '' }}">
      <button class="menu-link menu-toggle">
        <span class="menu-ico">📚</span>
        <span>Manajemen Absensi</span>
        <span class="menu-caret">▾</span>
      </button>
      <ul class="submenu">
        <li><a class="submenu-link {{ request()->routeIs('absensi.index') ? 'active' : '' }}"
               href="{{ route('absensi.index') }}">Input manual</a></li>
        <li><a class="submenu-link"
               href="{{ route('absensi.index') }}?mode=edit">Edit data</a></li>
        <li><a class="submenu-link {{ request()->routeIs('absensi.log') ? 'active' : '' }}"
               href="{{ route('absensi.log') }}">Log</a></li>

      </ul>
    </li>

    {{-- DROPDOWN: Manajemen Data Master --}}
    <li class="menu-dropdown {{ request()->is('siswa*') || request()->is('kelas*') || request()->is('kartu*') ? 'open' : '' }}">
      <button class="menu-link menu-toggle">
        <span class="menu-ico">🧑‍🤝‍🧑</span>
        <span>Manajemen Data Master</span>
        <span class="menu-caret">▾</span>
      </button>
      <ul class="submenu">
        <li><a class="submenu-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}"
               href="{{ route('siswa.index') }}">Siswa</a></li>
        <li><a class="submenu-link {{ request()->routeIs('kelas.*') ? 'active' : '' }}"
               href="{{ route('kelas.index') }}">Kelas</a></li>
        <li><a class="submenu-link {{ request()->routeIs('kartu.*') ? 'active' : '' }}"
               href="{{ route('kartu.index') }}">Kartu RFID</a></li>
      </ul>
    </li>

    <li>
      <a class="menu-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}"
         href="{{ route('laporan.index') }}">
        <span class="menu-ico">📄</span>
        <span>Laporan</span>
      </a>
    </li>

    <li>
      <a class="menu-link {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}"
         href="{{ route('pengaturan.index') }}">
        <span class="menu-ico">⚙️</span>
        <span>Pengaturan</span>
      </a>
    </li>
  </ul>
</aside>
