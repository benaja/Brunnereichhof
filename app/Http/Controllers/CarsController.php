<?php

namespace App\Http\Controllers;

use App\Car;
use App\Http\Requests\CarRequest;
use App\Http\Resources\CarResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarsController extends Controller
{
    public function index(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['cars_read', 'resource_planner_write']);

        $cars = Car::orderBy('name')
            ->when($request->get('search'), function ($query, $search) {
                $query->where('name', 'LIKE', "%$search%")
                    ->orWhere('number', 'LIKE', "%$search%");
            })
            ->get();

        return CarResource::collection($cars);
    }

    public function store(CarRequest $request)
    {
        auth()->user()->authorize(['superadmin'], ['cars_write']);

        return CarResource::make($request->store());
    }

    public function update(CarRequest $request, Car $car)
    {
        auth()->user()->authorize(['superadmin'], ['cars_write']);

        $car = $request->update($car);

        return CarResource::make($car);
    }

    public function destroy(Car $car)
    {
        auth()->user()->authorize(['superadmin'], ['cars_write']);

        Storage::disk('s3')->delete($car->image);

        $car->delete();
    }
}
