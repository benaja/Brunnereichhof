<?php

namespace App\Http\Requests;

use App\Tool;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Image;

class ToolRequest extends FormRequest
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
            'amount' => ['required', 'integer', $sometimes],
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
            $tool = Tool::create($data);

            if (isset($data['image']) && $data['image']) {
                $imagePath = $this->storeImage($data['image']);

                $tool->update([
                    'image' => $imagePath,
                ]);
            }

            return $tool;
        });
    }

    public function update(Tool $tool)
    {
        $data = $this->validated();

        $tool->update($data);

        return DB::transaction(function () use ($data, $tool) {
            if (isset($data['image']) && $data['image'] && is_file($data['image'])) {
                Storage::disk('s3')->delete($tool->image);
                $data['image'] = $this->storeImage($data['image']);
            } elseif (! isset($data['image'])) {
                Storage::disk('s3')->delete($tool->image);
            }

            $tool->update($data);

            return $tool;
        });
    }

    private function storeImage($image)
    {
        $thumbnail = Image::make($image);
        $width = $thumbnail->width();
        $height = $thumbnail->height();
        $factor = $width / $height;
        $thumbnail->resize(100 * $factor, 100);

        $imagePath = Storage::disk('s3')->put('tools', $image);

        Storage::disk('s3')->put('small/'.$imagePath, (string) $thumbnail->stream());

        return $imagePath;
    }
}
