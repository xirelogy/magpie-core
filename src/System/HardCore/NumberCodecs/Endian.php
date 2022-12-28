<?php

namespace Magpie\System\HardCore\NumberCodecs;

/**
 * Binary representation endianess
 */
enum Endian : string
{
    /**
     * Big endian
     */
    case BIG = 'big';
    /**
     * Little endian
     */
    case LITTLE = 'little';
}