<?php


namespace jovixv\Ping;


interface PingServiceContract
{
    /**
     * PingService constructor.
     * @param int $millisecondTimeout
     * @param int $packetPayloadSize bytes count of payload
     */
    public function __construct(int $millisecondTimeout, int $packetPayloadSize);

    /**
     * @param string $address
     * @param int $packageCount
     * @return PingResponseEntity
     */
    public function ping(string $address, int $packageCount = 4): PingResponseEntity;
}
