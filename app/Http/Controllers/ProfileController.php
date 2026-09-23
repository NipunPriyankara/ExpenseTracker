<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the user profile settings page.
     */
    public function show()
    {
        $user = Auth::user();
        $stats = [
            'total_expenses' => $user->expenses()->count(),
            'total_incomes' => $user->incomes()->count(),
            'total_categories' => $user->categories()->count(),
            'member_since' => $user->created_at ? $user->created_at->format('M d, Y') : 'N/A',
        ];

        return view('profile.show', compact('user', 'stats'));
    }

    /**
     * Update user profile information (Name / Username, Email, Profile Picture).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        // Handle Avatar removal
        if ($request->boolean('remove_avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        }

        // Handle new Avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if present
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->name = trim($validated['name']);
        $user->email = strtolower(trim($validated['email']));
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile details updated successfully!');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::min(6)],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Password changed successfully!');
    }
}
