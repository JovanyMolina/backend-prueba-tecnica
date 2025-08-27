<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => ['sometimes','required','string','max:255'],
            'description'    => ['sometimes','nullable','string'],
            'start_date'     => ['sometimes','required','date'],
            'end_date'       => ['nullable','date','after_or_equal:start_date'],
            'status'         => ['sometimes','required', Rule::in(['Activo','Pausado','Terminado'])],
            'collaborators'  => ['sometimes','array'],
            'collaborators.*'=> ['integer','exists:users,id'],
        ];
    }
}
