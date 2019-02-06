<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Game as GameResource;
use App\Game as Game;
use App\User as User;
use Illuminate\Support\Facades\DB;


class GameControllerAPI extends Controller
{

    public function index()
    {

    }

    public function store(Request $request)
    {

        $game = request('game');

        //$user = User::where('nickname', $game["createdBy"])->firstOrFail();


        //$createdID = $user->id;

        $createdID = User::where('nickname', $game["createdBy"])->firstOrFail()->id;
        $winnerID = User::where('nickname', $game["winner"])->firstOrFail()->id;

        $nrOfPlayers = sizeof($game['players']);


        if ($nrOfPlayers == 1) {
            $type = Game::TYPE_SINGLEPLAYER;
        } else {
            $type = Game::TYPE_MULTIPLAYER;
        }


        $gameFinal = Game::create([
            'type' => "{$type}",
            'status' => 'terminated',
            'winner' => "{$winnerID}",
            'total_players' => "{$nrOfPlayers}",
            'created_by' => "{$createdID}"
        ]);

        $playersID = [];
        //Iterar games e ir buscar o userID

        for ($i = 0; $i < $nrOfPlayers; $i++) {
            $playersID[$i] = User::where('nickname', $game["players"][$i])->firstOrFail()->id;
        }

        $gameFinal->users()->attach($playersID);

        return response()->json($nrOfPlayers, 200);
    }
    //admin statistics
    //lista completa e ordenável de jogadores com info sobre o total de jogos (sing, mult e total) e vitorias de cada um
    //historico dos totais de jogos jogados em cada dia

    public function playerStatistics($nickname)
    {

        $user = User::where('nickname', $nickname)->firstOrFail();

        $playerID = $user->id;

        $playerMultiTotals = $user->games()->where('type', Game::TYPE_MULTIPLAYER)->count();

        $playerSingleTotals = $user->games()->where('type', Game::TYPE_SINGLEPLAYER)->count();

        //both should work.
        //$playerTotalVictories = Game::where('winner', $playerID)->count();
        $playerTotalVictories = $user->games()->where('winner', $playerID)->count();


        return response()->json([
            'playerMultiTotals' => $playerMultiTotals,
            'playerSingleTotals' => $playerSingleTotals,
            'playerTotalGames' => $user->games()->count(),
            'playerTotalVictories' => $playerTotalVictories
        ], 200);

    }

    public function publicStatistics()
    {
        $multiTotals = Game::where('type', Game::TYPE_MULTIPLAYER)->count();
        $singleTotals = Game::where('type', Game::TYPE_SINGLEPLAYER)->count();

        $totals = Game::count();


        //$top3ids = Game::groupBy('winner')->count()->take(3)->findOrFail('winner');
        $top3ids = Game::select(DB::raw("COUNT(*) as count, winner"))->groupBy('winner')->orderBy('count', 'desc')->take(3)->get();
        $top3 = [];

        for ($i = 0; $i < sizeof($top3ids); $i++) {
            $top3[$i] = User::withTrashed()->where('id', $top3ids[$i]['winner'])->firstOrFail()->nickname;
        }

        /*foreach ($top3ids as $key=>$id) {
            $top3[$key] = User::where('id', $top3ids['winner'])->firstOrFail()->nickname;
        }*/

        return response()->json([
            'multiTotals' => $multiTotals,
            'singleTotals' => $singleTotals,
            'totals' => $totals,
            'top3' => $top3
        ], 200);

    }

    public function adminStatistics(Request $request)
    {

        $userCollection = User::withTrashed()->where('admin', User::IS_NOT_ADMIN)->get();
        //$user = User::where('nickname',$nickname)->firstOrFail();

        $userArray = [];
        foreach ($userCollection as $user) {
            $playerID = $user->id;
            $playerMultiTotals = $user->games()->where('type', Game::TYPE_MULTIPLAYER)->count();
            $playerSingleTotals = $user->games()->where('type', Game::TYPE_SINGLEPLAYER)->count();
            $playerTotalVictories = $user->games()->where('winner', $playerID)->count();


            $userArray[] = ['playerMultiTotals' => $playerMultiTotals,
                'playerSingleTotals' => $playerSingleTotals,
                'playerTotalGames' => $user->games()->count(),
                'playerTotalVictories' => $playerTotalVictories,
                'playerName' => $user->name,
                'playerNickname' => $user->nickname

            ];
        }
        $gamesCollectionByDay = Game::select(DB::raw("COUNT(*) as count, DATE_FORMAT(created_at, '%d/%m/%Y') as date"))->groupBy('date')->orderBy('date', 'asc')->get();

        $dateArray = [];
        $countArray = [];
        foreach ($gamesCollectionByDay as $date) {
            $dateArray[] = $date->date;
            $countArray[] = $date->count;
        }


        //TODO paginate collection if time available.
        /*$userCollection = collect($userArray);
        if ($request->has('page')) {
            return $userCollection->paginate(10);
        }
        return response()->json($userCollection->all(), 200);*/

        //return response()->json(['users' => $userArray, 'chartsDate' => $dateArray, 'chartsTotals' => $countArray], 200);
        return response()->json(['users' => $userArray, 'chartsData' => $gamesCollectionByDay, 'chartsDate' => $dateArray, 'chartsTotal' => $countArray], 200);

    }
}

