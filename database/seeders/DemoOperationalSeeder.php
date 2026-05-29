<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\EmployeeDetail;
use App\Models\LeaveRequest;
use App\Models\Position;
use App\Models\Presensi;
use App\Models\ShiftSchedule;
use App\Models\ShiftSwap;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoOperationalSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name' => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
        ]);

        $departments = $this->seedOrganization();
        $users = $this->seedEmployees($departments);

        $this->seedSchedules($users);
        $this->seedPresensi($users);
        $this->seedLeaves($users, $admin);
        $this->seedAnnouncements($departments, $users);
        $this->seedShiftSwap($users);
    }

    private function seedOrganization(): array
    {
        $rawDepartments = [
            'Instalasi Gawat Darurat' => ['Perawat IGD', 'Dokter Jaga', 'Administrasi IGD'],
            'Rawat Inap' => ['Perawat Rawat Inap', 'Kepala Ruangan', 'Administrasi Rawat Inap'],
            'Laboratorium' => ['Analis Laboratorium', 'Koordinator Lab'],
        ];

        $items = [];

        foreach ($rawDepartments as $departmentName => $positionNames) {
            $department = Department::updateOrCreate([
                'nama_departemen' => $departmentName,
            ]);

            $unit = Unit::updateOrCreate([
                'nama_unit' => $departmentName,
            ], [
                'department_id' => $department->id,
            ]);

            $positions = [];
            foreach ($positionNames as $positionName) {
                $positions[] = Position::updateOrCreate([
                    'department_id' => $department->id,
                    'nama_jabatan' => $positionName,
                ]);
            }

            $items[$departmentName] = compact('department', 'unit', 'positions');
        }

        return $items;
    }

    private function seedEmployees(array $departments): array
    {
        $rawEmployees = [
            ['name' => 'Siti Rahmawati', 'username' => 'siti.rahmawati', 'email' => 'siti.rahmawati@demo.local', 'gender' => 'P', 'department' => 'Instalasi Gawat Darurat', 'position' => 0],
            ['name' => 'Budi Santoso', 'username' => 'budi.santoso', 'email' => 'budi.santoso@demo.local', 'gender' => 'L', 'department' => 'Instalasi Gawat Darurat', 'position' => 1],
            ['name' => 'Dewi Lestari', 'username' => 'dewi.lestari', 'email' => 'dewi.lestari@demo.local', 'gender' => 'P', 'department' => 'Instalasi Gawat Darurat', 'position' => 2],
            ['name' => 'Agus Prasetyo', 'username' => 'agus.prasetyo', 'email' => 'agus.prasetyo@demo.local', 'gender' => 'L', 'department' => 'Rawat Inap', 'position' => 0],
            ['name' => 'Rina Kurnia', 'username' => 'rina.kurnia', 'email' => 'rina.kurnia@demo.local', 'gender' => 'P', 'department' => 'Rawat Inap', 'position' => 1],
            ['name' => 'Hendra Wijaya', 'username' => 'hendra.wijaya', 'email' => 'hendra.wijaya@demo.local', 'gender' => 'L', 'department' => 'Rawat Inap', 'position' => 2],
            ['name' => 'Maya Anggraini', 'username' => 'maya.anggraini', 'email' => 'maya.anggraini@demo.local', 'gender' => 'P', 'department' => 'Laboratorium', 'position' => 0],
            ['name' => 'Fajar Nugroho', 'username' => 'fajar.nugroho', 'email' => 'fajar.nugroho@demo.local', 'gender' => 'L', 'department' => 'Laboratorium', 'position' => 1],
        ];

        $users = [];

        foreach ($rawEmployees as $index => $employee) {
            $user = User::updateOrCreate([
                'email' => $employee['email'],
            ], [
                'name' => $employee['name'],
                'username' => $employee['username'],
                'password' => Hash::make('password'),
                'role' => 'user',
            ]);

            $organization = $departments[$employee['department']];
            $position = $organization['positions'][$employee['position']];

            UserProfile::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'no_hp' => '0812' . str_pad((string) ($index + 1000), 8, '0', STR_PAD_LEFT),
                'alamat' => 'Jl. Demo Presensi No. ' . ($index + 1),
                'tanggal_lahir' => Carbon::create(1988 + $index, ($index % 12) + 1, 10)->toDateString(),
                'jenis_kelamin' => $employee['gender'],
                'agama' => 'Islam',
                'nik' => '3374' . str_pad((string) ($index + 1), 12, '0', STR_PAD_LEFT),
            ]);

            EmployeeDetail::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'unit_id' => $organization['unit']->id,
                'department_id' => $organization['department']->id,
                'position_id' => $position->id,
                'nip' => 'RS' . now()->format('Y') . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'departemen' => $organization['department']->nama_departemen,
                'jabatan' => $position->nama_jabatan,
                'status_kerja' => $index % 3 === 0 ? 'kontrak' : 'tetap',
            ]);

            $users[] = $user;
        }

        return $users;
    }

    private function seedSchedules(array $users): void
    {
        $shiftOptions = [
            ['code' => 'P', 'in' => '07:00:00', 'out' => '15:00:00'],
            ['code' => 'S', 'in' => '15:00:00', 'out' => '23:00:00'],
            ['code' => 'M', 'in' => '23:00:00', 'out' => '07:00:00'],
        ];

        foreach (range(-5, 5) as $dayOffset) {
            $date = today()->addDays($dayOffset)->toDateString();

            foreach ($users as $index => $user) {
                $isOff = (($index + $dayOffset + 5) % 7) === 0;
                $shift = $shiftOptions[($index + $dayOffset + 6) % count($shiftOptions)];

                ShiftSchedule::updateOrCreate([
                    'user_id' => $user->id,
                    'tanggal' => $date,
                ], [
                    'jam_masuk' => $isOff ? '00:00:00' : $shift['in'],
                    'jam_pulang' => $isOff ? '00:00:00' : $shift['out'],
                    'status' => $isOff ? 'libur' : 'aktif',
                    'shift_code' => $isOff ? 'O' : $shift['code'],
                ]);
            }
        }
    }

    private function seedPresensi(array $users): void
    {
        foreach (range(-5, 0) as $dayOffset) {
            $date = today()->addDays($dayOffset)->toDateString();

            foreach ($users as $index => $user) {
                $schedule = ShiftSchedule::query()
                    ->where('user_id', $user->id)
                    ->whereDate('tanggal', $date)
                    ->first();

                if (!$schedule || $schedule->status !== 'aktif') {
                    continue;
                }

                if ($dayOffset === 0 && $index === 4) {
                    continue;
                }

                $isLate = in_array(($index + abs($dayOffset)) % 5, [1], true);
                $hasCheckedOut = $dayOffset < 0 || $index % 2 === 0;
                $jamMasuk = Carbon::parse($schedule->jam_masuk->format('H:i:s'))
                    ->addMinutes($isLate ? 24 + $index : max(0, $index - 2));
                $jamKeluar = $hasCheckedOut
                    ? Carbon::parse($schedule->jam_pulang->format('H:i:s'))->subMinutes($index % 3)
                    : null;

                Presensi::updateOrCreate([
                    'user_id' => $user->id,
                    'tanggal' => $date,
                ], [
                    'jam_masuk' => $jamMasuk->format('H:i:s'),
                    'jam_keluar' => $jamKeluar?->format('H:i:s'),
                    'latitude_masuk' => -7.797068,
                    'longitude_masuk' => 110.370529,
                    'latitude_keluar' => $jamKeluar ? -7.797071 : null,
                    'longitude_keluar' => $jamKeluar ? 110.370531 : null,
                    'jarak_masuk' => 18.5 + $index,
                    'jarak_keluar' => $jamKeluar ? 20.0 + $index : null,
                    'face_distance_masuk' => 0.31,
                    'face_distance_keluar' => $jamKeluar ? 0.29 : null,
                    'status' => $isLate ? 'telat' : 'hadir',
                    'status_pulang' => $jamKeluar ? 'normal' : null,
                ]);
            }
        }
    }

    private function seedLeaves(array $users, User $admin): void
    {
        LeaveRequest::updateOrCreate([
            'user_id' => $users[4]->id,
            'tanggal_mulai' => today()->toDateString(),
            'tanggal_selesai' => today()->toDateString(),
        ], [
            'jenis_izin' => 'izin',
            'keterangan' => 'Keperluan keluarga.',
            'status' => 'approved',
            'catatan_admin' => 'Disetujui untuk data demo.',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        LeaveRequest::updateOrCreate([
            'user_id' => $users[6]->id,
            'tanggal_mulai' => today()->addDays(1)->toDateString(),
            'tanggal_selesai' => today()->addDays(2)->toDateString(),
        ], [
            'jenis_izin' => 'cuti',
            'keterangan' => 'Rencana cuti tahunan.',
            'status' => 'pending',
            'catatan_admin' => null,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        LeaveRequest::updateOrCreate([
            'user_id' => $users[2]->id,
            'tanggal_mulai' => today()->subDays(2)->toDateString(),
            'tanggal_selesai' => today()->subDays(1)->toDateString(),
        ], [
            'jenis_izin' => 'sakit',
            'keterangan' => 'Istirahat sesuai rekomendasi dokter.',
            'status' => 'approved',
            'catatan_admin' => 'Lampiran menyusul.',
            'approved_by' => $admin->id,
            'approved_at' => now()->subDays(2),
        ]);
    }

    private function seedAnnouncements(array $departments, array $users): void
    {
        Announcement::updateOrCreate([
            'judul' => 'Briefing Operasional Pagi',
        ], [
            'isi' => 'Seluruh pegawai dimohon mengikuti briefing singkat sebelum memulai pelayanan.',
            'tanggal_mulai' => today()->subDay()->toDateString(),
            'tanggal_berakhir' => today()->addDays(7)->toDateString(),
            'target_type' => 'all',
            'unit_id' => null,
            'is_published' => true,
            'action_url' => null,
        ]);

        Announcement::updateOrCreate([
            'judul' => 'Pengecekan Alat Laboratorium',
        ], [
            'isi' => 'Tim laboratorium melakukan pengecekan alat pada awal shift.',
            'tanggal_mulai' => today()->toDateString(),
            'tanggal_berakhir' => today()->addDays(5)->toDateString(),
            'target_type' => 'unit',
            'unit_id' => $departments['Laboratorium']['unit']->id,
            'is_published' => true,
            'action_url' => null,
        ]);

        $personal = Announcement::updateOrCreate([
            'judul' => 'Konfirmasi Jadwal Dinas',
        ], [
            'isi' => 'Mohon cek ulang jadwal dinas minggu berjalan.',
            'tanggal_mulai' => today()->toDateString(),
            'tanggal_berakhir' => today()->addDays(3)->toDateString(),
            'target_type' => 'users',
            'unit_id' => null,
            'is_published' => true,
            'action_url' => null,
        ]);

        $personal->users()->syncWithoutDetaching([
            $users[0]->id,
            $users[3]->id,
            $users[6]->id,
        ]);
    }

    private function seedShiftSwap(array $users): void
    {
        $requesterShift = ShiftSchedule::query()
            ->where('user_id', $users[0]->id)
            ->whereDate('tanggal', today()->addDay()->toDateString())
            ->where('status', 'aktif')
            ->first();

        $targetShift = ShiftSchedule::query()
            ->where('user_id', $users[1]->id)
            ->whereDate('tanggal', today()->addDay()->toDateString())
            ->where('status', 'aktif')
            ->first();

        if (!$requesterShift || !$targetShift) {
            return;
        }

        ShiftSwap::updateOrCreate([
            'requester_id' => $users[0]->id,
            'target_user_id' => $users[1]->id,
            'shift_id' => $requesterShift->id,
            'target_shift_id' => $targetShift->id,
        ], [
            'status' => 'pending',
            'note' => 'Contoh pengajuan tukar shift untuk data demo.',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }
}
