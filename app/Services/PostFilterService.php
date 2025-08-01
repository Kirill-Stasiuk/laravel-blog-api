<?php
namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PostFilterService
{
    protected array $filterable = [
        'user_id' => 'where',
        'created_at' => 'whereDate',
        'status' => 'where',
        'title' => 'whereLike',
    ];

    public function apply(Request $request, Builder $query): Builder
    {
        foreach ($this->filterable as $field => $method) {
            if ($request->filled($field)) {
                $value = $request->query($field);

                match ($method) {
                    'where' => $query->where($field, $value),
                    'whereDate' => $query->whereDate($field, $value),
                    'whereLike' => $query->where($field, 'LIKE', "%{$value}%"),
                    default => null
                };
            }
        }

        return $query;
    }
}
