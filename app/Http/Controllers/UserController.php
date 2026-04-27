<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;



class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view ('users.index', compact('users'));
    }
    public function create()
    {
        return view('users.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
        'name' => 'required|string|max:255',
        'role' => 'required|in:admin,worker',
        ]);

        // Generate random password
        $randomPassword = Str::random(8);

        User::create([
            'name' => $validated['name'],
            'role' => $validated['role'],
            'password' => bcrypt($randomPassword),
            'visible_password' => $randomPassword, // store plaintext (optional)
        ]);

        return redirect()->route('users.index');
    }
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,worker',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
            $user->visible_password = $validated['password'];
        }

        $user->save();

        return redirect()->route('users.index');
    }
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index');
    }

}
