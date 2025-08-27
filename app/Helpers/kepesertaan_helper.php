<?php

if (!function_exists('jenisIdentitas')) {
    function jenisIdentitas($identitas)
    {
        $length = strlen($identitas);
        switch ($length) {
            case $length == 16:
                return ['jenis' => 'nik', 'nomor' => $identitas];
                break;
            case $length == 13:
                return ['jenis' => 'noBpjs', 'nomor' => $identitas];
                break;
            case $length < 12:
                return ['jenis' => 'norm', 'nomor' => $identitas];
                break;
            default:
                return ['jenis' => 'default', 'nomor' => $identitas];
        }
    }
}
