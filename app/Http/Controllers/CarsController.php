<?php

namespace App\Http\Controllers;

use App\Car;
use App\Http\Requests\CarRequest;
use App\Http\Resources\CarResource;

class CarsController extends Controller
{
    public function index() {
        $cars = Car::orderBy('name')->get();

        return CarResource::collection($cars);
    }

    public function store(CarRequest $request) {
        return CarResource::make($request->store());
    }

    public function update(CarRequest $request, Car $car) {
        $car = $request->update($car);

        return CarResource::make($car);
    }

    public function destroy(Car $car) {
        $car->delete();
    }
}
