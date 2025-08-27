<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
    return [
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'due_date'       => ['nullable', 'date'],
            'state'          => ['required', Rule::in(['Pendiente', 'En progreso', 'Hecha'])],
            'priority'       => ['required', Rule::in(['Baja', 'Media', 'Alta'])],
            'assigned_users' => ['required', 'array', 'min:1'],
            'assigned_users.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio',
            'state.required' => 'El estado es obligatorio',
            'priority.required' => 'La prioridad es obligatoria',
            'assigned_to.required' => 'Debe asignar la tarea a un usuario',
            'assigned_to.exists' => 'El usuario seleccionado no existe',
        ];
    }
}
