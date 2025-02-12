<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreUserRequest;
use App\Http\Requests\Web\LoginUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth', only: ['profile', 'regenerateToken']),
        ];
    }

    /**
     * Show the home page with login form.
     */
    public function home()
    {
        return view('home');
    }

    /**
     * Handle user login.
     */
    public function login(LoginUserRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('profile');
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->onlyInput('email');
    }

    /**
     * Show registration form.
     */
    public function showRegisterForm()
    {
        return view('register');
    }

    /**
     * Handle user registration.
     */
    public function register(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('web_gen')->plainTextToken;
        Auth::login($user);

        return redirect()->route('profile')->with('token', $token);
    }

    /**
     * Show the user's profile with an option to regenerate the API token.
     */
    public function profile()
    {
        return view('profile', ['user' => Auth::user()]);
    }

    /**
     * Regenerate API token for authenticated user.
     */
    public function regenerateToken()
    {
        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('web_regen')->plainTextToken;
        
        return back()->with('token', $token);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
