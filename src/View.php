<?php

class View
{
    private static string $viewsPath = '';

    public static function init(string $path): void
    {
        self::$viewsPath = rtrim($path, '/');
    }

    public static function render(string $view, array $data = [], string $layout = 'layout'): void
    {
        extract($data);

        $viewFile = self::$viewsPath . '/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "View not found: {$view}";
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = self::$viewsPath . '/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    public static function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public static function redirect(string $url): void
    {
        // Prepend BASE_URL for relative paths (starting with /)
        if (defined('BASE_URL') && str_starts_with($url, '/')) {
            $url = BASE_URL . $url;
        }
        header('Location: ' . $url);
        exit;
    }

    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
