<?php

if (!function_exists('setSplitDateRangeFormat')) {
    function setSplitDateRangeFormat(string $tanggal): array
    {
        $date = explode(' - ', $tanggal);
        return $date;
    }
}
