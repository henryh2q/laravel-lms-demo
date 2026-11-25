<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->query('role'), fn ($q, $role) => $q->where('role', $role))
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")))
            ->withCount('enrollments')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['required', Rule::in(User::ROLES)]]);

        // Demo accounts keep their roles so the one-click logins always work.
        abort_if(str_ends_with($user->email, '@demo.test'), 422, 'Demo accounts cannot be changed.');
        abort_if($user->is($request->user()), 422, 'You cannot change your own role.');

        $user->update($data);

        return back()->with('status', "{$user->name} is now {$user->role}.");
    }
}
