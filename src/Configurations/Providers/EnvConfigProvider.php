<?php

namespace Magpie\Configurations\Providers;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\Concepts\ConfigSelectable;
use Magpie\Configurations\ConfigRedirect;

/**
 * Configuration provider from 'env'
 */
class EnvConfigProvider extends ConfigProvider
{
    /**
     * Current type class
     */
    const TYPECLASS = 'env';


    /**
     * @inheritDoc
     */
    protected function onCreateParser(iterable $keys, array $contexts, ?ConfigSelectable $selection) : ?ConfigParser
    {
        if (!$selection instanceof EnvConfigSelection) return null;

        return new EnvConfigParser($this, $keys, $contexts, $selection);
    }


    /**
     * Create specific redirection setup
     * @param array<string|null> $typePrefixes
     * @return ConfigRedirect<ConfigSelectable>
     */
    public static function createRedirectSetup(array $typePrefixes = []) : ConfigRedirect
    {
        $parser = ClosureParser::create(function (mixed $value, ?string $hintName) use ($typePrefixes) : ConfigSelectable {
            $value = StringParser::create()->parse($value, $hintName);
            $prefixes = $value !== '-' ? [ $value ] : [];

            return new EnvConfigSelection(array_merge($typePrefixes, $prefixes));
        });

        return new ConfigRedirect($parser, '-');
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }
}