<?php
namespace App\Facades;

class Binary
{
    /**
     * Get a binary path
     *
     * @param $target
     * @return string
     */
    public static function path($target)
    {
        return base_path('binary')."/$target";
    }
}
