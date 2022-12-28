<?php

namespace Magpie\HttpServer\Traits;

/**
 * Common implementation of WithHeaderSpecifiable
 */
trait CommonHeaderSpecifiable
{
    /**
     * Set header
     * @param string $headerName
     * @param string $value
     * @param bool $isAllowDuplicate
     * @return $this
     */
    public function withHeader(string $headerName, string $value, bool $isAllowDuplicate = false) : static
    {
        $headerName = trim($headerName);
        $headerKey = strtolower($headerName);

        $this->headerNames[$headerKey] = $headerName;

        if (!$isAllowDuplicate) {
            $this->headerValues[$headerKey] = $value;
        } else {
            $values = $this->headerValues[$headerKey] ?? [];
            if (is_string($values)) $values = [$values];
            $values[] = $value;

            $this->headerValues[$headerKey] = $values;
        }

        return $this;
    }
}