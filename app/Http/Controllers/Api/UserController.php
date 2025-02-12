<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreUserRequest;
use App\Http\Requests\API\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['store']),
        ];
    }

    /**
     * Display a list of users with pagination.
     */
    public function index()
    {
        $paginatedUsers = User::paginate(5);

        // Add _links for each user
        $usersWithLinks = $paginatedUsers->getCollection()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                '_links' => [
                    'self' => [
                        'href' => route('users.show', ['user' => $user->id]),
                    ],
                    'update' => [
                        'href' => route('users.update', ['user' => $user->id]),
                        'method' => 'PUT',
                    ],
                    'delete' => [
                        'href' => route('users.destroy', ['user' => $user->id]),
                        'method' => 'DELETE',
                    ],
                ],
            ];
        });

        // Replace the original collection with a new one with _links
        $paginatedUsers->setCollection($usersWithLinks);

        return response()->json($paginatedUsers, Response::HTTP_OK)
            ->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Create a new user.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $token = $user->createToken('API')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                '_links' => [
                    'self' => [
                        'href' => route('users.show', ['user' => $user->id]),
                    ],
                    'update' => [
                        'href' => route('users.update', ['user' => $user->id]),
                        'method' => 'PUT',
                    ],
                    'delete' => [
                        'href' => route('users.destroy', ['user' => $user->id]),
                        'method' => 'DELETE',
                    ],
                ],
            ],
            'token' => $token,
        ], Response::HTTP_CREATED)->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Display one user.
     */
    public function show(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            '_links' => [
                'self' => [
                    'href' => route('users.show', ['user' => $user->id])
                ],
                'update' => [
                    'href' => route('users.update', ['user' => $user->id]),
                    'method' => 'PUT'
                ],
                'delete' => [
                    'href' => route('users.destroy', ['user' => $user->id]),
                    'method' => 'DELETE'
                ]
            ]
        ], Response::HTTP_OK)->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Update user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                '_links' => [
                    'self' => [
                        'href' => route('users.show', ['user' => $user->id]),
                    ],
                    'update' => [
                        'href' => route('users.update', ['user' => $user->id]),
                        'method' => 'PUT',
                    ],
                    'delete' => [
                        'href' => route('users.destroy', ['user' => $user->id]),
                        'method' => 'DELETE',
                    ],
                ],
            ],
        ], Response::HTTP_OK)->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Delete user.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
