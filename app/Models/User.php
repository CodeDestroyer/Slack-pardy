<?php

namespace App\Models;



class User{
    protected $name;
    protected $score;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setScore($score)
    {
        $this->score = $score;
    }

    public function getScore()
    {
        return $this->score;
    }
}

