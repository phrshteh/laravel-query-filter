<?php

if (PHP_VERSION_ID < 80100 && !function_exists('array_is_list')) {
    function array_is_list(array $array): bool
    {
        if ([] === $array || $array === array_values($array)) {
            return true;
        }

        $nextKey = -1;

        foreach ($array as $k => $v) {
            if ($k !== ++$nextKey) {
                return false;
            }
        }

        return true;
    }
}
