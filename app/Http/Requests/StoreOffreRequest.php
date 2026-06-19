<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOffreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $input = $this->input('competences_requises');
        if (is_string($input)) {
            $competences = collect(explode("\n", $input))
                ->map(fn (string $ligne) => trim($ligne))
                ->filter()
                ->values()
                ->all();
            $this->merge(['competences_requises' => $competences]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'competences_requises' => ['required', 'array', 'min:1'],
            'competences_requises.*' => ['string', 'max:100'],
            'niveau_experience_min' => ['required', 'integer', 'min:0', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'competences_requises.required' => 'Indique au moins une compétence requise (une par ligne).',
            'competences_requises.min' => 'Indique au moins une compétence requise (une par ligne).',
        ];
    }
}
