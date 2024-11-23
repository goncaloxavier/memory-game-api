<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;

class GameController extends Controller
{
    // Retrieve games created by a user
    public function getUserGames($id, Request $request)
    {
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
    
        // Start building the query
        $query = $user->createdGames();
        
        // Get the board size filter from the request
        $boardSize = $request->input('boardSize');
    
        // Apply the filter if boardSize is provided
        if ($boardSize) {
            $query->where('board_id', $boardSize);
        }
    
        // Get the paginated results
        $games = $query->paginate(10);
    
        // Return the paginated results as a JSON response
        return response()->json($games);
    }

    // Save a new game to the database
    public function saveGame(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:S,M',
            'status' => 'required|in:PE,PL,E,I',
            'began_at' => 'required|date_format:Y-m-d H:i:s',
            'ended_at' => 'required|date_format:Y-m-d H:i:s',
            'board_id' => 'required|integer|exists:boards,id',
            'created_at' => 'required|date_format:Y-m-d H:i:s',
            'total_time' => 'required|numeric',
            'created_user_id' => 'required|exists:users,id',
            'custom' => 'required|array',
            'custom.pairs_found' => 'required|integer',
            'custom.turns' => 'required|integer',
            'custom.score' => 'required|numeric',
        ]);

        $game = new Game();
        $game->type = $validated['type'];
        $game->status = $validated['status'];
        $game->began_at = $validated['began_at'];
        $game->ended_at = $validated['ended_at'];
        $game->board_id = $validated['board_id'];
        $game->created_at = $validated['created_at'];
        $game->total_time = $validated['total_time'];
        $game->created_user_id = $validated['created_user_id'];
        $game->custom = json_encode([
            'pairs_found' => $validated['custom']['pairs_found'],
            'turns' => $validated['custom']['turn'],
            'score' => $validated['custom']['score']
        ]);

        $game->save();
        return response()->json($game, 201);
    }

    public function getGlobalLeaderboard(Request $request)
    {
        // Get the sort criteria and board size directly from the request
        $sortCriteria = $request->input('sortCriteria');  // No default value
        $sortOrder = $request->input('sortOrder', 'asc');  // Default to ascending order if not provided
        $boardSize = $request->input('boardSize');  // Expecting board_id parameter
    
        // Base query
        $query = Game::with('user');
    
        // If a board size is provided, filter by board_id
        if ($boardSize) {
            $query->where('board_id', $boardSize);
        }
    
        // If sortCriteria is provided, order by that criteria
        if ($sortCriteria) {
            // Check if sortCriteria is a JSON field
            if (strpos($sortCriteria, '->') !== false) {
                // Handle JSON fields (e.g., custom->turns)
                $jsonField = explode('->', $sortCriteria);
                $jsonColumn = $jsonField[0];
                $jsonKey = $jsonField[1];
    
                $query->orderByRaw("JSON_EXTRACT($jsonColumn, '$.$jsonKey') $sortOrder");
            } else {
                // Handle regular fields (e.g., total_time)
                $query->orderBy($sortCriteria, $sortOrder);
            }
        }
    
        // Fetch the top 3 games based on the provided parameters
        $globalGames = $query->take(3)->get();
    
        // Return the leaderboard data as a JSON response
        return response()->json($globalGames, 200);
    }

    public function getPersonalLeaderboard($userId, Request $request)
    {
        // Find the user by their ID
        $user = User::find($userId);
    
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
    
        // Get the sort criteria, sort order, and board size from the request
        $sortCriteria = $request->input('sortCriteria');  // No default value, so it must be passed
        $sortOrder = $request->input('sortOrder', 'asc');  // Default to descending order if not provided
        $boardSize = $request->input('boardSize');  // Expecting board_id parameter
    
        // Base query to fetch the user's games
        $query = $user->createdGames();
    
        // If a board size is provided, filter by board_id
        if ($boardSize) {
            $query->where('board_id', $boardSize);
        }
    
        // If sortCriteria is provided, order by that criteria
        if ($sortCriteria) {
            // Check if sortCriteria is a JSON field (e.g., custom->turns)
            if (strpos($sortCriteria, '->') !== false) {
                // Handle JSON fields (e.g., custom->turns)
                $jsonField = explode('->', $sortCriteria);
                $jsonColumn = $jsonField[0];
                $jsonKey = $jsonField[1];
    
                $query->orderByRaw("JSON_EXTRACT($jsonColumn, '$.$jsonKey') $sortOrder");
            } else {
                // Handle regular fields (e.g., total_time)
                $query->orderBy($sortCriteria, $sortOrder);
            }
        }
    
        // Fetch the top 3 games based on the provided parameters
        $personalGames = $query->take(3)->get();
    
        // Return the leaderboard data as a JSON response
        return response()->json($personalGames, 200);
    }

    // Filter games by board size
    public function filterGamesByBoardSize(Request $request)
    {
        $boardId = $request->input('board_id');

        if (!$boardId) {
            return response()->json(['error' => 'Board size is required'], 400);
        }

        $filteredGames = Game::where('board_id', $boardId)->get();
        return response()->json($filteredGames, 200);
    }
}