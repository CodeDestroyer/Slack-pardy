<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Services\GameMasterService;
use DB;
use Illuminate\Http\Request;
use Log;
use Response;

class GameHandler extends Controller
{
    protected $request;
    protected $_gameService;

    public function __construct(Request $request, GameMasterService $gameService){
        $this->request = $request;
        $this->_gameService = $gameService;
        $this->middleware('trigger.filter');
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $matches = array();
        $text = $this->request->get('text');
        if (preg_match('/new game/i', $text)) {
            $this->_gameService->createNewGame();
        } else if (preg_match('/join game/i', $text)) {
            $this->_gameService->joinGame();
        }
        else if (preg_match('/pick (\d+) (\d+)/i', $text,$matches)) {
            $this->_gameService->pickQuestion($matches[1],$matches[2]);
        }


    }
    public function ping()
    {
        $this->_gameService->pickRandomBoardLeader();
    }


}
