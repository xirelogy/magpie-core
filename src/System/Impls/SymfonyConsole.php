<?php

namespace Magpie\System\Impls;

use Magpie\Consoles\BasicConsole;
use Magpie\Consoles\Concepts\ConsoleDisplayable;
use Magpie\Consoles\ConsoleTable;
use Magpie\Consoles\DisplayStyle;
use Magpie\Consoles\Texts\CompoundStructuredText;
use Magpie\Consoles\Texts\StructuredText;
use Magpie\Consoles\Texts\UnitStructuredText;
use Stringable;
use Symfony\Component\Console\Formatter\OutputFormatter as SymfonyOutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle as SymfonyOutputFormatterStyle;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Output\ConsoleOutput as SymfonyConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface as SymfonyConsoleOutputInterface;

/**
 * Default implementation of console
 * @internal
 */
class SymfonyConsole extends BasicConsole
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'symfony';
    /**
     * @var SymfonyConsoleOutputInterface Backend
     */
    protected SymfonyConsoleOutputInterface $backend;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->backend = new SymfonyConsoleOutput();

        $formatter = $this->backend->getFormatter();
        $formatter->setStyle('notice', new SymfonyOutputFormatterStyle('bright-white'));
        $formatter->setStyle('debug', new SymfonyOutputFormatterStyle('gray'));
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function output(Stringable|string|null $text, ?DisplayStyle $style = null) : void
    {
        $outText = static::flattenText($text, $style);
        $this->backend->writeln($outText);
    }


    /**
     * @inheritDoc
     */
    public function display(?ConsoleDisplayable $target) : void
    {
        if ($target === null) return;

        switch ($target::getTypeClass()) {
            case ConsoleTable::TYPECLASS:
                // Console table
                $exported = $target->_export();

                $outTable = new SymfonyTable($this->backend);
                $outTable->setHeaders($exported->headers);
                $outTable->setRows($exported->rows);
                $outTable->setStyle('default');
                $outTable->render();
                break;

            default:
                // Unsupported
                break;
        }
    }


    /**
     * Flatten the text before output
     * @param Stringable|string|null $text
     * @param DisplayStyle|null $style
     * @return string
     */
    protected static function flattenText(Stringable|string|null $text, ?DisplayStyle $style = null) : string
    {
        if ($text === null) return '';

        if ($text instanceof StructuredText) return static::flattenStructuredText($text);

        return static::flattenStyledText($text, $style);
    }


    /**
     * Flatten the text as StructuredText
     * @param StructuredText $text
     * @return string
     */
    protected static function flattenStructuredText(StructuredText $text) : string
    {
        $ret = '';

        if ($text instanceof CompoundStructuredText) {
            foreach ($text->texts as $subText) {
                $ret .= static::flattenStructuredText($subText);
            }
            return $ret;
        }

        if ($text instanceof UnitStructuredText) {
            return static::flattenStyledText($text->text, $text->format);
        }

        // Safe fallback
        return $text->__toString();
    }


    /**
     * Flatten a styled text
     * @param string $text
     * @param DisplayStyle|string|null $style
     * @return string
     */
    protected static function flattenStyledText(string $text, DisplayStyle|string|null $style) : string
    {
        $escapedText = SymfonyOutputFormatter::escape($text);

        if ($style instanceof DisplayStyle) $style = $style->value;

        $backendStyle = match ($style) {
            DisplayStyle::EMERGENCY->value,
            DisplayStyle::ALERT->value,
            DisplayStyle::CRITICAL->value,
            DisplayStyle::ERROR->value,
                => 'error',
            DisplayStyle::WARNING->value,
                => 'comment',
            DisplayStyle::NOTICE->value,
            DisplayStyle::STRONG->value,
                => 'notice',
            DisplayStyle::INFO->value,
                => 'info',
            DisplayStyle::DEBUG->value,
            DisplayStyle::NOTE->value,
                => 'debug',
            default,
                => null,
        };

        return $backendStyle ? "<$backendStyle>$escapedText</$backendStyle>" : $escapedText;
    }
}