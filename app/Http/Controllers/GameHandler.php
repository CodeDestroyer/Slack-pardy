<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use DB;
use Response;
use App\Models\Question;
use App\Services\GameMasterService;
use App\Jobs\JoinGame;
use App\Models\Category;
use App\Http\Requests;
use Maknz\Slack\Facades\Slack;
use App\Http\Controllers\Controller;

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
        $text = $this->request->get('text');
        if (preg_match('/new game/i', $text)) {
            $this->_gameService->createNewGame($this->request);
        } else if (preg_match('/join game/i', $text)) {
            $this->_gameService->joinGame($this->request);
        }

    }


}
