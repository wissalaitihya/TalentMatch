<?php

namespace App\Http\Requests;

use App\Models\Analyse;
use App\Models\Candidat;
use App\Models\Offre;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ComparisonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $offre = $this->route('offre');

        return $offre instanceof Offre && $offre->user_id === $this->user()?->id;
    }

    public function rules(): array
    {
        return [
            'candidat_id_1' => ['required', 'integer', 'exists:candidats,id'],
            'candidat_id_2' => ['required', 'integer', 'exists:candidats,id', 'different:candidat_id_1'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $offre = $this->route('offre');
                $id1 = $this->integer('candidat_id_1');
                $id2 = $this->integer('candidat_id_2');

                $candidat1 = Candidat::find($id1);
                $candidat2 = Candidat::find($id2);

                if (! $candidat1 || $candidat1->offre_id !== $offre->id) {
                    $validator->errors()->add('candidat_id_1', 'Ce candidat n\'appartient pas à cette offre.');
                }

                if (! $candidat2 || $candidat2->offre_id !== $offre->id) {
                    $validator->errors()->add('candidat_id_2', 'Ce candidat n\'appartient pas à cette offre.');
                }

                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $analyse1 = Analyse::where('candidat_id', $id1)->first();
                $analyse2 = Analyse::where('candidat_id', $id2)->first();

                if (! $analyse1 || $analyse1->statut_analyse?->value !== 'completed') {
                    $validator->errors()->add('candidat_id_1', 'L\'analyse de ce candidat n\'est pas encore terminée.');
                }

                if (! $analyse2 || $analyse2->statut_analyse?->value !== 'completed') {
                    $validator->errors()->add('candidat_id_2', 'L\'analyse de ce candidat n\'est pas encore terminée.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'candidat_id_1.required' => 'Veuillez sélectionner le premier candidat.',
            'candidat_id_1.exists' => 'Le premier candidat sélectionné n\'existe pas.',
            'candidat_id_2.required' => 'Veuillez sélectionner le deuxième candidat.',
            'candidat_id_2.exists' => 'Le deuxième candidat sélectionné n\'existe pas.',
            'candidat_id_2.different' => 'Veuillez sélectionner deux candidats différents.',
        ];
    }
}
