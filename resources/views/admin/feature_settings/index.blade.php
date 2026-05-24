@extends('layouts.admin')

@section('title', 'Pengaturan Fitur')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">Pengaturan Fitur</h1>
            <p class="mt-0.5 text-sm text-gray-500">Atur fitur yang tampil dan bisa diakses oleh role User dan Admin.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.features.update') }}" class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        @csrf
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
            <span class="text-sm font-semibold text-gray-700">Daftar Fitur</span>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">{{ count($features) }} fitur</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3 text-left">Fitur</th>
                        <th class="px-5 py-3 text-center">User</th>
                        <th class="px-5 py-3 text-center">Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($features as $featureKey => $feature)
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-blue-50 text-blue-600">
                                        <i class="fas {{ $feature['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-700">{{ $feature['label'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $featureKey }}</p>
                                    </div>
                                </div>
                            </td>
                            @foreach($roles as $role)
                                <td class="px-5 py-3.5 text-center">
                                    @if($availableFeatures[$featureKey][$role] ?? true)
                                        <label class="relative inline-flex cursor-pointer items-center">
                                            <input
                                                type="checkbox"
                                                name="settings[{{ $featureKey }}][{{ $role }}]"
                                                value="1"
                                                class="peer sr-only"
                                                @checked($settings[$featureKey][$role] ?? false)
                                            >
                                            <span class="h-6 w-12 rounded-full bg-gray-200 transition peer-checked:bg-blue-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-6"></span>
                                        </label>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-400">Tidak tersedia</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end border-t border-gray-100 bg-gray-50 px-5 py-4">
            <button class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                <i class="fas fa-save text-xs"></i>
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
