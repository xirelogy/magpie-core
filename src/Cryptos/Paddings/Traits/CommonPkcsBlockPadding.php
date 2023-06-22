<?php

namespace Magpie\Cryptos\Paddings\Traits;

use Magpie\Cryptos\Exceptions\CryptoException;

/**
 * Common block padding for PKCS (PKCS#5 and PKCS#7)
 */
trait CommonPkcsBlockPadding
{
    /**
     * Encode a payload, applying the padding (common)
     * @param string $payload
     * @param int $blockSize
     * @return string
     */
    protected function blockEncode(string $payload, int $blockSize) : string
    {
        $pad = $blockSize - (strlen($payload) % $blockSize);
        if ($pad === 0) $pad = $blockSize;
        return $payload . str_repeat(chr($pad), $pad);
    }


    /**
     * Decode a payload, removing the padding (common)
     * @param string $payload
     * @param int $blockSize
     * @return string
     * @throws CryptoException
     */
    protected function blockDecode(string $payload, int $blockSize) : string
    {
        if (strlen($payload) == 0) {
            return $this->handleDecodeError($payload, 'Empty payload');
        }

        $pad = ord($payload[strlen($payload) - 1]);
        if ($pad < 1 || $pad > $blockSize) {
            return $this->handleDecodeError($payload, _format_safe('Pad ({{0}}) not in range', $pad) ?? 'Pad not in range');
        }

        if (strlen($payload) < $pad) {
            return $this->handleDecodeError($payload, 'Insufficient payload length');
        }

        for ($p = 0; $p < $pad; ++$p) {
            $checkPad = ord($payload[strlen($payload) - $p - 1]);
            if ($checkPad !== $pad) {
                return $this->handleDecodeError($payload, 'Payload padding check failed');
            }
        }

        return substr($payload, 0, strlen($payload) - $pad);
    }
}