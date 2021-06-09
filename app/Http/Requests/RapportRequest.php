<?php

namespace App\Http\Requests;

use App\Rapport;
use Illuminate\Foundation\Http\FormRequest;

class RapportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'comment_mo' => ['string', 'nullable'],
            'comment_tu' => ['string', 'nullable'],
            'comment_we' => ['string', 'nullable'],
            'comment_th' => ['string', 'nullable'],
            'comment_fr' => ['string', 'nullable'],
            'comment_sa' => ['string', 'nullable'],
            'isFinished' => ['boolean', 'nullable'],
            'default_project_id' => ['integer', 'exists:project,id'],
        ];
    }

    public function update(Rapport $rapport)
    {
        $data = $this->validated();

        $rapport->update($data);

        return $rapport;
    }
}
