<?php

class Env
{
    private static array $vars = [];
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (self::$loaded) return;

        $file = rtrim($path, '/') . '/.env';
        if (!file_exists($file)) return;

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (preg_match('/^"(.*)"$/', $value, $m)) $value = $m[1];
            elseif (preg_match("/^'(.*)'$/", $value, $m)) $value = $m[1];

            self::$vars[$key] = $value;
            $_ENV[$key] = $value;
        }

        self::$loaded = true;
    }

    public static function get(string $key, string $default = ''): string
    {
        return self::$vars[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
