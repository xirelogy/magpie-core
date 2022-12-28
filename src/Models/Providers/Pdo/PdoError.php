<?php

namespace Magpie\Models\Providers\Pdo;

use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;
use PDOException as PhpPdoException;

/**
 * PDO related error information
 */
class PdoError implements Packable
{
   use CommonPackable;

    /**
     * @var string|null SQLSTATE error code
     */
   public readonly ?string $sqlState;
    /**
     * @var int|null Implementation specific error code
     */
   public readonly ?int $code;
    /**
     * @var string|null Implementation specific error message
     */
   public readonly ?string $message;


    /**
     * Constructor
     * @param string|null $sqlState
     * @param int|null $code
     * @param string|null $message
     */
   protected function __construct(?string $sqlState, ?int $code, ?string $message)
   {
       $this->sqlState = $sqlState;
       $this->code = $code;
       $this->message = $message;
   }


    /**
     * Get the simplified error message
     * @return string|null
     */
   public function getSimpleMessage() : ?string
   {
       return
           $this->message ??
           ($this->code !== null ? _format_safe(_l('Error {{0}}', $this->code)) : null) ??
           ($this->sqlState !== null ? _format_safe(_l('[SQLSTATE {{0}}]', $this->sqlState)) : null) ??
           null;
   }


    /**
     * @inheritDoc
     */
   protected function onPack(object $ret, PackContext $context) : void
   {
       $ret->sqlState = $this->sqlState;
       $ret->code = $this->code;
       $ret->message = $this->message;
   }


    /**
     * Construct from given native PDOException
     * @param PhpPdoException $ex
     * @return static
     * @internal
     */
   public static function _from(PhpPdoException $ex) : static
   {
        $errorInfo = $ex->errorInfo;
        if ($errorInfo === null) return new static(null, null, null);

        return new static(
            $errorInfo[0],
            $errorInfo[1],
            $errorInfo[2],
        );
   }
}