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
        Log::error($text);
        $channel = $this->request->get('channel_name');
        $channelID = $this->request->get('channel_id');
        if (preg_match('/new game/i', $text)) {
            $this->_gameService->createNewGame($this->request);
        } else if (preg_match('/join game/i', $text))
        {
            $this->_gameService->joinGame($this->request);
        }

    }

    public function ping(){
        $text = array ("text"=>$this->request->all());
        return Response::json($text);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
