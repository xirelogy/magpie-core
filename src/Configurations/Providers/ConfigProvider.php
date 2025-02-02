<?php

namespace Magpie\Configurations\Providers;

use Magpie\Configurations\Concepts\ConfigSelectable;
use Magpie\Configurations\ConfigKey;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Traits\StaticCreatable;

/**
 * Configuration provider
 */
abstract class ConfigProvider implements TypeClassable
{
    use StaticCreatable;

    /**
     * @var array<string, mixed> Associated context
     */
    protected array $contexts = [];


    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * Specify context
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function withContext(string $key, mixed $value) : static
    {
        $this->contexts[$key] = $value;
        return $this;
    }


    /**
     * Create a configuration parser
     * @param iterable<string|null, ConfigKey> $keys
     * @param ConfigSelectable|null $selection
     * @return ConfigParser
     * @throws SafetyCommonException
     */
    public final function createParser(iterable $keys, ?ConfigSelectable $selection) : ConfigParser
    {
        $instance = $this->onCreateParser($keys, $this->contexts, $selection);
        if ($instance === null) throw new OperationFailedException();

        return $instance;
    }


    /**
     * Create a specific configuration parser
     * @param iterable<string|null, ConfigKey> $keys
     * @param array<string, mixed> $contexts
     * @param ConfigSelectable|null $selection
     * @return ConfigParser|null
     */
    protected abstract function onCreateParser(iterable $keys, array $contexts, ?ConfigSelectable $selection) : ?ConfigParser;


    /**
     * Get description for specific type
     * @return string
     */
    public static function describeType() : string
    {
        return _l('Specific type');
    }
}