<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoreController extends Controller
{
    public function scores($slug){
        try {
            $scores = DB::table('scores')
                ->join('game_versions', 'scores.game_version_id', '=', 'game_versions.id')
                ->join('games', 'game_versions.game_id', '=', 'games.id')
                ->join('users', 'scores.user_id', '=', 'users.id')
                ->where('games.slug', $slug)
                ->select(
                    'users.username',
                    DB::raw('MAX(scores.score) as max_score'),
                    DB::raw('MAX(scores.created_at) as last_played')
                )
                ->groupBy('users.id', 'users.username')
                ->orderByDesc('max_score')
                ->get()
                ->map(function ($row) {
                    return [
                        "username"  => $row->username,
                        "score"     => (int) $row->max_score,
                        "timestamp" => \Carbon\Carbon::parse($row->last_played)->toISOString()
                    ];
                });

            return response()->json([
                "scores" => $scores
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => "Failed to load scores",
                "debug" => config("app.debug") ? $th->getMessage() : null
            ], 500);
        }
    }

    public function storeScore(Request $request, $slug){
        try {
            $request->validate([
                "score" => "required|integer|min:0"
            ]);

            $user = $request->user();

            $game = Game::where('slug', $slug)->firstOrFail();

            $latestVersion = $game->gameVersion()
                ->orderByDesc('id')
                ->first();

            if (!$latestVersion) {
                return response()->json([
                    "status" => "error",
                    "message" => "Game has no versions"
                ], 400);
            }

            Score::create([
                "user_id" => $user->id,
                "game_version_id" => $latestVersion->id,
                "score" => $request->score
            ]);

            return response()->json([
                "status" => "success"
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => "Failed to submit score",
                "debug" => config("app.debug") ? $th->getMessage() : null
            ], 500);
        }
    }

}
