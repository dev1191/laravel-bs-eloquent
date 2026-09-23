<?php

// config for Dev1191/BikramSambat
return [
    /**
     * Serialization format for BsDate attributes when converted to JSON / API responses.
     * Supported options:
     * - 'dual':   ['ad' => '2024-04-14', 'bs' => '2081-01-01']
     * - 'bs':     '2081-01-01'
     * - 'ad':     '2024-04-14'
     * - 'object': ['bs_year' => 2081, 'bs_month' => 1, 'bs_day' => 1, 'fiscal_year' => '2080/81', ...]
     */
    'serialization' => env('BS_DATE_SERIALIZATION', 'dual'),

    /**
     * Default date format string when formatting BS dates.
     */
    'default_format' => 'Y-m-d',

    /**
     * Whether to format numbers in Devanagari numerals by default.
     */
    'devanagari' => false,
];
