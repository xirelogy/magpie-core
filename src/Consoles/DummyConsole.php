<?php

namespace Magpie\Consoles;

use Magpie\Consoles\Concepts\ConsoleServiceable;
use Magpie\Consoles\Inputs\PromptWithOption;
use Magpie\Exceptions\UnsupportedException;
use Magpie\General\Traits\SingletonInstance;
use Stringable;

/**
 * A 'do nothing' console
 */
class DummyConsole extends BasicConsole
{
    use SingletonInstance;

    /**
     * Current type class
     */
    public const TYPECLASS = 'dummy';


    /**
     * @inheritDoc
     */
    public function output(Stringable|string|null $text, ?DisplayStyle $style = null) : void
    {
        // nop
    }


    protected function createService() : ConsoleServiceable
    {
        return new class implements ConsoleServiceable {
            /**
             * @inheritDoc
             */
            public static function getTypeClass() : string
            {
                return DummyConsole::TYPECLASS;
            }
        };
    }


    /**
     * @inheritDoc
     */
    protected function obtain(PromptWithOption|Stringable|string|null $prompt, ?string $default) : ?string
    {
        throw new UnsupportedException();
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }
}