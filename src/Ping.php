<?php

namespace jovixv\Ping;

use jovixv\Ping\Protocol\ICMP\PingService;
use jovixv\Ping\PingResponseEntity as PingResponseEntity;

class Ping
{
    /**
     * @var PingServiceContract|null $pingService
     */
    protected $pingService = null;

    /**
     * @param string $address
     * @param int $timeout
     * @param int $packageCount
     * @param int $packageSize
     * @return PingResponseEntity
     */
    public function ping(string $address, int $timeout = 500, int $packageCount = 4, $packageSize = 64): PingResponseEntity
    {
        $pingService = $this->pingService ?? new PingService($timeout, $packageSize);

        $result = $pingService->ping($address, $packageCount);

        return $result;
    }

    /**
     * @param PingServiceContract $pingService
     */
    public function setPingService(PingServiceContract $pingService)
    {
        $this->pingService = $pingService;
    }

}
