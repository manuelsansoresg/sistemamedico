<?php

if (! function_exists('trans_column')) {
    function trans_column(string $module, string $column, ?string $default = null): string
    {
        $key = "{$module}.columns.{$column}";
        if (__($key) !== $key) {
            return __($key);
        }

        $commonKey = "common.{$column}";
        if (__($commonKey) !== $commonKey) {
            return __($commonKey);
        }

        return $default ?? ucfirst(str_replace('_', ' ', $column));
    }
}

if (! function_exists('trans_enum')) {
    function trans_enum(string $key, ?string $default = null): string
    {
        $translated = __($key);
        if ($translated !== $key) {
            return $translated;
        }

        return $default ?? $key;
    }
}
