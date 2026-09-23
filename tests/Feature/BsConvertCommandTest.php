<?php

it('converts BS date via artisan command', function () {
    $this->artisan('bs:convert', ['date' => '2081-01-01'])
        ->expectsOutputToContain('Bikram Sambat (BS): 2081-01-01 (Baisakh)')
        ->expectsOutputToContain('Gregorian (AD):     2024-04-14')
        ->expectsOutputToContain('Day:                Sunday (आइतबार)')
        ->expectsOutputToContain('Fiscal Year:        2080/81 (Q4)')
        ->assertSuccessful();
});

it('converts AD date via artisan command with flag', function () {
    $this->artisan('bs:convert', ['date' => '2024-04-14', '--from' => 'ad'])
        ->expectsOutputToContain('Bikram Sambat (BS): 2081-01-01 (Baisakh)')
        ->expectsOutputToContain('Gregorian (AD):     2024-04-14')
        ->assertSuccessful();
});
