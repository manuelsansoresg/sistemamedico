<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSharedExpedientePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('permission');

        return $this->user()?->hasRole('paciente') === true
            && $this->user()?->perfil_compartido === true
            && $permission?->patient_id === $this->user()?->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'permission_type' => ['required', Rule::in(['read', 'download'])],
            'duration_hours' => ['nullable', 'integer', 'min:5', 'max:8760'],
            'can_edit_owned_records' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'accept_terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'duration_hours.min' => __('pacientes.qr.permissions.validation.min_duration'),
            'accept_terms.accepted' => __('pacientes.qr.permissions.validation.accept_terms'),
        ];
    }
}
