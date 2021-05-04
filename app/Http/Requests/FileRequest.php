<?php

namespace App\Http\Requests;

use App\File;
use Illuminate\Foundation\Http\FormRequest;

class FileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $required = request()->method() === 'POST' ? 'required' : '';

        return [
            'filable_id' => [$required, 'integer'],
            'filable_type' => [$required, 'string'],
            'type' => [$required, 'string'],
            'is_submitted' => ['boolean'],
            'expiration_date' => ['date'],
            'path' => ['string', 'nullable'],
        ];
    }

    public function store()
    {
        $data = $this->validated();

        return File::create($data);
    }

    public function update(File $file)
    {
        $data = $this->validated();

        $file->update($data);

        return $file;
    }
}
