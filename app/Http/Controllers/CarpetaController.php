<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CarpetaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $carpetas = \App\Models\Carpeta::where('user_id', auth()->id())->get();

        return view('carpetas.index', compact('carpetas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('carpetas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        \App\Models\Carpeta::create([
            'nombre' => $request->nombre,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('carpetas.index')->with('success', 'Carpeta creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $carpeta = \App\Models\Carpeta::where('user_id', auth()->id())
            ->findOrFail($id);

        return view('carpetas.show', compact('carpeta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $carpeta = \App\Models\Carpeta::where('user_id', auth()->id())->findOrFail($id);

        return view('carpetas.edit', compact('carpeta'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',

        ]);

        $carpeta = \App\Models\Carpeta::where('user_id', auth()->id())->findOrFail($id);

        $carpeta->update([
            'nombre' => $request->nombre,
        ]);

        return redirect()->back()->with('success', 'Carpeta actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $carpeta = \App\Models\Carpeta::where('user_id', auth()->id())
            ->findOrFail($id);

        $carpeta->delete();

        return redirect()->route('carpetas.index');
    }
}
