<?php

namespace Magpie\Models\Schemas\Checks;

use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\Models\Concepts\ModelCheckListenable;
use Magpie\Models\Exceptions\ModelSourceFileNotFoundException;
use Magpie\Models\Model;
use Magpie\Models\Schemas\TableSchema;
use Magpie\System\HardCore\AutoloadReflection;

/**
 * Utility to apply comments to table schemas
 */
class TableSchemaCommenter extends TableSchemaChecker
{
    /**
     * @inheritDoc
     */
    public static function apply(Model $model, ?ModelCheckListenable $listener = null) : void
    {
        static::applyOn($model, static::acceptListener($listener));
    }


    /**
     * @inheritDoc
     */
    protected static function applyOn(Model $model, ModelCheckListenable $listener) : void
    {
        $tableSchema = TableSchema::fromNative($model);

        $listener->notifyCheckTable($model::class, $tableSchema->getName(), false);

        $sourceFilename = static::getSourceFilename($model) ?? throw new ModelSourceFileNotFoundException($model::class);

        $sourceContent = LocalRootFileSystem::instance()->readFile($sourceFilename)->getData();
        $sourceContent = str_replace("\r", '', $sourceContent);

        $sourceRows = explode("\n", $sourceContent);
        $sourceSections = static::splitSourceCode($sourceRows);

        $comments = $tableSchema->exportPropertyComments($model::getColumnMethodPrefix());

        $outSource = static::mergeSourceCodes($sourceSections[0], $comments, $sourceSections[2]);

        // Trim any infinite extension of last line
        while (str_ends_with($outSource, "\n\n")) {
            $outSource = substr($outSource, 0, -1);
        }

        LocalRootFileSystem::instance()->writeFile($sourceFilename, $outSource);
    }


    /**
     * Get source filename for given model
     * @param Model $model
     * @return string|null
     */
    protected static function getSourceFilename(Model $model) : ?string
    {
        return iter_first(AutoloadReflection::instance()->getClassFilenames($model::class));
    }


    /**
     * Split source code into 3 sections:
     * - Header: until the last 'use'
     * - Comments: first docblock found (optional)
     * - Content: after any docblock
     *
     * @param array<string> $rows
     * @return array< array<string> >
     */
    protected static function splitSourceCode(array $rows) : array
    {
        $header = [];
        $comments = [];
        $code = [];

        $totalRows = count($rows);
        $currentBlock = 0;
        for ($i = 0; $i < $totalRows; ++$i) {
            $row = $rows[$i];
            $trimmedRow = trim($row);

            switch ($currentBlock) {
                case 0:
                    // Expect to be header
                    if ($trimmedRow === '/**') {
                        $currentBlock = 1;
                        --$i;
                        continue 2;
                    }

                    if ($trimmedRow === '#[' || str_starts_with($trimmedRow, 'class ')) {
                        $currentBlock = 2;
                        --$i;
                        continue 2;
                    }

                    $header[] = $row;
                    break;

                case 1:
                    // Expect to be comments
                    if ($trimmedRow === '#[' || str_starts_with($trimmedRow, 'class ')) {
                        $currentBlock = 2;
                        --$i;
                        continue 2;
                    }

                    $comments[] = $row;
                    break;

                case 2:
                    $code[] = $row;
                    break;
            }
        }

        return [
            $header,
            $comments,
            $code,
        ];
    }


    /**
     * Merge multiple source codes
     * @param array<string> ...$codeSections
     * @return string
     */
    protected static function mergeSourceCodes(array ...$codeSections) : string
    {
        $ret = '';
        foreach ($codeSections as $codeSection) {
            foreach ($codeSection as $code) {
                $ret .= "$code\n";
            }
        }

        return $ret;
    }
}