<?php

namespace App\Http\Controllers;

use App\Models\ESignature;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ESignatureController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        $signatures = ESignature::with(['user'])
            ->where('organization_id', $currentUser->organization_id)
            ->latest('signed_at')
            ->paginate(50);

        return view('admin.e-signatures', compact('signatures'));
    }

    public function sign(Request $request)
    {
        $validated = $request->validate([
            'record_type' => 'required|string',
            'record_id' => 'required|integer',
            'action' => 'required|string',
            'password' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        try {
            $signature = ESignature::createSignature(
                auth()->user(),
                $validated['record_type'],
                $validated['record_id'],
                $validated['action'],
                $validated['password'],
                $validated['reason'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'E-signature recorded successfully',
                'signature' => $signature,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
