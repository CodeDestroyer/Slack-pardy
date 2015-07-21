<?php
/**
 * Created by PhpStorm.
 * User: pcunningham
 * Date: 7/15/15
 * Time: 9:34 PM
 */

namespace App\Services\Contracts;


interface IMessageHandler
{
    public function sendMessage($recipient,$message);
    public function displayBoard($recipient,$categories);
    public function sendMessageMention($recipient, $user, $message);
}