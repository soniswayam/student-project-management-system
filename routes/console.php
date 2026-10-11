<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('reminders:send')
    ->dailyAt('07:00')
    ->withoutOverlapping();
