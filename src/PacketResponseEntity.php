<?php


namespace jovixv\Ping;


class PacketResponseEntity
{
    public $packet = null;

    public $delay = null;

    public $size = null;

    public $isSuccess = false;

    public $errorException;
}
