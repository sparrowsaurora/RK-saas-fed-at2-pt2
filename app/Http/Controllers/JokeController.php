<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJokeRequest;
use App\Http\Requests\UpdateJokeRequest;
use App\Models\Joke;
use Illuminate\Http\Request;

class JokeController extends Controller
{
    public function index()
    {
        $jokes = Joke::latest()->paginate(10);
        return view('jokes.index', compact('jokes'));
    }

    public function show(Joke $joke)
    {
        return view('jokes.show', compact('joke'));
    }

    public function create()
    {
        return view('jokes.create');
    }

    public function store(StoreJokeRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        Joke::create($data);

        return redirect()->route('jokes.index')->with('success', 'Joke created successfully!');
    }

    public function edit(Joke $joke)
    {
        return view('jokes.edit', compact('joke'));
    }

    public function update(Request $request, Joke $joke)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $joke->update($validated);

        return redirect()->route('jokes.index')->with('success', 'Joke updated successfully.');
    }


    public function destroy(Joke $joke)
    {
        $joke->delete();

        return redirect()->route('jokes.index')->with('success', 'Joke deleted (soft) successfully!');
    }

    // Trash methods for managing soft deleted jokes:

    public function trash()
    {
        $jokes = Joke::onlyTrashed()->paginate(10);
        return view('jokes.trash', compact('jokes'));
    }

    public function restore($id)
    {
        $joke = Joke::onlyTrashed()->findOrFail($id);
        $joke->restore();

        return redirect()->route('jokes.trash')->with('success', 'Joke restored successfully!');
    }

    public function restoreAll()
    {
        Joke::onlyTrashed()->restore();

        return redirect()->route('jokes.trash')->with('success', 'All jokes restored!');
    }

    public function forceDelete($id)
    {
        $joke = Joke::onlyTrashed()->findOrFail($id);
        $joke->forceDelete();

        return redirect()->route('jokes.trash')->with('success', 'Joke permanently deleted!');
    }

    public function forceDeleteAll()
    {
        Joke::onlyTrashed()->forceDelete();

        return redirect()->route('jokes.trash')->with('success', 'All jokes permanently deleted!');
    }
}

