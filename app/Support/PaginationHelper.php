<?php

namespace App\Support;

use Illuminate\Http\Request;

class PaginationHelper
{
    public static function resolvePerPage(Request $request, int $default = 15, int $max = 500, string $param = 'per_page'): int
    {
        $choice = $request->input($param, $default);
        $custom = $request->input($param . '_custom');

        if ($choice === 'custom') {
            $value = is_numeric($custom) ? (int) $custom : $default;
        } else {
            $value = is_numeric($choice) ? (int) $choice : $default;
        }

        $value = max(1, min($max, $value));

        return $value;
    }
}
