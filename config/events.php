<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Date-based Employee Events
    |--------------------------------------------------------------------------
    |
    | Configuration for the daily Telegram notifications about employee
    | events (birthdays, work anniversaries, etc.). The scheduler runs
    | a single command once per day in the timezone defined below.
    |
    */

    'enabled' => env('EVENTS_NOTIFY_ENABLED', true),

    'timezone' => env('EVENTS_TIMEZONE', 'Europe/Kyiv'),

    'notify_at' => env('EVENTS_NOTIFY_AT', '09:00'),

    /*
    |--------------------------------------------------------------------------
    | Message Templates
    |--------------------------------------------------------------------------
    |
    | Each template is rendered with placeholders replaced by trigger logic.
    | All user-supplied values are HTML-escaped before substitution, so the
    | template itself may safely contain Telegram HTML formatting tags.
    |
    | Available placeholders:
    |   :name, :surname, :department, :department_suffix
    |
    */

    'templates' => [
        'birthday' => env(
            'EVENTS_BIRTHDAY_TEMPLATE',
            "\u{1F382} Сьогодні день народження святкує <b>:name :surname</b>:department_suffix!\nВітаємо! \u{1F389}"
        ),
    ],

];
