<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest   
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'            => ['required','string','max:255'],
            'description'     => ['nullable','string'],
            'start_date'      => ['required','date'],
            'end_date'        => ['nullable','date','after_or_equal:start_date'],
            'status'          => ['required','in:Activo,Pausado,Terminado'],
            'collaborators'   => ['array'],
            'collaborators.*' => ['integer','exists:users,id'],
        ];
    }
}
