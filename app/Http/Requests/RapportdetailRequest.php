<?php

namespace App\Http\Requests;

use App\Rapport;
use App\Rapportdetail;
use Illuminate\Foundation\Http\FormRequest;

class RapportdetailRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $required = $this->method() === 'POST' ? 'required' : '';

        return [
            'rapport_id' => [$required, 'integer', 'exists:rapport,id'],
            'employee_id' => [$required, 'integer', 'exists:employee,id'],
            'foodtype_id' => ['nullable', 'integer'],
            'date' => [$required, 'date'],
            'hours' => ['nullable', 'numeric'],
            'project_id' => ['nullable', 'integer'],
            'contract_type' => [$required, 'string'],
            'day' => [$required, 'integer'],
        ];
    }

    public function store()
    {
        $data = $this->validated();

        $rapport = Rapport::find($data['rapport_id']);

        $data['customer_id'] = $rapport->customer_id;

        return Rapportdetail::create($data);
    }

    public function update(Rapportdetail $rapportdetail)
    {
        $data = $this->validated();

        $rapportdetail->update($data);

        return $rapportdetail;
    }
}
