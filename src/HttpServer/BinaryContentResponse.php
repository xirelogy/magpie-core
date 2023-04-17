<?php

namespace Magpie\HttpServer;

use Exception;
use Magpie\General\Concepts\BinaryContentable;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\FileSystemAccessible;
use Magpie\General\Concepts\PrimitiveBinaryContentable;
use Magpie\General\Contents\BinaryContent;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Sugars\Quote;
use Magpie\HttpServer\Concepts\WithHeaderSpecifiable;
use Magpie\HttpServer\Traits\CommonHeaderSpecifiable;
use Magpie\System\Kernel\ExceptionHandler;

/**
 * A response which is a binary content
 */
class BinaryContentResponse extends CommonRenderable implements WithHeaderSpecifiable
{
    use CommonHeaderSpecifiable;


    /**
     * @var PrimitiveBinaryContentable Associated content
     */
    public readonly PrimitiveBinaryContentable $content;
    /**
     * @var bool If content served as attachment
     */
    protected bool $isAttachment = false;
    /**
     * @var string|null Specific filename for attachment
     */
    protected ?string $filename = null;
    /**
     * @var array<string, string> Header names
     */
    protected array $headerNames = [];
    /**
     * @var array<string, string|array> Headers values
     */
    protected array $headerValues = [];


    /**
     * Constructor
     * @param BinaryDataProvidable $content
     */
    public function __construct(BinaryDataProvidable $content)
    {
        $this->content = BinaryContent::asPrimitiveBinaryContentable($content);
    }


    /**
     * With attachment filename
     * @param string|null $filename
     * @return $this
     */
    public function withAttachmentFilename(?string $filename = null) : static
    {
        $this->isAttachment = true;
        $this->filename = $filename;

        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function onRender() : void
    {
        $mimeType = $this->content->getMimeType();
        if ($mimeType !== null) {
            $this->withHeader(CommonHttpHeader::CONTENT_TYPE, $mimeType);
        }

        if ($this->isAttachment) {
            $filename = $this->filename ?? $this->content->getFilename();
            if ($filename !== null) {
                $this->withHeader(CommonHttpHeader::CONTENT_DISPOSITION, 'attachment; filename=' . Quote::double(static::encodeFilename($filename)));
            }
        }

        static::sendHeaders($this->headerNames, $this->headerValues);

        try {
            if ($this->content instanceof FileSystemAccessible) {
                $this->onRenderFromFile($this->content);
            } else {
                echo $this->content->getData();
            }
        } catch (Exception) {
            echo '';    // Suppressed error
        }
    }


    /**
     * Render from file
     * @param FileSystemAccessible $content
     * @return void
     * @throws Exception
     */
    protected function onRenderFromFile(FileSystemAccessible $content) : void
    {
        $scope = ExceptionHandler::setScopeErrorLevel();
        _used($scope);

        $output = fopen('php://output', 'w');
        $file = fopen($content->getFileSystemPath(), 'r');

        stream_copy_to_stream($file, $output);

        fclose($output);
        fclose($file);
    }


    /**
     * Encode as filename
     * @param string $filename
     * @return string
     */
    protected static function encodeFilename(string $filename) : string
    {
        $filename = str_replace('/', '_', $filename);
        $filename = str_replace('\\', '_', $filename);

        return urlencode($filename);
    }
}