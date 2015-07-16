<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use DB;
use Intervention\Image\Facades\Image;
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
        $channel = $this->request->get('channel_name');
        $channelID = $this->request->get('channel_id');
        if (preg_match('/new game/i', $text)) {
            $this->_gameService->createNewGame($this->request);
        } else if (preg_match('/join game/i', $text))
        {
            $this->_gameService->joinGame($this->request);
        }

        /**
         *
        $test = Category::active()->random()->take(6)->get();
        foreach ($test as $category){
            echo $category->name;
        }

        $job = (new JoinGame())->delay(60);

        $this->dispatch($job);
         * */

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function displayBoard()
    {
        $backDrop = public_path()."/images/backboard.jpg";
        $img = Image::make($backDrop);
        $img->text('WORLD BOOK', 15, 30, function($font) {
            $font->file(public_path()."/fonts/gyparody.ttf");
            $font->size(12);
            $font->color('#fff');

        });
        $img->text('DESCRIBES THE', 15, 45, function($font) {
            $font->file(public_path()."/fonts/gyparody.ttf");
            $font->size(12);
            $font->color('#fff');

        });
        $img->text('"G" MAN', 15, 60, function($font) {
            $font->file(public_path()."/fonts/gyparody.ttf");
            $font->size(12);
            $font->color('#fff');

        });

        $img->text('MONEY SLANG', 120, 40, function($font) {
            $font->file(public_path()."/fonts/gyparody.ttf");
            $font->size(12);
            $font->color('#fff');

        });

        $img->text('3 LITTLE LETTERS', 230, 40, function($font) {
            $font->file(public_path()."/fonts/gyparody.ttf");
            $font->size(12);
            $font->color('#fff');

        });
        $img->text('ON THE RADIO', 330, 40, function($font) {
            $font->file(public_path()."/fonts/gyparody.ttf");
            $font->size(12);
            $font->color('#fff');

        });
        $img->text('BEGINNING & END', 440, 40, function($font) {
            $font->file(public_path()."/fonts/gyparody.ttf");
            $font->size(12);
            $font->color('#fff');

        });
        $img->text('CELEB STUFF', 550, 40, function($font) {
            $font->file(public_path()."/fonts/gyparody.ttf");
            $font->size(12);
            $font->color('#fff');

        });
        return $img->response('jpg');
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
