<?php

namespace App\Http\Controllers;

use App\Models\City;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('city_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $city = City::orderBy('id', 'DESC')->get();
        return view('admin.city.index', compact('city'));
    }

    public function create()
    {
        abort_if(Gate::denies('city_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.city.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('city_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $request->validate([
            'name' => 'bail|required',
            'status' => 'bail|required',
            'image' => 'bail|nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
        ]);
        $data = $request->only('name', 'status');
        if ($request->hasFile('image')) {
            $data['image'] = (new AppHelper)->saveImage($request);
        }
        City::create($data);
        return redirect()->route('city.index')->withStatus(__('City has added successfully.'));
    }

    public function edit(City $city)
    {
        abort_if(Gate::denies('city_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.city.edit', compact('city'));
    }

    public function update(Request $request, City $city)
    {
        abort_if(Gate::denies('city_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $request->validate([
            'name' => 'bail|required',
            'status' => 'bail|required',
            'image' => 'bail|nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
        ]);
        $data = $request->only('name', 'status');
        if ($request->hasFile('image')) {
            (new AppHelper)->deleteFile($city->image);
            $data['image'] = (new AppHelper)->saveImage($request);
        }
        $city->update($data);
        return redirect()->route('city.index')->withStatus(__('City has updated successfully.'));
    }
}
