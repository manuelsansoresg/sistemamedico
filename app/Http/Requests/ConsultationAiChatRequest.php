<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsultationAiChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('doctor') === true;
    }

    public function rules(): array
    {
        return [
            'cita_id' => ['required', 'integer', 'exists:citas,id'],
            'message' => ['required', 'string', 'max:1200'],
            'messages' => ['nullable', 'array', 'max:10'],
            'messages.*.role' => ['required_with:messages', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required_with:messages', 'string', 'max:2000'],
            'context' => ['nullable', 'array'],
        ];
    }
}
