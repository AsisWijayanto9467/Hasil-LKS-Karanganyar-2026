<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function show(Request $request, $username) {
        try {

            $viewer = $request->user();
            $user = User::where('username', $username)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'not-found',
                    'message' => 'User not found'
                ], 404);
            }

            $isSelf = $viewer && $viewer->id === $user->id;

            // =========================
            // AUTHORED GAMES
            // =========================
            $gamesQuery = $user->games()
                ->select('slug', 'title', 'description');

            if (!$isSelf) {
                $gamesQuery->whereHas('gameVersion');
            }

            $authoredGames = $gamesQuery->get();

            // =========================
            // HIGHSCORES (1 per game)
            // =========================
            $highscores = DB::table('scores')
                ->join('game_versions', 'scores.game_version_id', '=', 'game_versions.id')
                ->join('games', 'game_versions.game_id', '=', 'games.id')
                ->where('scores.user_id', $user->id)
                ->select(
                    'games.id as game_id',
                    'games.slug',
                    'games.title',
                    'games.description',
                    DB::raw('MAX(scores.score) as max_score'),
                    DB::raw('MAX(scores.created_at) as last_played')
                )
                ->groupBy('games.id', 'games.slug', 'games.title', 'games.description')
                ->get()
                ->map(function ($row) {
                    return [
                        'game' => [
                            'slug' => $row->slug,
                            'title' => $row->title,
                            'description' => $row->description,
                        ],
                        'score' => (int) $row->max_score,
                        'timestamp' => \Carbon\Carbon::parse($row->last_played)->toISOString()
                    ];
                });

            return response()->json([
                'username'             => $user->username,
                'registeredTimestamp' => $user->created_at->toISOString(),
                'authoredGames'        => $authoredGames,
                'highscores'           => $highscores
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => "menampilkan relasi gagal",
                "debug" => config("app.debug") ? $th->getMessage() : null
            ], 500);
        }
    }
}
