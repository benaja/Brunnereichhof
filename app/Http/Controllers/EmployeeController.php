<?php

namespace App\Http\Controllers;

use Image;
use App\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    // GET employee
    public function index(Request $request)
    {
        $employees = Employee::all()->sortBy('lastname')->toArray();
        return array_values($employees);
    }

    // GET employee/create
    public function create(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);

        return view('pages.admin.employee.create');
    }

    // POST employee
    public function store(Request $request)
    {
        $this->validate(request(), $this->validateArray);

        $employee = Employee::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'callname' => $request->callname,
            'nationality' => $request->nationality,
            'isIntern' => $request->isIntern,
            'isDriver' => $request->isDriver,
            'german_knowledge' => $request->german_knowledge,
            'english_knowledge' => $request->english_knowledge,
            'sex' => $request->sex,
            'comment' => $request->comment,
            'experience' => $request->experience,
            'isActive' => 1,
            'allergy' => $request->allergy
        ]);

        return $employee->id;
    }

    // GET employee/{id}
    public function show(Request $request, Employee $employee)
    {
        return $employee;

        return view('pages.admin.employee.show', compact('employee'));
    }

    // PATCH employee/{id}
    public function update(Request $request, $id)
    {
        // if($request->profileimage != null){
        //     if($request->profileimage == "delete"){
        //         $imagePath = public_path('profileimages/') . $employee->profileimage;
        //         $smallImagePath = public_path('profileimages/') . "small-" . $employee->profileimage;
        //         if(file_exists($imagePath)) {
        //             unlink($imagePath);
        //         }
        //         if(file_exists($smallImagePath)){
        //             unlink($smallImagePath);
        //         }
        //     }else{
        //         $photoName = uniqid().'.'.$request->profileimage->getClientOriginalExtension();
        //         $request->profileimage->move(public_path('profileimages'), $photoName);

        //         $img = Image::make(public_path('profileimages/'). $photoName);
        //         $width = $img->width();
        //         $height = $img->height();

        //         $factor = $width / $height;

        //         $img->resize(150 * $factor, 150);

        //         $img->save(public_path('profileimages/'). "small-$photoName");

        //         $request->element = "profileimage";
        //         $request->data = $photoName;
        //         if($employee->profileimage != null){
        //             $imagePath = public_path('profileimages/') . $employee->profileimage;
        //             $smallImagePath = public_path('profileimages/') . "small-" . $employee->profileimage;
        //             if(file_exists($imagePath)) {
        //                 unlink($imagePath);
        //             }
        //             if(file_exists($smallImagePath)){
        //                 unlink($smallImagePath);
        //             }
        //         }
        //     }
                
        // }else if($request->profileimage == ""){
        // }
        // $element = $request->element;

        // $employee->$element = $request->data;
        // $employee->save();

        // return $request->data;

        $this->validate($request, $this->validateArray);

        $employe = Employee::find($id);

        $employe->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'callname' => $request->callname,
            'nationality' => $request->nationality,
            'isIntern' => $request->isIntern,
            'isDriver' => $request->isDriver,
            'german_knowledge' => $request->german_knowledge,
            'english_knowledge' => $request->english_knowledge,
            'sex' => $request->sex,
            'comment' => $request->comment,
            'experience' => $request->experience,
            'isActive' => $request->isActive,
            'allergy' => $request->allergy
        ]);
        $employe->save();
    }
    
    // POST employee/{id}/editimage
    public function uploadImage(Request $request, $id)
    {
        if ($request->profileimage != null) {
            $employee = Employee::find($id);
            $photoName = uniqid() . '.' . $request->profileimage->getClientOriginalExtension();
            $request->profileimage->move(public_path('profileimages'), $photoName);

            $img = Image::make(public_path('profileimages/') . $photoName);
            $width = $img->width();
            $height = $img->height();

            $factor = $width / $height;

            $img->resize(150 * $factor, 150);

            $img->save(public_path('profileimages/') . "small-$photoName");

            $request->element = "profileimage";
            $request->data = $photoName;
            if ($employee->profileimage != null) {
                $imagePath = public_path('profileimages/') . $employee->profileimage;
                $smallImagePath = public_path('profileimages/') . "small-" . $employee->profileimage;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                if (file_exists($smallImagePath)) {
                    unlink($smallImagePath);
                }
            }
            $employee->profileimage = $photoName;
            $employee->save();
            return response($photoName);
        }else {
            return response('no image', 404);
        }
    }

    public function deleteImage(Request $request, $id){
        $employee = Employee::find($id);

        $imagePath = public_path('profileimages/') . $employee->profileimage;
        $smallImagePath = public_path('profileimages/') . "small-" . $employee->profileimage;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        if (file_exists($smallImagePath)) {
            unlink($smallImagePath);
        }
        $employee->profileimage = null;
        $employee->save();
    }

    // DELETE employee/{id}
    public function destroy(Request $request, Employee $employee)
    {
        if ($employee->profileimage != null) {
            $imagePath = public_path('profileimages/') . $employee->profileimage;
            $smallImagePath = public_path('profileimages/') . "small-" . $employee->profileimage;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            if (file_exists($smallImagePath)) {
                unlink($smallImagePath);
            }
        }

        $employee->delete();
    }

    // GET employee/all
    public function all(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);

        $employees = Employee::where('isActive', 1)->get();

        return $employees;
    }

    // Helpers
    private $validateArray = [
        'firstname' => 'required|string|max:100',
        'lastname' => 'required|string|max:100',
        'callname' => 'nullable|max:100|string',
        'sex' => 'required',
        'comment' => 'nullable|string|max:500',
        'experience' => 'nullable|string|max:100',
        'allergy' => 'nullable|string|max:100',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ];
}
