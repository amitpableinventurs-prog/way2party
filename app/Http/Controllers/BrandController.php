<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::enabled()->get();
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:20',
            'url' => 'nullable|url|max:255',
            'logo_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:512',
            'status' => 'nullable|boolean',
        ]);

        $brand = new Brand();
        if ($request->hasFile('logo_image')) {
            $name = uniqid() . '.' . $request->file('logo_image')->getClientOriginalExtension();
            $destinationPath = public_path('/images/brands');
            $request->file('logo_image')->move($destinationPath, $name);
            $brand->logo_image = $name;
        }

        $brand->name = $validated['name'];
        $brand->url = $validated['url'];
        if (isset($validated['status'])) {
            $brand->status = $validated['status'];
        }
        $brand->save();
        return redirect()->route('brands.index')->withStatus(__('Brand has added successfully.'));
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:20',
            'url' => 'nullable|url|max:255',
            'logo_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:512',
            'status' => 'nullable|boolean',
        ]);


        $brand = Brand::findOrFail($id);
        if ($request->hasFile('logo_image')) {
            if ($brand->logo_image) {
                $filePath = public_path('images/brands/' . $brand->logo_image);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $name = uniqid() . '.' . $request->file('logo_image')->getClientOriginalExtension();
            $destinationPath = public_path('/images/brands');
            $request->file('logo_image')->move($destinationPath, $name);
            $brand->logo_image = $name;
        } else {
            unset($validated['logo_image']);
        }

        $brand->update($validated);
        return redirect()->route('brands.index')->withStatus(__('Brand has updated successfully.'));
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        if ($brand->logo_image) {
            $filePath = public_path('images/brands/' . $brand->logo_image);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        $brand->delete();
        return response()->json(['success' => true, 'message' => __('Brand has deleted successfully.')]);
    }
}
