<?php

namespace App\Http\Requests;

use App\Car;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Image;

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
        $sometimes = $this->method() === 'PATCH' ? 'sometimes' : '';

        $validation = [
            'name' => ['required', 'string', $sometimes],
            'seats' => ['required', 'integer', $sometimes],
            'number' => ['string', 'nullable'],
            'comment' => ['string', 'nullable'],
            'important_comment' => ['string', 'nullable'],
            'fuel' => ['string', 'required', $sometimes],
        ];

        if (request()->file('image')) {
            $validation['image'] = ['image'];
        } elseif (request()->get('image')) {
            $validation['image'] = ['string'];
        } else {
            $validation['image'] = ['nullable'];
        }

        return $validation;
    }

    public function store()
    {
        $data = $this->validated();

        return DB::transaction(function () use ($data) {
            $car = Car::create($data);

            if (isset($data['image']) && $data['image']) {
                $imagePath = $this->storeImage($data['image']);

                $car->update([
                    'image' => $imagePath,
                ]);
            }

            return $car;
        });
    }

    public function update(Car $car)
    {
        $data = $this->validated();

        return DB::transaction(function () use ($data, $car) {
            if (isset($data['image']) && $data['image'] && is_file($data['image'])) {
                Storage::disk('s3')->delete($car->image);
                $data['image'] = $this->storeImage($data['image']);
            } elseif (! isset($data['image'])) {
                Storage::disk('s3')->delete($car->image);
            }

            $car->update($data);

            return $car;
        });
    }

    private function storeImage($image)
    {
        $thumbnail = Image::make($image);
        $width = $thumbnail->width();
        $height = $thumbnail->height();
        $factor = $width / $height;
        $thumbnail->resize(100 * $factor, 100);

        $imagePath = Storage::disk('s3')->put('cars', $image);

        Storage::disk('s3')->put('small/'.$imagePath, (string) $thumbnail->stream());

        return $imagePath;
    }
}
