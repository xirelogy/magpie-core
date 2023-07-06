<?php

namespace Magpie\HttpServer\Resolvers;

use Magpie\General\Networks\IpAddress;
use Magpie\General\Sugars\Excepts;

/**
 * A chain of address
 */
final class AddressChain
{
    /**
     * @var array<IpAddress> All addresses in chain
     */
    protected array $addresses = [];
    /**
     * @var IpAddress|null Last address pushed
     */
    protected ?IpAddress $lastAddress = null;


    /**
     * Try to push an address into the chain
     * @param IpAddress|string|null $address
     * @return bool
     */
    public function push(IpAddress|string|null $address) : bool
    {
        if ($address === null) return false;
        if ($address === '') return false;

        if (!$address instanceof IpAddress) {
            $address = Excepts::noThrow(fn () => IpAddress::parse($address));
            if ($address === null) return false;
        }

        $this->addresses[] = $address;
        $this->lastAddress = $address;

        return true;
    }


    /**
     * Last address pushed to the chain
     * @return IpAddress|null
     */
    public function getLastAddress() : ?IpAddress
    {
        return $this->lastAddress;
    }


    /**
     * Finalize into result (provided in backwards order)
     * @return iterable<string>
     */
    public function finalize() : iterable
    {
        $count = count($this->addresses);
        for ($i = $count - 1; $i >= 0; --$i) {
            yield '' . $this->addresses[$i];
        }
    }
}