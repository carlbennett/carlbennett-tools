<?php

namespace CarlBennett\Tools\Libraries\Core;

class Logger
{
    public static string $additionalHtmlHeader = '';
    public static string $additionalHtmlFooter = '';

    private function __construct() {}

    /**
     * Registers Application Performance Monitors (APMs).
     */
    public static function registerAPMs(): void
    {
        if (\extension_loaded('newrelic'))
        {
            \newrelic_disable_autorum();
            self::$additionalHtmlHeader .= \newrelic_get_browser_timing_header();
            self::$additionalHtmlFooter .= \newrelic_get_browser_timing_footer();
        }
    }

    public static function logMetric(string $name, mixed $value): void
    {
        if (\extension_loaded('newrelic') && \is_float($value))
        {
            \newrelic_custom_metric($name, $value);
        }
    }
}
