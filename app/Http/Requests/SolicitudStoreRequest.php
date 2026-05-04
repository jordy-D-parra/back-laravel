<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SolicitudStoreRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'tipo_solicitante' => ['required', Rule::in(['interno', 'externo'])],
            'institucion_id' => 'required_if:tipo_solicitante,externo|nullable|exists:institucion,id',
            'fecha_requerida' => 'required|date|after_or_equal:today',
            'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
            'justificacion' => 'required|string|min:20|max:1000',
            'prioridad' => ['required', Rule::in(['baja', 'normal', 'alta', 'urgente'])],
            'observaciones' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.tipo_item' => ['required', Rule::in(['activo', 'periferico'])],
            'items.*.item_id' => 'required|integer',
            'items.*.cantidad' => 'required|integer|min:1',
            'oficio_adjunto' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'justificacion.min' => 'La justificación debe tener al menos 20 caracteres',
            'fecha_requerida.after_or_equal' => 'La fecha requerida no puede ser anterior a hoy',
            'items.required' => 'Debe solicitar al menos un item',
            'items.*.cantidad.min' => 'La cantidad debe ser al menos 1'
        ];
    }
}
