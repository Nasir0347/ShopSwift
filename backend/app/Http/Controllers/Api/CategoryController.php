<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        return $this->success(CategoryResource::collection($categories));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|url',
            'description' => 'nullable|string'
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
            'image' => $validated['image'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return $this->success(new CategoryResource($category), 'Category created', 201);
    }

    public function show($id)
    {
        $category = Category::with('children')->find($id);
        if (!$category) return $this->error('Category not found', 404);
        return $this->success(new CategoryResource($category));
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) return $this->error('Category not found', 404);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|url',
            'description' => 'nullable|string'
        ]);

        if (isset($validated['name'])) {
            $category->name = $validated['name'];
            $category->slug = Str::slug($validated['name']);
        }
        if (array_key_exists('parent_id', $validated)) $category->parent_id = $validated['parent_id'];
        if (array_key_exists('image', $validated)) $category->image = $validated['image'];
        if (array_key_exists('description', $validated)) $category->description = $validated['description'];

        $category->save();

        return $this->success(new CategoryResource($category), 'Category updated');
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) return $this->error('Category not found', 404);
        $category->delete();
        return $this->success([], 'Category deleted');
    }
}
