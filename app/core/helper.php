<?php
// ============================================================
// ULMS — Helper Utilities
// Core Layer: core/Helper.php
// ============================================================

class Helper {

    /** Sanitize output to prevent XSS */
    public static function e(mixed $value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** Redirect to a URL */
    public static function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    /** Redirect back with an error */
    public static function redirectWithError(string $url, string $msg): void {
        Session::flash('error', $msg);
        self::redirect($url);
    }

    /** Redirect back with a success message */
    public static function redirectWithSuccess(string $url, string $msg): void {
        Session::flash('success', $msg);
        self::redirect($url);
    }

    /** Format a date string */
    public static function formatDate(?string $date, string $format = DATE_FORMAT): string {
        if (!$date) return '—';
        return date($format, strtotime($date));
    }

    /** Format currency */
    public static function formatCurrency(float $amount): string {
        return 'Rs. ' . number_format($amount, 2);
    }

    /** Calculate overdue days from due_date to today (or return_date) */
    public static function calculateOverdueDays(string $dueDate, ?string $returnDate = null): int {
        $due    = new DateTime($dueDate);
        $target = $returnDate ? new DateTime($returnDate) : new DateTime('today');
        $diff   = $due->diff($target);
        // If due is in the future (diff negative direction), 0 days
        if ($target <= $due) return 0;
        return (int)$diff->days;
    }

    /** Calculate fine amount */
    public static function calculateFine(int $overdueDays): float {
        return $overdueDays * FINE_PER_DAY;
    }

    /** Get a summary badge HTML for loan status */
    public static function loanStatusBadge(string $status): string {
        $map = [
            'active'   => 'badge-active',
            'returned' => 'badge-returned',
            'overdue'  => 'badge-overdue',
        ];
        $class = $map[$status] ?? 'badge-default';
        return '<span class="badge ' . $class . '">' . ucfirst(self::e($status)) . '</span>';
    }

    /** Get a summary badge for reservation status */
    public static function reservationStatusBadge(string $status): string {
        $map = [
            'pending'   => 'badge-warning',
            'fulfilled' => 'badge-returned',
            'cancelled' => 'badge-overdue',
        ];
        $class = $map[$status] ?? 'badge-default';
        return '<span class="badge ' . $class . '">' . ucfirst(self::e($status)) . '</span>';
    }

    /** Get fine status badge */
    public static function fineStatusBadge(string $status): string {
        $class = $status === 'paid' ? 'badge-returned' : 'badge-overdue';
        return '<span class="badge ' . $class . '">' . ucfirst(self::e($status)) . '</span>';
    }

    /** Generate a pagination HTML string */
    public static function paginate(int $total, int $perPage, int $current, string $url): string {
        $pages = (int)ceil($total / $perPage);
        if ($pages <= 1) return '';
        $html = '<nav class="pagination">';
        for ($i = 1; $i <= $pages; $i++) {
            $active = $i === $current ? ' active' : '';
            $html .= '<a href="' . $url . '&page=' . $i . '" class="page-btn' . $active . '">' . $i . '</a>';
        }
        $html .= '</nav>';
        return $html;
    }

    /** Slugify a string */
    public static function slug(string $str): string {
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        return preg_replace('/-+/', '-', $str);
    }

    /** Get current IP address */
    public static function getIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /** Truncate string */
    public static function truncate(string $text, int $length = 60): string {
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }
}
