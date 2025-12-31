<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,pm,member', // Adjusted roles based on typical needs
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            // Generate a username from email or name
            'username' => explode('@', $request->email)[0] . rand(100,999),
        ]);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone_number' => 'nullable|string|max:20',
            'role' => 'required|in:admin,pm,member',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
             return redirect()->back()->with('error', 'You cannot delete yourself.');
        }
        
        $user->delete();
        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    // Keeping this for backward compatibility if routed, or remove if unused
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin,pm,member',
        ]);
        $user->update(['role' => $request->role]);
        return redirect()->back()->with('success', 'User role updated successfully.');
    }

    public function reset2FA(User $user)
    {
        $user->google2fa_secret = null;
        $user->save();
        
        return redirect()->back()->with('success', 'User 2FA has been reset successfully. They will need to scan the QR code again upon next login.');
    }
}
