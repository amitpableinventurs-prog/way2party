<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\MembershipPlan;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Throwable;

class MembershipController extends Controller
{
    public function index()
    {
        $user = AppUser::with('membershipPlan.features')->find(Auth::guard('appuser')->user()->id);
        $plans = MembershipPlan::active()->with('features')->orderBy('sort_order')->get();
        $currency = Setting::first(['currency_sybmol'])->currency_sybmol ?? '';
        return view('frontend.membership.index', compact('user', 'plans', 'currency'));
    }

    public function upgrade(MembershipPlan $plan)
    {
        $user = AppUser::find(Auth::guard('appuser')->user()->id);

        if ($user->membership_plan_id == $plan->id && (!$user->membership_expires_at || $user->membership_expires_at->isFuture())) {
            return redirect()->back()->with('error_msg', __('You are already on this plan.'));
        }

        if ($plan->price > 0) {
            if ($user->balance < $plan->price) {
                return redirect()->back()->with('error_msg', __('Insufficient wallet balance. Please add funds first.'));
            }
            try {
                $user->withdraw($plan->price, ['note' => 'Membership upgrade: ' . $plan->name]);
            } catch (Throwable $th) {
                return redirect()->back()->with('error_msg', __('Unable to process payment. Please try again.'));
            }
        }

        $user->update([
            'membership_plan_id' => $plan->id,
            'membership_expires_at' => $plan->duration_days ? now()->addDays($plan->duration_days) : null,
        ]);

        return redirect()->route('myMembership')->with('success', __('Membership upgraded successfully.'));
    }
}
