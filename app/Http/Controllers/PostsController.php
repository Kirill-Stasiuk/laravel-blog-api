<?php

namespace App\Http\Controllers;

use App\Services\PostFilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Routing\Controller;

class PostsController extends Controller
{
    public function index(Request $request, PostFilterService $filterService): LengthAwarePaginator
    {
        $perPage = $request->query('per_page', 10);

        $query = Post::with('user')->latest();

        $query = $filterService->apply($request, $query);

        return $query->paginate($perPage);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string|max:255',
        ]);

        $post = Post::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'data' => $post
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'body' => 'sometimes|required|string|max:255',
        ]);

        $post->update($data);

        return response()->json([
            'data' => $post
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ], 204);
    }
}
