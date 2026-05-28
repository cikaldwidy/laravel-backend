@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
@php
    $createTargetType = old('target_type', 'all');
    $createUserIds = collect(old('user_ids', []))->map(fn ($id) => (string) $id);
@endphp
<div class="space-y-5">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">Pengumuman</h1>
            <p class="mt-0.5 text-sm text-gray-500">Buat dan kelola informasi yang tampil untuk pegawai.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex w-fit items-center gap-2 rounded-md bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">
                <i class="fa-solid fa-bullhorn text-xs"></i>
                {{ method_exists($announcements, 'total') ? $announcements->total() : $announcements->count() }} pengumuman
            </span>
            <button type="button" onclick="toggleAnnouncementForm(true)" class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-gradient-to-r from-blue-600 to-sky-500 px-4 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:from-blue-700 hover:to-sky-600">
                <i class="fa-solid fa-plus text-xs"></i>
                Buat Pengumuman
            </button>
        </div>
    </div>

    <div id="announcement-create-panel" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/45 px-4 py-6 backdrop-blur-sm">
        <div class="absolute inset-0" onclick="toggleAnnouncementForm(false)"></div>
        <div class="relative max-h-[92vh] w-full max-w-4xl overflow-hidden rounded-md border border-gray-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="fa-solid fa-paper-plane text-sm"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700">Buat Pengumuman</h2>
                        <p class="text-xs text-gray-500">Atur target penerima dan periode tayang.</p>
                    </div>
                </div>
                <button type="button" onclick="toggleAnnouncementForm(false)" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-50 hover:text-gray-700" aria-label="Tutup form pengumuman">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

        <form method="POST" action="{{ route('admin.announcements.store') }}" class="grid max-h-[calc(92vh-4.25rem)] gap-4 overflow-y-auto p-5 md:grid-cols-2 announcement-form">
            @csrf
            <label class="md:col-span-2">
                <span class="block text-xs font-semibold text-gray-600 mb-1">Judul</span>
                <input name="judul" value="{{ old('judul') }}" placeholder="Judul pengumuman" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                @error('judul')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="md:col-span-2">
                <span class="block text-xs font-semibold text-gray-600 mb-1">Isi Pengumuman</span>
                <textarea name="isi" rows="4" placeholder="Isi pengumuman" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">{{ old('isi') }}</textarea>
                @error('isi')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label>
                <span class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Mulai</span>
                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                @error('tanggal_mulai')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label>
                <span class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Berakhir</span>
                <input type="date" name="tanggal_berakhir" value="{{ old('tanggal_berakhir') }}" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                @error('tanggal_berakhir')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label>
                <span class="block text-xs font-semibold text-gray-600 mb-1">Target</span>
                <select name="target_type" class="w-full rounded-md border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none js-target-type">
                    <option value="all" @selected($createTargetType === 'all')>Semua User</option>
                    <option value="unit" @selected($createTargetType === 'unit')>Per Unit Kerja/Bagian</option>
                    <option value="users" @selected($createTargetType === 'users')>Khusus User</option>
                </select>
                @error('target_type')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="js-unit-field hidden">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Unit Kerja/Bagian</span>
                <select name="unit_id" class="w-full rounded-md border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                    <option value="">Pilih Unit Kerja/Bagian</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) old('unit_id') === (string) $unit->id)>{{ $unit->nama_departemen }}</option>
                    @endforeach
                </select>
                @error('unit_id')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="js-users-field hidden md:col-span-2">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">User Khusus</span>
                <select name="user_ids[]" multiple class="min-h-32 w-full rounded-md border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected($createUserIds->contains((string) $user->id))>{{ $user->name }}</option>
                    @endforeach
                </select>
                <span class="block mt-1 text-xs text-gray-500">Tahan Ctrl untuk memilih lebih dari satu user.</span>
                @error('user_ids')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="md:col-span-2">
                <span class="block text-xs font-semibold text-gray-600 mb-1">URL Saat Notifikasi Diklik</span>
                <input name="action_url" value="{{ old('action_url', '/pengumuman') }}" placeholder="/pengumuman" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                <span class="block mt-1 text-xs text-gray-500">Gunakan path aplikasi, misalnya /pengumuman, /jadwal-shift, atau /tukar-shift.</span>
                @error('action_url')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <div class="flex justify-end border-t border-gray-100 pt-4 md:col-span-2">
                <button class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    Publish
                </button>
            </div>
        </form>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
            <span class="text-sm font-semibold text-gray-700">Daftar Pengumuman</span>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">{{ method_exists($announcements, 'total') ? $announcements->total() : $announcements->count() }} data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[920px] text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Judul</th>
                        <th class="px-5 py-3 text-left">Target</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Periode</th>
                        <th class="px-5 py-3 text-left">URL</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($announcements as $announcement)
                        @php
                            $targetLabel = match ($announcement->target_type) {
                                'unit' => $announcement->unit?->nama_departemen ?? 'Unit Kerja/Bagian',
                                'users' => 'Khusus User',
                                default => 'Semua User',
                            };
                        @endphp
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-5 py-4">
                                <div class="max-w-xs">
                                    <p class="truncate font-semibold text-gray-800">{{ $announcement->judul }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-gray-500">{{ $announcement->isi }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $targetLabel }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $announcement->is_published ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $announcement->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $announcement->tanggal_mulai?->format('d/m/Y') ?? '-' }} - {{ $announcement->tanggal_berakhir?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-mono text-xs text-gray-500">{{ $announcement->action_url ?: '-' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end">
                                    <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" data-confirm-form data-confirm-title="Hapus pengumuman?" data-confirm-message="Pengumuman ini akan dihapus dari daftar aktif." data-confirm-button="Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex items-center gap-1.5 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                            <i class="fa-solid fa-trash-can text-[10px]"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                    <i class="fa-solid fa-bullhorn text-sm"></i>
                                </div>
                                <p class="mt-3 text-sm font-semibold text-gray-700">Belum ada pengumuman.</p>
                                <p class="text-xs text-gray-500">Pengumuman yang dibuat akan muncul di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($announcements, 'links'))
            <div class="border-t border-gray-100 px-5 py-3.5">
                {{ $announcements->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .animate-modal {
        animation: modalIn .2s ease;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: scale(.96) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    html[data-admin-theme="dark"] #announcement-create-panel > .relative {
        background: var(--admin-card) !important;
        border-color: var(--admin-border) !important;
        box-shadow: 0 24px 56px rgba(0,0,0,.42) !important;
    }
</style>

<script>
function openModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    el.classList.add('flex');
}

function closeModal(id) {
    const el = document.getElementById(id);
    el.classList.add('hidden');
    el.classList.remove('flex');
}

document.getElementById('modal-hapus')?.addEventListener('click', function (event) {
    if (event.target === this) closeModal('modal-hapus');
});

function openHapus(nama, actionUrl) {
    document.getElementById('hapus-nama').textContent = nama;
    document.getElementById('form-hapus').action = actionUrl;
    openModal('modal-hapus');
}

function toggleAnnouncementForm(show = true) {
    const panel = document.getElementById('announcement-create-panel');
    if (!panel) return;

    panel.classList.toggle('hidden', !show);
    panel.classList.toggle('flex', show);
    document.body.classList.toggle('overflow-hidden', show);

    if (show) {
        setTimeout(() => panel.querySelector('input[name="judul"]')?.focus(), 250);
    }
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        toggleAnnouncementForm(false);
    }
});

@if($errors->any())
    toggleAnnouncementForm(true);
@endif

document.querySelectorAll('.announcement-form').forEach((form) => {
    const targetEl = form.querySelector('.js-target-type');
    const unitField = form.querySelector('.js-unit-field');
    const usersField = form.querySelector('.js-users-field');

    function syncTargetFields() {
        const value = targetEl?.value;
        unitField?.classList.toggle('hidden', value !== 'unit');
        usersField?.classList.toggle('hidden', value !== 'users');

        unitField?.querySelectorAll('select, input, textarea').forEach((field) => {
            field.disabled = value !== 'unit';
        });
        usersField?.querySelectorAll('select, input, textarea').forEach((field) => {
            field.disabled = value !== 'users';
        });
    }

    targetEl?.addEventListener('change', syncTargetFields);
    syncTargetFields();
});
</script>
@endsection
