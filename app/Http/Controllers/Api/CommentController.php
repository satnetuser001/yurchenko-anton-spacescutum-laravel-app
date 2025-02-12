<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CommentController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paginatedComments = Comment::paginate(5);

        // Add _links for each comment
        $commentsWithLinks = $paginatedComments->getCollection()->map(function ($comment) {
            return [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'product_id' => $comment->product_id,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                '_links' => [
                    'self' => [
                        'href' => route('comments.show', ['comment' => $comment->id]),
                    ],
                    'update' => [
                        'href' => route('comments.update', ['comment' => $comment->id]),
                        'method' => 'PUT',
                    ],
                    'delete' => [
                        'href' => route('comments.destroy', ['comment' => $comment->id]),
                        'method' => 'DELETE',
                    ],
                ],
            ];
        });

        // Replace the original collection with a new one with _links
        $paginatedComments->setCollection($commentsWithLinks);

        return response()->json($paginatedComments, Response::HTTP_OK)
            ->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'content' => 'required|string',
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'product_id' => $validated['product_id'],
            'content' => $validated['content'],
        ]);

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'product_id' => $comment->product_id,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                '_links' => [
                    'self' => [
                        'href' => route('comments.show', ['comment' => $comment->id]),
                    ],
                    'update' => [
                        'href' => route('comments.update', ['comment' => $comment->id]),
                        'method' => 'PUT',
                    ],
                    'delete' => [
                        'href' => route('comments.destroy', ['comment' => $comment->id]),
                        'method' => 'DELETE',
                    ],
                ],
            ],
        ], Response::HTTP_CREATED)->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        return response()->json([
            'id' => $comment->id,
            'user_id' => $comment->user_id,
            'product_id' => $comment->product_id,
            'content' => $comment->content,
            'created_at' => $comment->created_at,
            'updated_at' => $comment->updated_at,
            '_links' => [
                'self' => [
                    'href' => route('comments.show', ['comment' => $comment->id]),
                ],
                'update' => [
                    'href' => route('comments.update', ['comment' => $comment->id]),
                    'method' => 'PUT',
                ],
                'delete' => [
                    'href' => route('comments.destroy', ['comment' => $comment->id]),
                    'method' => 'DELETE',
                ],
            ],
        ], Response::HTTP_OK)->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update($validated);
        
        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'product_id' => $comment->product_id,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                '_links' => [
                    'self' => [
                        'href' => route('comments.show', ['comment' => $comment->id]),
                    ],
                    'update' => [
                        'href' => route('comments.update', ['comment' => $comment->id]),
                        'method' => 'PUT',
                    ],
                    'delete' => [
                        'href' => route('comments.destroy', ['comment' => $comment->id]),
                        'method' => 'DELETE',
                    ],
                ],
            ],
        ], Response::HTTP_OK)->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
