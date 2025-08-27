<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
      return [
        'title'          => ['sometimes', 'required', 'string', 'max:255'],
        'description'    => ['sometimes', 'nullable', 'string'],
        'due_date'       => ['sometimes', 'nullable', 'date'],
        'state'          => ['sometimes', Rule::in(['Pendiente', 'En progreso', 'Hecha'])],
        'priority'       => ['sometimes', Rule::in(['Baja', 'Media', 'Alta'])],
        'assigned_users' => ['sometimes', 'array', 'min:1'],
        'assigned_users.*' => ['integer', 'exists:users,id'],
    ];
    }
}
