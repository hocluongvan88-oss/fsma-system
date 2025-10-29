<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use Illuminate\Http\Request;

class UserPackageController extends Controller
{
    public function updatePackage(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, __('messages.unauthorized_action'));
        }

        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        try {
            $organization = $user->organization;
            if (!$organization) {
                return redirect()
                    ->back()
                    ->with('error', 'User must belong to an organization.');
            }

            $oldPackage = $organization->package_id;
            $organization->update([
                'package_id' => $request->package_id,
            ]);

            \Log::info('Organization package updated', [
                'admin_id' => auth()->id(),
                'organization_id' => $organization->id,
                'old_package' => $oldPackage,
                'new_package' => $request->package_id,
            ]);

            return redirect()
                ->back()
                ->with('success', __('messages.package_updated_successfully', [
                    'organization' => $organization->name
                ]));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('messages.package_update_failed', [
                    'error' => $e->getMessage()
                ]));
        }
    }
}
