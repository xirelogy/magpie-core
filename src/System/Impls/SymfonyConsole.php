<?php

namespace Magpie\System\Impls;

use Magpie\Consoles\BasicConsole;
use Magpie\Consoles\Concepts\ConsoleDisplayable;
use Magpie\Consoles\ConsoleTable;
use Magpie\Consoles\DisplayStyle;
use Magpie\Consoles\Inputs\PromptWithHiddenInput;
use Magpie\Consoles\Inputs\PromptWithOption;
use Magpie\Consoles\Texts\CompoundStructuredText;
use Magpie\Consoles\Texts\StructuredText;
use Magpie\Consoles\Texts\UnitStructuredText;
use Magpie\Exceptions\OperationFailedException;
use Stringable;
use Symfony\Component\Console\Formatter\OutputFormatter as SymfonyOutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle as SymfonyOutputFormatterStyle;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Input\ArgvInput as SymfonyArgvInput;
use Symfony\Component\Console\Input\InputInterface as SymfonyConsoleInputInterface;
use Symfony\Component\Console\Output\ConsoleOutput as SymfonyConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface as SymfonyConsoleOutputInterface;
use Symfony\Component\Console\Question\Question as SymfonyQuestion;
use Throwable;

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
     * @var SymfonyConsoleInputInterface Input backend
     */
    protected SymfonyConsoleInputInterface $inputBackend;
    /**
     * @var SymfonyConsoleOutputInterface Output backend
     */
    protected SymfonyConsoleOutputInterface $outputBackend;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inputBackend = new SymfonyArgvInput();
        $this->outputBackend = new SymfonyConsoleOutput();

        $this->inputBackend->setInteractive(true);

        $formatter = $this->outputBackend->getFormatter();
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
        $this->outputBackend->writeln($outText);
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

                $outTable = new SymfonyTable($this->outputBackend);
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
     * @inheritDoc
     */
    protected function obtain(PromptWithOption|Stringable|string|null $prompt, ?string $default) : string
    {
        $prompt = static::flattenInputPromptOptions($prompt, $options);

        $question = new SymfonyQuestion(static::flattenText($prompt), $default);
        $helper = new SymfonyQuestionHelper();

        foreach ($options as $option) {
            switch ($option) {
                case PromptWithHiddenInput::TYPECLASS:
                    // Set input as hidden
                    $question->setHidden(true);
                    $question->setHiddenFallback(false);
                    break;

                default:
                    // Default NOP
                    break;
            }
        }

        try {
            return $helper->ask($this->inputBackend, $this->outputBackend, $question);
        } catch (Throwable $ex) {
            throw new OperationFailedException(previous: $ex);
        }
    }


    /**
     * Extract prompt options whenever available
     * @param PromptWithOption|Stringable|string|null $prompt
     * @param array|null $options
     * @return Stringable|string|null
     */
    protected static function flattenInputPromptOptions(PromptWithOption|Stringable|string|null $prompt, ?array &$options = null) : Stringable|string|null
    {
        $options = [];
        while ($prompt instanceof PromptWithOption) {
            $options[] = $prompt::getTypeClass();
            $prompt = $prompt->prompt;
        }

        return $prompt;
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