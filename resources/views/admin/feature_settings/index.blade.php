@extends('layouts.admin')

@section('title', 'Pengaturan Fitur')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Pengaturan Fitur</h1>
                <p class="text-sm text-gray-500 mt-1">Atur fitur yang tampil dan bisa diakses oleh role User dan Admin.</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                <i class="fas fa-sliders"></i>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.features.update') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold">Fitur</th>
                        <th class="px-5 py-3 text-center font-bold">User</th>
                        <th class="px-5 py-3 text-center font-bold">Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($features as $featureKey => $feature)
                        <tr>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                        <i class="fas {{ $feature['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $feature['label'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $featureKey }}</p>
                                    </div>
                                </div>
                            </td>
                            @foreach($roles as $role)
                                <td class="px-5 py-4 text-center">
                                    @if($availableFeatures[$featureKey][$role] ?? true)
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                name="settings[{{ $featureKey }}][{{ $role }}]"
                                                value="1"
                                                class="sr-only peer"
                                                @checked($settings[$featureKey][$role] ?? false)
                                            >
                                            <span class="w-12 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-100 rounded-full peer peer-checked:after:translate-x-6 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></span>
                                        </label>
                                    @else
                                        <span class="text-xs font-semibold text-gray-400">Dihapus</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm">
                <i class="fas fa-save"></i>
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
