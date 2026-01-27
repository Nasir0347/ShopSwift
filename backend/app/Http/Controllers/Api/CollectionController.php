<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Http\Resources\CollectionResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $collections = Collection::all();
        return $this->success(CollectionResource::collection($collections));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'in:manual,smart',
        ]);

        $collection = Collection::create([
            'title' => $validated['title'],
            'handle' => Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'] ?? 'manual',
        ]);

        return $this->success(new CollectionResource($collection), 'Collection created', 201);
    }

    public function show($id)
    {
        $collection = Collection::find($id);
        if (!$collection) return $this->error('Collection not found', 404);
        return $this->success(new CollectionResource($collection));
    }

    public function update(Request $request, $id)
    {
        $collection = Collection::find($id);
        if (!$collection) return $this->error('Collection not found', 404);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'in:manual,smart',
        ]);

        if (isset($validated['title'])) {
            $collection->title = $validated['title'];
            $collection->handle = Str::slug($validated['title']);
        }
        if (array_key_exists('description', $validated)) $collection->description = $validated['description'];
        if (isset($validated['type'])) $collection->type = $validated['type'];

        $collection->save();

        return $this->success(new CollectionResource($collection), 'Collection updated');
    }

    public function destroy($id)
    {
        $collection = Collection::find($id);
        if (!$collection) return $this->error('Collection not found', 404);
        $collection->delete();
        return $this->success([], 'Collection deleted');
    }
}
