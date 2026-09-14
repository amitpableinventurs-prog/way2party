<?php

namespace App\Http\Controllers;

use App\Models\MembershipFeature;
use App\Models\MembershipPlan;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_if(!Auth::user()->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');
            return $next($request);
        });
    }

    public function index()
    {
        $membershipPlans = MembershipPlan::orderBy('sort_order')->get();
        return view('admin.membershipPlan.index', compact('membershipPlans'));
    }

    public function create()
    {
        $features = MembershipFeature::orderBy('name')->get();
        return view('admin.membershipPlan.create', compact('features'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'bail|required|string|max:255',
            'amount' => 'bail|required|numeric|min:0',
            'price' => 'bail|required|numeric|min:0',
            'duration_days' => 'bail|nullable|integer|min:1',
            'image' => 'bail|nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            'description' => 'bail|nullable|string',
            'sort_order' => 'bail|nullable|integer|min:0',
            'features' => 'bail|nullable|array',
            'features.*' => 'bail|exists:membership_features,id',
        ]);
        $data = $request->except(['image', 'features', '_token']);
        $data['status'] = $request->boolean('status');
        if ($request->hasFile('image')) {
            $data['image'] = (new AppHelper)->saveImage($request);
        }
        $membershipPlan = MembershipPlan::create($data);
        $membershipPlan->features()->sync($request->input('features', []));
        return redirect()->route('membership-plan.index')->withStatus(__('Membership plan has been added successfully.'));
    }

    public function edit(MembershipPlan $membershipPlan)
    {
        $features = MembershipFeature::orderBy('name')->get();
        $selectedFeatures = $membershipPlan->features()->pluck('membership_features.id')->toArray();
        return view('admin.membershipPlan.edit', compact('membershipPlan', 'features', 'selectedFeatures'));
    }

    public function update(Request $request, MembershipPlan $membershipPlan)
    {
        $request->validate([
            'name' => 'bail|required|string|max:255',
            'amount' => 'bail|required|numeric|min:0',
            'price' => 'bail|required|numeric|min:0',
            'duration_days' => 'bail|nullable|integer|min:1',
            'image' => 'bail|nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            'description' => 'bail|nullable|string',
            'sort_order' => 'bail|nullable|integer|min:0',
            'features' => 'bail|nullable|array',
            'features.*' => 'bail|exists:membership_features,id',
        ]);
        $data = $request->except(['image', 'features', '_token', '_method']);
        $data['status'] = $request->boolean('status');
        if ($request->hasFile('image')) {
            (new AppHelper)->deleteFile($membershipPlan->image);
            $data['image'] = (new AppHelper)->saveImage($request);
        }
        $membershipPlan->update($data);
        $membershipPlan->features()->sync($request->input('features', []));
        return redirect()->route('membership-plan.index')->withStatus(__('Membership plan has been updated successfully.'));
    }

    public function destroy(MembershipPlan $membershipPlan)
    {
        try {
            $membershipPlan->delete();
            return true;
        } catch (\Throwable $th) {
            return response('Data is Connected with other Data', 400);
        }
    }
}
