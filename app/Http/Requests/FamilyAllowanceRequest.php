<?php

namespace App\Http\Requests;

use App\FamilyAllowance;
use Illuminate\Foundation\Http\FormRequest;

class FamilyAllowanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'civil_status' => ['string'],
            'has_family_allowance' => ['boolean'],
            'expiration_of_family_allowance' => ['date'],
            'partner_employed' => ['boolean'],
            'needs_e411_form' => ['boolean'],
            'is_e411_handed_out' => ['boolean'],
            'is_e411_submitted' => ['boolean'],
            'has_marriage_document' => ['boolean'],
            'has_divorce_document' => ['boolean'],
            'it_registration_family_allowances_send' => ['boolean'],
            'family_allowance_expiration_date' => ['date'],
            'claim_id_received' => ['boolean'],
            'claim_id_expiration_date' => ['date'],
        ];
    }

    public function update(FamilyAllowance $familyAllowance)
    {
        $data = $this->validated();

        $familyAllowance->update($data);

        return $familyAllowance;
    }
}
