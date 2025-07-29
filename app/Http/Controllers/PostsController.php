<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Routing\Controller;

class PostsController extends Controller
{
    public function index(Request $request): LengthAwarePaginator
    {
        $perPage = $request->query('per_page', 10);
        $query = Post::with('user')->latest();

        $filterable = [
            'user_id' => 'where',
            'created_at' => 'whereDate',
            'status' => 'where',
            'title' => 'whereLike',
        ];

        foreach ($filterable as $field => $method) {
            if ($request->filled($field)) {
                $value = $request->query($field);

                switch ($method) {
                    case 'where':
                        $query->where($field, $value);
                        break;

                    case 'whereDate':
                        $query->whereDate($field, $value);
                        break;

                    case 'whereLike':
                        $query->where($field, 'LIKE', "%$value%");
                        break;
                }
            }
        }

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

        return response()->json($post, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'body' => 'sometimes|required|string|max:255',
        ]);

        $post->update($data);

        return response()->json($post);
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
