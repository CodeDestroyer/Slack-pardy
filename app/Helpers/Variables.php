<?php
namespace App\Helpers;


class Variables  {

    public function assignIfNotEmpty(&$item, $default)
    {
        return (!empty($item)) ? $item : $default;
    }

}