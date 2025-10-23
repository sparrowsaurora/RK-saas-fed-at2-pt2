<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Controllers\Controller;
use App\Models\Joke;
use App\Models\JokeReaction;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;

class JokeReactionController extends Controller
{
    public function store(Request $request, string $id)
    {
        $validated = $request->validate([
            'is_positive' => ['required', 'boolean'],
        ]);

        $userId = $request->user()->id;
        $isPositive = $validated['is_positive'];

        $joke = Joke::findOrFail($id);

        $existing = JokeReaction::where('user_id', $userId)
            ->where('joke_id', $id)
            ->first();

        if (!$existing) {
            // New reaction
            JokeReaction::create([
                'user_id' => $userId,
                'joke_id' => $id,
                'is_positive' => $isPositive,
            ]);

            // Update joke counts
            if ($isPositive) {
                $joke->increment('positive_count');
            } else {
                $joke->increment('negative_count');
            }

            return ApiResponse::success($joke->fresh(), 'Reaction added');
        }

        if ($existing->is_positive === $isPositive) {
            // Same reaction → remove it (toggle off)
            $existing->delete();

            // Decrease the corresponding counter
            if ($isPositive) {
                $joke->decrement('positive_count');
            } else {
                $joke->decrement('negative_count');
            }

            return ApiResponse::success($joke->fresh(), 'Reaction removed');
        }

        // Different reaction → switch from + to -, or vice versa
        if ($isPositive) {
            $joke->increment('positive_count');
            $joke->decrement('negative_count');
        } else {
            $joke->increment('negative_count');
            $joke->decrement('positive_count');
        }

        $existing->update(['is_positive' => $isPositive]);

        return ApiResponse::success($joke->fresh(), 'Reaction updated');
    }
}
