<?php
abstract class Controller {

    protected function view(string $view, array $viewData = []): void {
        extract($viewData, EXTR_SKIP);
        $file = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($file)) {
            http_response_code(404);
            die("View not found: {$view}");
        }
        require $file;
    }

    protected function url(string $path = ''): string {
        $path = ltrim($path, '/');
        return BASE_URL . ($path !== '' ? 'index.php/' . $path : '');
    }

    protected function redirect(string $path = ''): void {
        header('Location: ' . $this->url($path));
        exit;
    }

    protected function json(mixed $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function requireAuth(): void {
        if (empty($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Session expired. Please log in again.'], 401);
            }
            $this->redirect('login');
        }
    }

    protected function requireRole(string ...$roles): void {
        $this->requireAuth();
        if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Access denied.'], 403);
            }
            http_response_code(403);
            die('Access denied.');
        }
    }

    protected function flash(string $key, string $message): void {
        $_SESSION['flash'][$key] = $message;
    }

    protected function getFlash(string $key): ?string {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }

    public static function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '"/>';
    }

    protected function verifyCsrf(): void {

        $token = $_POST['csrf_token']
              ?? $_SERVER['HTTP_X_CSRF_TOKEN']
              ?? '';

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Invalid security token. Please refresh the page.'], 403);
            }
            http_response_code(403);
            die('Invalid security token. Please go back and try again.');
        }
    }

    protected function sanitize(string $value): string {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    protected function postInput(string $key, string $default = ''): string {
        return trim($_POST[$key] ?? $default);
    }

    protected function getInput(string $key, string $default = ''): string {
        return trim($_GET[$key] ?? $default);
    }
}