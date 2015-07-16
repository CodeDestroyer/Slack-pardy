<?php
/**
 * Created by PhpStorm.
 * User: pcunningham
 * Date: 7/16/15
 * Time: 2:10 PM
 */

namespace App\Services;
use App\Models\Category;
use Cache;

class BoardService
{

    public function getGameBoard($boardKey) {

        $board = Cache::get($boardKey, array());
        if(empty($board)){
            $categories = Category::active()->random()->take(6)->get();
            foreach ($categories as $category){
                $board[] = array(
                    "id" => $category->id,
                    "name" => $category->name,
                    "available" => array(200,400,600,800,1000)
                );
            }
            Cache::forever($boardKey, $board);
        }
        return $board;
    }

}