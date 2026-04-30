@extends('layouts.app')

@section('title', 'Buat Izin')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-xl shadow p-6">
            <h1 class="text-xl font-bold text-gray-800">Form Izin</h1>
            <form method="POST" action="{{ route('leave_requests.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-semibold text-gray-700">Jenis Izin</label>
                    <select name="jenis_izin" class="w-full border rounded px-3 py-2 mt-1">
                        <option value="sakit">Sakit</option>
                        <option value="cuti">Cuti</option>
                        <option value="izin">Izin</option>
                        <option value="dinas">Dinas Luar</option>
                    </select>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="w-full border rounded px-3 py-2 mt-1">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="w-full border rounded px-3 py-2 mt-1">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Keterangan</label>
                    <textarea name="keterangan" rows="4" class="w-full border rounded px-3 py-2 mt-1">{{ old('keterangan') }}</textarea>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Lampiran</label>
                    <input type="file" name="lampiran" class="w-full border rounded px-3 py-2 mt-1 bg-white">
                </div>
                <div class="flex gap-3">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded font-semibold">Submit</button>
                    <a href="{{ route('leave_requests.index') }}" class="border px-4 py-2 rounded font-semibold">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
