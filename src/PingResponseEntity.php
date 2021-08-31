<?php

namespace jovixv\Ping;

class PingResponseEntity
{
    public $address;

    public $averageDelay;

    public $packetSize;

    public $sourceIp;

    /**
     * @var array|PacketResponseEntity[]
     */
    public $packets = [];
}
