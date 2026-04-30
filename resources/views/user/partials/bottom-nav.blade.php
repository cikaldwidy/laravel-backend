@php($active = $active ?? '')

<nav class="user-bottom-nav">
    <div class="user-bottom-nav-inner">
        <a href="{{ route('dashboard') }}" class="{{ $active === 'home' ? 'user-nav-link-active' : 'user-nav-link' }}">
            <i class="fa-solid fa-house text-lg"></i>
            <p>Home</p>
        </a>
        <a href="{{ route('history.index') }}" class="{{ $active === 'history' ? 'user-nav-link-active' : 'user-nav-link' }}">
            <i class="fa-solid fa-file-lines text-lg"></i>
            <p>Histori</p>
        </a>
        <a href="{{ route('absen.page') }}" class="w-14 h-14 -mt-8 bg-emerald-700 text-white rounded-full flex items-center justify-center border-4 border-white shadow-lg">
            <i class="fa-solid fa-fingerprint text-xl"></i>
        </a>
        <a href="{{ route('leave_requests.index') }}" class="{{ $active === 'leave' ? 'user-nav-link-active' : 'user-nav-link' }}">
            <i class="fa-solid fa-calendar-days text-lg"></i>
            <p>Izin</p>
        </a>
        <a href="{{ route('announcements.index') }}" class="{{ $active === 'info' ? 'user-nav-link-active' : 'user-nav-link' }}">
            <i class="fa-solid fa-circle-info text-lg"></i>
            <p>Info</p>
        </a>
    </div>
</nav>
