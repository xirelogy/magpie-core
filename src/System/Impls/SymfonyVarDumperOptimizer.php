<?php

namespace Magpie\System\Impls;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Closure;
use Magpie\General\Traits\StaticClass;
use Magpie\Models\Schemas\Configs\SchemaPreference;
use Magpie\Models\Schemas\TableSchema;
use Magpie\Routes\Impls\RouteMap;
use Magpie\Routes\RouteContext;
use Magpie\Routes\RouteDomain;
use Magpie\System\Impls\Concepts\SymfonyVarDumperArrayPatchable;
use Magpie\System\Impls\Patches\SymfonyVarDumperCutStubPatch;
use Magpie\System\Impls\Patches\SymfonyVarDumperDropPrivatePatch;
use Magpie\System\Impls\Patches\SymfonyVarDumperDropPropertiesPatch;
use Magpie\System\Impls\Patches\SymfonyVarDumperDropProtectedPatch;
use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Cloner\VarCloner;

/**
 * Symfony's VarDumper optimizer
 * @internal
 */
class SymfonyVarDumperOptimizer
{
    use StaticClass;


    /**
     * Setup the var dumper
     * @return void
     */
    public static function setup() : void
    {
        VarCloner::$defaultCasters[Carbon::class] = static::createCaster([
            SymfonyVarDumperDropProtectedPatch::create(),
        ]);

        VarCloner::$defaultCasters[CarbonImmutable::class] = static::createCaster([
            SymfonyVarDumperDropProtectedPatch::create(),
        ]);

        VarCloner::$defaultCasters[RouteDomain::class] = static::createCaster([
            SymfonyVarDumperDropPrivatePatch::for(RouteDomain::class),
        ]);

        VarCloner::$defaultCasters[RouteMap::class] = static::createCaster([
            SymfonyVarDumperCutStubPatch::for(static::protectedKeyOf('root')),
        ]);

        VarCloner::$defaultCasters[RouteContext::class] = static::createCaster([
            SymfonyVarDumperDropProtectedPatch::create(),
        ]);

        VarCloner::$defaultCasters[SchemaPreference::class] = static::createCaster([
            SymfonyVarDumperDropPrivatePatch::for(SchemaPreference::class),
        ]);

        VarCloner::$defaultCasters[TableSchema::class] = static::createCaster([
            SymfonyVarDumperDropPrivatePatch::for(TableSchema::class),
            SymfonyVarDumperDropPropertiesPatch::for(
                static::protectedKeyOf('preference'),
                static::protectedKeyOf('class'),
                static::protectedKeyOf('attribute'),
                static::protectedKeyOf('attributeInstance'),
                static::protectedKeyOf('cacheColumns'),
                static::protectedKeyOf('cacheAutoIncrementColumns'),
                static::protectedKeyOf('cacheCreateTimestampColumns'),
                static::protectedKeyOf('cacheUpdateTimestampColumns'),
            ),
        ]);
    }


    /**
     * Create a caster
     * @param array<SymfonyVarDumperArrayPatchable> $patches
     * @return Closure
     */
    protected static function createCaster(array $patches) : Closure
    {
        return function(mixed $object, array $array, Stub $stub, bool $isNested, int $filter) use($patches) : array {
            _used($stub, $isNested, $filter);

            $ret = [];
            foreach ($array as $arrKey => $arrValue) {
                foreach ($patches as $patch) {
                    if (!$patch->isKeepArrayItem($arrKey)) continue 2;
                    $patch->patchArrayItem($arrKey, $arrValue);
                }

                $ret[$arrKey] = $arrValue;
            }

            return $ret;
        };
    }


    /**
     * The effective key name for protected variable
     * @param string $name
     * @return string
     */
    protected static function protectedKeyOf(string $name) : string
    {
        return Caster::PREFIX_PROTECTED . $name;
    }


    /**
     * The effective key name for private variable (of a given class)
     * @param string $className
     * @param string $name
     * @return string
     */
    protected static function privateKeyOf(string $className, string $name) : string
    {
        return "\0" . $className . "\0" . $name;
    }
}