<?php

namespace App\Models;



class User{
    protected $name;
    protected $score;

    public function __construct($name)
    {
        $this->name = $name;
        $this->score = 0;
    }

    public function setScore($score)
    {
        $this->score = $score;
    }

    public function getScore()
    {
        return $this->score;
    }

    public function getName()
    {
        return $this->name;
    }

    public function updateScore($value)
    {
        $this->score += $value;
    }
}

