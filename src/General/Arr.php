<?php

namespace Magpie\General;

use Magpie\General\Traits\StaticClass;

/**
 * Array utilities
 */
class Arr
{
    use StaticClass;


    /**
     * Insert item into the array
     * @param array $arr Target array
     * @param mixed $item Item to be inserted
     * @param int|null $index Index to insert the item into, or append the item to array when null
     * @return void
     */
    public static function insert(array &$arr, mixed $item, ?int $index = null) : void
    {
        if ($index !== null) {
            $arr = array_merge(array_slice($arr, 0, $index), [$item], array_slice($arr, $index));
        } else {
            $arr[] = $item;
        }
    }


    /**
     * Delete item from the array
     * @param array $arr Target array
     * @param mixed $item Item to be deleted
     * @return int Total number of items deleted from the array
     */
    public static function deleteByValue(array &$arr, mixed $item) : int
    {
        $totalDeleted = 0;
        $ret = [];
        foreach ($arr as $candidateItem) {
            if ($candidateItem !== $item) {
                $ret[] = $candidateItem;
            } else {
                ++$totalDeleted;
            }
        }

        if ($totalDeleted > 0) $arr = $ret;
        return $totalDeleted;
    }
}