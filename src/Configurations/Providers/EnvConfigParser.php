<?php

namespace Magpie\Configurations\Providers;

use Magpie\Configurations\Concepts\ConfigurableParser;
use Magpie\Configurations\ConfigKey;
use Magpie\Configurations\EnvParserHost;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\InvalidArgumentException;
use Throwable;

/**
 * Configuration parser instance for 'env'
 */
class EnvConfigParser extends ConfigParser
{
    /**
     * @var EnvParserHost Parser host instance
     */
    protected readonly EnvParserHost $parserHost;
    /**
     * @var array<string|null> All prefixes
     */
    protected readonly array $prefixes;


    /**
     * Constructor
     * @param EnvConfigProvider $provider
     * @param iterable<string|null, ConfigKey> $keys
     * @param array<string, mixed> $contexts
     * @param EnvConfigSelection $selection
     */
    public function __construct(EnvConfigProvider $provider, iterable $keys, array $contexts, EnvConfigSelection $selection)
    {
        parent::__construct($provider, $keys, $contexts);

        $this->parserHost = new EnvParserHost();
        $this->prefixes = $selection->prefixes;
    }


    /**
     * @inheritDoc
     */
    protected function onHasConfig(ConfigKey $key) : bool
    {
        $finalKey = $this->getFinalKey($key);

        return $this->parserHost->has($finalKey);
    }


    /**
     * @inheritDoc
     */
    protected function onGetConfig(ConfigKey $key) : mixed
    {
        $finalKey = $this->getFinalKey($key);

        // Tap reconfiguration
        if ($key->parser instanceof ConfigurableParser) {
            $selection = new EnvConfigSelection(array_merge($this->prefixes, iter_flatten($key->name->getLowerSegments(), false)));
            try {
                return $key->parser->parseConfig($this->provider, $selection);
            } catch (ArgumentException $ex) {
                throw $ex;
            } catch (Throwable $ex) {
                throw new InvalidArgumentException($finalKey, previous: $ex);
            }
        }

        // Return value
        if ($key->isRequired) {
            return $this->parserHost->requires($finalKey, $key->parser);
        } else {
            return $this->parserHost->optional($finalKey, $key->parser, $key->defaultValue);
        }
    }


    /**
     * Construct final key
     * @param ConfigKey $key
     * @return string
     */
    protected function getFinalKey(ConfigKey $key) : string
    {
        $finalKeys = array_merge($this->prefixes);
        foreach ($key->name->getUpperSegments() as $segment) {
            $finalKeys[] = $segment;
        }

        return EnvParserHost::makeEnvKey(...$finalKeys);
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return EnvConfigProvider::TYPECLASS;
    }
}