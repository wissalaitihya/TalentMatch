<?php

namespace App\Http\Requests;

use App\Models\Offre;
use Illuminate\Foundation\Http\FormRequest;

class SoumettreCvRequest extends FormRequest
{
    public function authorize(): bool
    {
        $offre = $this->route('offre');

        return $offre instanceof Offre && $offre->user_id === $this->user()?->id;
    }

    public function rules(): array
    {
        return [
            'nom_candidat' => ['required', 'string', 'max:255'],
            'cv_texte' => ['required', 'string', 'min:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom_candidat.required' => 'Le nom du candidat est requis.',
            'cv_texte.required' => 'Le texte du CV est requis.',
            'cv_texte.min' => 'Le CV doit contenir au moins 20 caractères.',
        ];
    }
}
