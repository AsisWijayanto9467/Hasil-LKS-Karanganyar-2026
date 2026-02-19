<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class GameController extends Controller
{
    private function convertMessage($msg)
    {
        if (str_contains($msg, 'required')) {
            return 'required';
        }

        if (str_contains($msg, 'at least')) {
            preg_match('/\d+/', $msg, $m);
            return 'must be at least ' . ($m[0] ?? '') . ' characters long';
        }

        if (str_contains($msg, 'may not be greater')) {
            preg_match('/\d+/', $msg, $m);
            return 'must be at most ' . ($m[0] ?? '') . ' characters long';
        }

        if (str_contains($msg, 'has already been taken')) {
            return 'already exists';
        }

        return $msg;
    }

    public function getGames(Request $request)
    {
        // ========================
        // Query Params (default)
        // ========================
        $page    = max((int) $request->query('page', 0), 0);
        $size    = max((int) $request->query('size', 10), 1);
        $sortBy  = $request->query('sortBy', 'title');
        $sortDir = $request->query('sortDir', 'asc') === 'desc' ? 'desc' : 'asc';

        // ========================
        // Base Query
        // ========================
        $query = Game::query()
            // ❗ WAJIB punya version
            ->whereHas('gameVersion')
            ->with(['user:id,username'])
            ->select('games.*')
            ->withCount([
                // popular → jumlah score semua version
                'gameVersion as score_count' => function ($q) {
                    $q->join('scores', 'scores.game_version_id', '=', 'game_versions.id');
                }
            ])
            ->addSelect([
                // uploadTimestamp → latest version
                'latest_upload' => DB::table('game_versions')
                    ->select('created_at')
                    ->whereColumn('game_versions.game_id', 'games.id')
                    ->latest('created_at')
                    ->limit(1)
            ]);

        // ========================
        // Sorting
        // ========================
        match ($sortBy) {
            'popular' => $query->orderBy('score_count', $sortDir),
            'uploaddate' => $query->orderBy('latest_upload', $sortDir),
            default => $query->orderBy('title', $sortDir),
        };

        // ========================
        // Pagination
        // ========================
        $totalElements = $query->count();

        $games = $query
            ->skip($page * $size)
            ->take($size)
            ->get();

        // ========================
        // Mapping Response
        // ========================
        $content = $games->map(function ($game) {
            $latestVersion = $game->gameVersion()
                ->latest('created_at')
                ->first();

            return [
                'slug'            => $game->slug,
                'title'           => $game->title,
                'description'     => $game->description,
                'thumbnail'       => "/games/{$game->slug}/{$latestVersion->version}/thumbnail.png",
                'uploadTimestamp' => $latestVersion->created_at->toISOString(),
                'author'          => $game->user->username,
                'scoreCount'      => (int) $game->score_count
            ];
        });

        return response()->json([
            'page'          => $page,
            'size'          => $content->count(),
            'totalElements' => $totalElements,
            'content'       => $content
        ], 200);
    }

    public function storeGames(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'title'       => 'required|string|min:3|max:60',
                'description' => 'required|string|min:0|max:200',
            ]);

            if ($validator->fails()) {

                $violations = [];

                foreach ($validator->errors()->messages() as $field => $messages) {
                    $violations[$field] = [
                        "message" => $this->convertMessage($messages[0])
                    ];
                }

                return response()->json([
                    "status" => "invalid",
                    "message" => "Request body is not valid.",
                    "violations" => $violations
                ], 400);
            }

            $data = $validator->validated();

            // ========================
            // Generate slug
            // ========================
            $slug = Str::slug($data['title']);

            // ========================
            // Check slug uniqueness
            // ========================
            if (Game::where('slug', $slug)->exists()) {
                return response()->json([
                    "status" => "invalid",
                    "message" => "Request body is not valid.",
                    "violations" => [
                        "title" => [
                            "message" => "already exists"
                        ]
                    ]
                ], 400);
            }

            $game = Game::create([
                'title'       => $data['title'],
                'slug'        => $slug,
                'description' => $data['description'],
                'created_by'  => $request->user()->id,
            ]);

            return response()->json([
                "status" => "success",
                "slug" => $game->slug
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create game',
                'error'   => config('app.debug') ? $th->getMessage() : null
            ], 500);
        }
    }

    public function showGame($slug){
        try {
            $game = Game::where('slug', $slug)
                ->whereHas('gameVersion')
                ->with([
                    'user:id,username',
                    'gameVersion' => function ($q) {
                        $q->latest('created_at');
                    }
                ])
                ->first();

            if (!$game) {
                return response()->json([
                    'status'  => 'not-found',
                    'message' => 'Game not found'
                ], 404);
            }

            $latestVersion = $game->gameVersion->first();

            $scoreCount = DB::table('scores')
                ->join('game_versions', 'scores.game_version_id', '=', 'game_versions.id')
                ->where('game_versions.game_id', $game->id)
                ->count();

            $thumbnailPath = "/games/{$game->slug}/{$latestVersion->version}/thumbnail.png";

            $thumbnail = file_exists(public_path($thumbnailPath))
                ? $thumbnailPath
                : null;

            return response()->json([
                'slug'            => $game->slug,
                'title'           => $game->title,
                'description'     => $game->description,
                'thumbnail'       => $thumbnail,
                'uploadTimestamp' => $latestVersion->created_at->toISOString(),
                'author'          => $game->user->username,
                'scoreCount'      => $scoreCount,
                'gamePath'        => "/games/{$game->slug}/{$latestVersion->version}/"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => "failed to get games data",
                "debug" => config("app.debug") ? $th->getMessage() : null
            ]);
        }
    }

    public function upload(Request $request, $slug)
    {
        try {
            // =========================
            // VALIDASI FORM
            // =========================
            if (!$request->hasFile('zipfile')) {
                return response("Missing zipfile", 400)
                    ->header('Content-Type', 'text/plain');
            }

            if (!$request->input('token')) {
                return response("Missing token", 401)
                    ->header('Content-Type', 'text/plain');
            }
            
            $accessToken = PersonalAccessToken::findToken($request->input('token'));

            if (!$accessToken) {
                return response("Invalid token", 401)
                    ->header('Content-Type', 'text/plain');
            }

            $user = $accessToken->tokenable;

            $game = Game::where('slug', $slug)->first();

            if (!$game) {
                return response("Game not found", 404)
                    ->header('Content-Type', 'text/plain');
            }

            if ($game->created_by !== $user->id) {
                return response("User is not author of the game", 403)
                    ->header('Content-Type', 'text/plain');
            }

            // =========================
            // HITUNG VERSI
            // =========================
            $lastVersion = GameVersion::where('game_id', $game->id)
                ->selectRaw('MAX(CAST(version AS UNSIGNED)) as max_version')
                ->value('max_version');

            $nextVersion = ($lastVersion ?? 0) + 1;


            // =========================
            // SIMPAN FILE
            // =========================
            $file = $request->file('zipfile');

            if ($file->getClientOriginalExtension() !== 'zip') {
                return response("Only zip files allowed", 400)
                    ->header('Content-Type', 'text/plain');
            }

            $storagePath = "games/{$game->slug}/{$nextVersion}";
            $filename = "game.zip";

            $file->move(public_path($storagePath), $filename);

            // =========================
            // SIMPAN DB
            // =========================
            GameVersion::create([
                'game_id'      => $game->id,
                'version'      => $nextVersion,
                'storage_path' => "/{$storagePath}/{$filename}"
            ]);

            // =========================
            // RESPONSE SUKSES (TEXT)
            // =========================
            return response("Upload success", 201)
                ->header('Content-Type', 'text/plain');
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => "failed to upload the latest game version",
                "debug" => config("app.debug") ? $th->getMessage() : null
            ]);
        }
    }

    public function update(Request $request, $slug){
        try {
            $user = $request->user();

            // =========================
            // FIND GAME
            // =========================
            $game = Game::where('slug', $slug)->first();

            if (!$game) {
                return response()->json([
                    "status" => "not_found",
                    "message" => "Game not found"
                ], 404);
            }

            if ($game->created_by !== $user->id) {
                return response()->json([
                    "status" => "forbidden",
                    "message" => "You are not the game author"
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title'       => 'required|string|min:3|max:60',
                'description' => 'required|string|min:0|max:200',
            ]);

            if ($validator->fails()) {
                $violations = [];

                foreach ($validator->errors()->messages() as $field => $messages) {
                    $violations[$field] = [
                        "message" => $this->convertMessage($messages[0])
                    ];
                }

                return response()->json([
                    "status" => "invalid",
                    "message" => "Request body is not valid.",
                    "violations" => $violations
                ], 400);
            }

            $data = $validator->validated();

            $game->update([
                'title'       => $data['title'],
                'description' => $data['description'],
            ]);

            return response()->json([
                "status" => "success"
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => "failed to update games data",
                "debug" => config("app.debug") ? $th->getMessage() : null
            ], 500);
        }
    }

    public function destroy(Request $request, $slug){
        try {
            $user = $request->user();
            $game = Game::where('slug', $slug)->first();

            if (!$game) {
                return response('', 404);
            }

            if ($game->created_by !== $user->id) {
                return response()->json([
                    'status'  => 'forbidden',
                    'message' => 'You are not the game author'
                ], 403);
            }

            $game->delete();

            return response('', 204);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => "failed to delete games data",
                "debug" => config("app.debug") ? $th->getMessage() : null
            ]);
        }
    }
}
