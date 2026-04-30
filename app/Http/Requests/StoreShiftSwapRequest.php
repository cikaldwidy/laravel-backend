<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftSwapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'shift_id' => ['required', 'integer', 'exists:shift_schedules,id'],
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
            'target_shift_id' => ['required', 'integer', 'exists:shift_schedules,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
