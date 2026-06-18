<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('orden')->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render('Categories/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'orden' => 'required|integer|min:0',
            'aplica_iva' => 'boolean',
        ]);

        Category::create($validated);

        return Redirect::route('categories.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Categories/Form', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'orden' => 'required|integer|min:0',
            'aplica_iva' => 'boolean',
        ]);

        $category->update($validated);

        return Redirect::route('categories.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return Redirect::route('categories.index')->with('success', 'Categoría eliminada exitosamente.');
    }
}
