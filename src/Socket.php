<?php

namespace jovixv\Ping;

use jovixv\Ping\Exceptions\SocketException as SocketException;
use jovixv\Ping\Exceptions\SocketSendException;
use jovixv\Ping\Exceptions\SocketTimeoutException;

class Socket
{
    /**
     * @var int
     */
    protected $socketDomain;

    /**
     * @var int
     */
    protected $socketType;

    /**
     * @var int
     */
    protected $socketProtocol;

    /**
     * @var string
     */
    protected $errorMessage = '';

    /**
     * for php 8 it will be Socket object, else Resource
     * @var $socket
     */
    private $socket;

    /**
     * Socket constructor.
     * @param int $socketDomain
     * @param int $socketType
     * @param int $socketProtocol
     */
    public function __construct(int $socketDomain, int $socketType, int $socketProtocol)
    {
        $this->socketDomain = $socketDomain;
        $this->socketType = $socketType;
        $this->socketProtocol = $socketProtocol;
    }

    /**
     * @return self
     */
    public function create(): self
    {
        new SocketException();
        $this->socket = socket_create($this->socketDomain, $this->socketType, $this->socketProtocol);
        return $this;
    }

    /**
     * @param string $packet
     * @param string $address
     * @param int $port
     * @param int $flag
     * @return Socket
     * @throws SocketSendException
     */
    public function send(string $packet, string $address, int $port = 0, $flag = 0): self
    {
        $sentBytes = socket_sendto($this->socket, $packet, strlen($packet), $flag, $address, $port);

        if ($sentBytes === false)
            throw new SocketSendException('Can not send data, by socket_sendto', 500);

        return $this;
    }

    /**
     * Default timeout is 150 ms.
     *
     * @param int $timeOut in millisecond
     * @return Socket
     * @throws SocketException|SocketTimeoutException
     */
    public function callSelect(int $timeOut = 150): self
    {

        $read = [$this->socket];
        $write = NULL;
        $except = NULL;

        $select = socket_select($read, $write, $except, 0, $timeOut * 1000);

        if ($select === null)
            throw new SocketException('Error select socket', 500);

        if ($select === 0)
            throw new SocketTimeoutException('Timeout select socket', 500);

        return $this;
    }

    /**
     * @param string $address
     * @param int $port
     * @param int $length
     * @param int $flag
     * @return string
     * @throws SocketException
     */
    public function receive(string $address, int $port = 0,  int $length = 65535, int $flag = 0): string
    {
        $packet = '';
        $receivedBytes = socket_recvfrom($this->socket, $packet, $length, $flag, $address, $port);

        if ($receivedBytes === false)
            throw new SocketException(socket_strerror(socket_last_error($this->socket)), 500);

        return $packet;
    }

    /**
     *
     */
    public function __destruct()
    {
       if ($this->socket)
           socket_close($this->socket);
    }
}
