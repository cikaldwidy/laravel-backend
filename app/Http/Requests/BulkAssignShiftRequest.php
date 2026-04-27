<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAssignShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'status' => ['required', 'in:aktif,libur'],
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'jam_masuk' => ['nullable', 'date_format:H:i'],
            'jam_pulang' => ['nullable', 'date_format:H:i'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasTemplate = (bool) $this->input('shift_id');
            $status = (string) $this->input('status');

            if (!$hasTemplate && $status === 'aktif') {
                if (!$this->input('jam_masuk') || !$this->input('jam_pulang')) {
                    $validator->errors()->add('jam_masuk', 'Jam masuk dan jam pulang wajib diisi saat shift aktif tanpa template shift.');
                }
            }
        });
    }
}
