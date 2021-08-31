<?php


namespace jovixv\Ping\Protocol\ICMP;


class PackageBuilder
{
    /**
     * @var string $packet
     */
    private $packet = '';

    /**
     * @var array $identity
     */
    private $identity = [0x00, 0x00];

    /**
     * @var array $sequence
     */
    private $sequence = [0x00, 0x00];

    /**
     * @var string
     */
    private $checksum = '\x00\x00';

    /**
     * Default value is 72 octets/byte.
     * This size include header data.
     * @var int|null $maxPackageSize
     */
    private $maxPackageSize = 72;

    /**
     * PackageBuilder constructor.
     * @param int|null $packetSize
     */
    public function __construct(?int $packetSize = null)
    {
        if ($packetSize)
            $this->maxPackageSize = $packetSize;
    }

    /**
     * @param string $octet
     * @return PackageBuilder
     */
    public function setType(string $octet): self
    {
        $this->setOctet(0, $octet);

        return $this;
    }

    /**
     * @param string $octet
     * @return PackageBuilder
     */
    public function setCode(string $octet): self
    {
        $this->setOctet(1, $octet);

        return $this;
    }

    /**
     * @return $this
     */
    public function initChecksum(): self
    {
        $this->setOctet(2, chr(0));
        $this->setOctet(3, chr(0));

        return $this;
    }

    /**
     * @param string $firstOctet
     * @param string $secondOctet
     * @return PackageBuilder
     */
    public function setIdentity(string $firstOctet, string $secondOctet): self
    {
        $this->setOctet(4, $firstOctet);
        $this->identity[0] = $firstOctet;

        $this->setOctet(5, $secondOctet);
        $this->identity[1] = $secondOctet;

        return $this;
    }

    /**
     * @param string $firstOctet
     * @param string $secondOctet
     * @return PackageBuilder
     */
    public function setSequence(string $firstOctet, string $secondOctet): self
    {
        $this->setOctet(6, $firstOctet);
        $this->sequence[0] = $firstOctet;

        $this->setOctet(7, $secondOctet);
        $this->sequence[1] = $secondOctet;

        return $this;
    }

    /**
     * @param int|null $packetSize
     * @return PackageBuilder
     */
    public function mockPackage(?int $packetSize = null): self
    {
        // size in byte
        $maxPackageSize = $packetSize ?? $this->maxPackageSize;

        while(strlen($this->packet) < $maxPackageSize)
            $this->appendOctet(chr(0));

        return $this;
    }

    /**
     * @return PackageBuilder
     */
    public function calculateChecksum(): self
    {
        // each element is 16 bit paired integer.(big endian)
        // also, it can be optimized. we can pair by 32 bit or 64 bit integers.
        $pairedIntegers = unpack('n*', $this->packet);
        $packetLen = strlen($this->packet);
        $longSum = array_sum($pairedIntegers);

        if ($longSum % 2){
            $temp = unpack('C*', $this->packet[$packetLen - 1]); // last element from packet
            $longSum += $temp[1];
        }

        // fold to 16 bit.
        while ($longSum >> 16)
            $longSum = ($longSum & 0xffff) + ($longSum >> 16);

        // pack to 16 bit inverted (ones'e complement)sum.
        $checksum = pack('n*', ~$longSum);
        $this->checksum = $checksum;

        $this->setOctet(2, $checksum[0]);
        $this->setOctet(3, $checksum[1]);

        return $this;
    }

    /**
     * @param int $index
     * @param string $octet
     */
    public function setOctet(int $index, string $octet)
    {
        $this->packet[$index] = $octet;
    }

    /**
     * @param string $octet
     */
    public function appendOctet(string $octet)
    {
        $this->packet .= $octet;
    }

    /**
     * @return string
     */
    public function getPacket(): string
    {
        return $this->packet;
    }

    /**
     * @return string
     */
    public function getChecksum(): string
    {
        return $this->checksum;
    }
}
