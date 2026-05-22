<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->simplePaginate(50);

        return view('system.users.index', compact('users'));
    }

    public function create()
    {
        return view('system.users.create', [
            'user' => new User(['role' => 'apartments']),
            'roleOptions' => User::roleOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $this->validateRequest($request);
        $attributes['password'] = $request->password;

        User::create($attributes);

        return redirect()->route('system.users.index')->with('status', 'Userul a fost adaugat.');
    }

    public function show(User $user)
    {
        return redirect()->route('system.users.edit', $user);
    }

    public function edit(User $user)
    {
        return view('system.users.edit', [
            'user' => $user,
            'roleOptions' => User::roleOptions(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $attributes = $this->validateRequest($request, $user);

        if ($request->filled('password')) {
            $attributes['password'] = $request->password;
        }

        $user->update($attributes);

        return redirect()->route('system.users.index')->with('status', 'Userul a fost modificat.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Nu poti sterge propriul user.');
        }

        $user->delete();

        return back()->with('status', 'Userul a fost sters.');
    }

    protected function validateRequest(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(array_keys(User::roleOptions()))],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
        ]);
    }
}
