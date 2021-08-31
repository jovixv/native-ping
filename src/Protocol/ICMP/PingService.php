<?php

namespace jovixv\Ping\Protocol\ICMP;

use jovixv\Ping\PacketResponseEntity;
use jovixv\Ping\PingResponseEntity;
use jovixv\Ping\PingServiceContract;
use jovixv\Ping\Socket;
use Exception;


class PingService implements PingServiceContract
{

    /**
     * @var int $timeout timeout in millisecond
     */
    private $timeout;

    /**
     * @var int $packetPayloadSize bytes count of payload.
     */
    private $packetPayloadSize;

    /**
     * @var PackageBuilder $packageBuilder
     */
    private $packageBuilder;

    /**
     * @var Socket $socketService
     */
    private $socketService;

    /**
     * PingService constructor.
     * @param int $millisecondTimeout
     * @param int $packetPayloadSize bytes count of payload
     */
    public function __construct(int $millisecondTimeout, int $packetPayloadSize)
    {
        $this->timeout = $millisecondTimeout;
        $this->packetPayloadSize = $packetPayloadSize;
        $this->packageBuilder = new PackageBuilder();
        $this->socketService = new Socket(AF_INET, SOCK_RAW, STREAM_IPPROTO_ICMP);
    }

    /**
     * @param string $address
     * @param int $packageCount
     * @return PingResponseEntity
     */
    public function ping(string $address, int $packageCount = 4): PingResponseEntity
    {
        $responseEntity = new PingResponseEntity();
        $sourceIP = null;

        for ($i=0; $i < $packageCount; $i++){

            $socket = $this->socketService->create();
            $identity = [chr(ord('T')), chr(ord('B'))];
            $seq = [chr($i), chr($i + 1)];

            $packet = $this->packageBuilder
                ->setType(chr(8))
                ->setCode(chr(0))
                ->initChecksum()
                ->setIdentity(chr(ord('T')), chr(ord('B')))
                ->setSequence(chr($i), chr($i + 1))
                ->mockPackage($this->packetPayloadSize + 8) // 8 is count of byte for header/
                ->calculateChecksum()->getPacket();

            $responsePacketEntity = new PacketResponseEntity();

            try{
                $startTime = microtime(true);
                $receivedData = $socket->send($packet, $address, 0, 0)->callSelect()->receive($address, 0);
                $endTime = microtime(true);

                $this->packetValidation($receivedData, $identity, $seq);

                if (!$sourceIP)
                    $sourceIP = $this->resolveIp($receivedData);

                $responsePacketEntity->delay = (int)(($endTime - $startTime) * 1000);
                $responsePacketEntity->packet = $receivedData;
                $responsePacketEntity->size = $this->packetPayloadSize;
                $responsePacketEntity->isSuccess = true;

            }catch (Exception $exception){
                $responsePacketEntity->isSuccess = false;
                $responsePacketEntity->errorException = $exception;
            }

            $responseEntity->packets[$i] = $responsePacketEntity;
        }

        $responseEntity->sourceIp = $sourceIP;
        $responseEntity->packetSize = $this->packetPayloadSize;
        $responseEntity->address = $address;
        $responseEntity->averageDelay = $this->countingAverageDelay($responseEntity->packets);

        return $responseEntity;
    }

    /**
     * @param string $receivedPacket
     * @param array $identity
     * @param array $sequence
     * @return bool
     * @throws ICMPException
     */
    private function packetValidation(string $receivedPacket, array $identity, array $sequence): bool
    {
        // offset from header IP data
        $receivedICMPPayload = substr($receivedPacket, 20);

        // bad identity
        if ($receivedICMPPayload[4] !== $identity[0] && $receivedICMPPayload[5] !== $identity[1])
            throw new ICMPException('Identity bytes is not equal');

        // sequence
        if ($receivedICMPPayload[6] !== $sequence[0] && $receivedICMPPayload[7] !== $sequence[1])
            throw new ICMPException('Sequence is not correct');


        $checksum = $this->packageBuilder
            ->setType(chr(0))
            ->setCode(chr(0))
            ->initChecksum()
            ->setIdentity($identity[0], $identity[1])
            ->setSequence($sequence[0], $sequence[1])
            ->mockPackage($this->packetPayloadSize + 8) // 8 is count of byte for header/
            ->calculateChecksum()->getChecksum();

        // checksum (it is fast check, can be optimize)
        if (substr($receivedICMPPayload, 2, 2) !== $checksum)
            throw new ICMPException('Wrong checksum');

        return true;
    }

    /**
     * @param PacketResponseEntity[]|array $packets
     * @return int|null
     */
    private function countingAverageDelay(array $packets): ?int
    {
        $averageDelay = null;
        $countOfValidPacket = 0;

        foreach ($packets as $packet) {
            if ($packet->isSuccess){
                $averageDelay += $packet->delay;
                $countOfValidPacket++;
            }
        }

        $averageDelay = ($countOfValidPacket) ? $averageDelay / count($packets) : null;

        return $averageDelay;
    }

    /**
     * @param string $packet
     * @return string
     */
    private function resolveIp(string $packet): string
    {
        // host ip
        $ipBinary = substr($packet, 12, 4);
        $ipBitArray = unpack('C*', $ipBinary);

        return implode('.', $ipBitArray);
    }


}
