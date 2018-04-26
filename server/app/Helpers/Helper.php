<?php

use Illuminate\Support\Facades\Log;

if (! function_exists('log_and_echo')) {
    /**
     * Logs and echo message.
     *
     * @param $text
     * @return void
     */
    function log_and_echo($text) {
        Log::debug($text);
        echo $text;
    }
}
