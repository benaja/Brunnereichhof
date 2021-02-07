<?php

namespace App\Http\Requests;

use App\Car;
use Illuminate\Foundation\Http\FormRequest;

class CarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sometimes =  $this->method() === 'PATCH' ? 'sometimes' : '';

        return [
            'name' => ['required', 'string', $sometimes],
            'seats' => ['required', 'integer', $sometimes],
            'number' => ['string', 'nullable'],
            'comment' => ['string', 'nullable'],
            'important_comment' => ['string', 'nullable'],
            'fuel' => ['string', 'nullable'],
            'image' => ['string', 'nullable'],
        ];
    }

    public function store() {
        $data = $this->validated();

        return Car::create($data);
    }

    public function update(Car $car) {
        $data = $this->validated();

        $car->update($data);

        return $car;
    }
}
