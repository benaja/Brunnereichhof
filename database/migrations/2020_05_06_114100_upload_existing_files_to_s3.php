<?php

use App\Employee;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class UploadExistingFilesToS3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $employeesWithImage = Employee::whereNotNull('profileimage')->get();

        foreach($employeesWithImage as $employee) {
            if (Storage::disk('profileimages')->exists($employee->profileimage)) {
                $imagePath = public_path('profileimages/'). $employee->profileimage;

                $img = Image::make($imagePath);
                $width = $img->width();
                $height = $img->height();
                $factor = $width / $height;
                $img->resize(150 * $factor, 150);

                $imagePath = Storage::disk('s3')->putFile('profileimages', new File(public_path('profileimages/'). $employee->profileimage));
                Storage::disk('s3')->put('small/'.$imagePath, (string)$img->stream());
                $employee->profileimage = $imagePath;
                $employee->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
