<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public const DEMO_ACCOUNTS = [
        'admin' => 'admin@demo.test',
        'instructor' => 'instructor@demo.test',
        'student' => 'student@demo.test',
    ];

    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => __('These credentials do not match our records.')]);
        }

        return $this->loggedIn($request);
    }

    /** One-click sign-in to the shared demo accounts, so visitors can try every role. */
    public function demo(Request $request, string $role)
    {
        abort_unless(isset(self::DEMO_ACCOUNTS[$role]), 404);

        Auth::login(User::where('email', self::DEMO_ACCOUNTS[$role])->firstOrFail());

        return $this->loggedIn($request);
    }

    public function register()
    {
        return view('auth.register');
    }

    public function storeRegistration(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:190', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        Auth::login(User::create([...$data, 'role' => 'student']));

        return $this->loggedIn($request);
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function loggedIn(Request $request)
    {
        $request->session()->regenerate();

        return redirect()->intended($request->user()->canTeach() ? route('admin.dashboard') : route('learning.dashboard'));
    }
}
