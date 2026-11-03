<?php

namespace CarlBennett\Tools\Libraries\Core;

class StringProcessor
{
    /**
     * Matches a pattern to a fuzzy subject, e.g. 'api.*' should match 'api.enabled'
     *
     * @param string $pattern The pattern that the subject should match, e.g. 'api.*'
     * @param string $subject The subject that is being compared, e.g. 'api.enabled'
     * @return boolean Whether the string matches.
     */
    public static function fuzzyMatch(string $pattern, string $subject): bool
    {
        $pattern = \implode('.*', \array_map(fn (string $part): string => \preg_quote($part, '/'), \explode('*', $pattern)));
        return (bool) \preg_match('/^' . $pattern . '$/i', $subject);
    }

    /**
     * Checks whether the current User Agent is a GUI web browser known by a common string, e.g. Firefox.
     *
     * @param string $user_agent User Agent to check, or null to check HTTP_USER_AGENT environment variable.
     * @return boolean Whether the client is using a GUI web browser or not.
    */
    public static function isBrowser(?string $user_agent = null): bool
    {
        return (1 === \preg_match(
            '#(?:AppleWebKit|Chrome|Edge|Firefox|Mozilla|MSIE|Opera|Safari|Trident|WebKit)#i',
            $user_agent ?? \getenv('HTTP_USER_AGENT') ?: ''
        ));
    }

    public static function sanitizeForUrl(array|string $haystack, bool $lowercase = true): array|string
    {
        $result = \preg_replace('/[^\da-z]+/im', '-', $haystack);
        $clean = fn (string $value): string => $lowercase ? \strtolower(\trim($value, '-')) : \trim($value, '-');
        return \is_string($result) ? $clean($result) : \array_map($clean, $result);
    }

    public static function stripExcessLines($buffer): string
    {
        return \preg_replace("/\n\n+/", "\n\n", $buffer);
    }

    public static function stripLeftPattern(string $haystack, string $needle): string
    {
        $needle_l = \strlen($needle);
        return \substr($haystack, 0, $needle_l) == $needle ? \substr($haystack, $needle_l) : $haystack;
    }

    public static function stripLinesWith(string $buffer, string $pattern): string
    {
        return \preg_replace("/\s+/", $pattern, $buffer);
    }

    public static function stripToSnippet(string $buffer, int $length): string
    {
        $buflen = \strlen($buffer);
        if ($buflen <= $length) return $buffer;
        return \preg_replace(
            "/\s+?(\S+)?$/",
            '',
            \substr($buffer, 0, $length - 2)
        ) . '...';
    }

    public static function stripUpTo(string $buffer, string $chr, int $len = 0): string
    {
        $i = \strpos($buffer, $chr);
        if ($i === false && $len <= 0)
        {
            return $buffer;
        }
        else if ($i === false && $len > 0)
        {
            return self::stripToSnippet($buffer, $len);
        }
        else
        {
            return self::stripToSnippet(\substr($buffer, 0, $i), $len);
        }
    }
}
