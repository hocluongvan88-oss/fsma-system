<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class EmailUnsubscribeController extends BaseController
{
    /**
     * Handle email unsubscribe request
     */
    public function unsubscribe(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->view('email.unsubscribe-error', [
                'message' => 'Invalid unsubscribe link. Token is missing.',
            ], 400);
        }

        $user = User::where('email_token', $token)->first();

        if (!$user) {
            return response()->view('email.unsubscribe-error', [
                'message' => 'Invalid unsubscribe link. User not found.',
            ], 404);
        }

        $user->notificationPreferences()->update(['is_enabled' => false]);

        return response()->view('email.unsubscribe-success', [
            'email' => $user->email,
        ]);
    }
}
