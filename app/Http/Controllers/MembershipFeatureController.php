<?php

namespace App\Http\Controllers;

use App\Models\MembershipFeature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MembershipFeatureController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_if(!Auth::user()->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');
            return $next($request);
        });
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'bail|required|string|max:255|unique:membership_features,name',
        ]);
        MembershipFeature::create(['name' => $request->name]);
        return redirect()->back()->withStatus(__('Feature added successfully.'));
    }

    public function destroy(MembershipFeature $membershipFeature)
    {
        $membershipFeature->delete();
        return redirect()->back()->withStatus(__('Feature removed.'));
    }
}
