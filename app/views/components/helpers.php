<?php
/**
 * Badge Component - Reusable status badge
 * 
 * Usage:
 * include VIEW_PATH . '/components/badge.php';
 * echo renderBadge('ACTIF', 'membre');
 * echo renderBadge('PAYE', 'versement');
 */

function renderBadge(string $status, string $type = 'membre'): string
{
    $classes = '';
    $label = htmlspecialchars($status);
    
    if ($type === 'membre') {
        $classes = match($status) {
            'ACTIF' => 'bg-green-100 text-green-800',
            'VG' => 'bg-gray-100 text-gray-800',
            'SUSPENDU' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    } elseif ($type === 'versement') {
        $classes = match($status) {
            'PAYE' => 'bg-green-100 text-green-800',
            'PARTIEL' => 'bg-orange-100 text-orange-800',
            'EN_ATTENTE' => 'bg-red-100 text-red-800',
            'ANNULE' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
    
    return sprintf(
        '<span class="inline-block px-3 py-1 text-xs font-medium %s rounded-full">%s</span>',
        $classes,
        $label
    );
}

/**
 * Format currency
 */
function formatCurrency(float $amount): string
{
    return number_format($amount, 0, ',', ' ') . ' FCFA';
}

/**
 * Format date
 */
function formatDate(?string $date, string $format = 'd/m/Y'): string
{
    if (!$date) {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Get month name in French
 */
function getMonthName(string $month): string
{
    $months = [
        'janvier' => 'Janvier',
        'février' => 'Février',
        'mars' => 'Mars',
        'avril' => 'Avril',
        'mai' => 'Mai',
        'juin' => 'Juin',
        'juillet' => 'Juillet',
        'août' => 'Août',
        'septembre' => 'Septembre',
        'octobre' => 'Octobre',
        'novembre' => 'Novembre',
        'décembre' => 'Décembre'
    ];
    
    return $months[strtolower($month)] ?? ucfirst($month);
}

/**
 * Render KPI card
 */
function renderKpiCard(string $title, $value, string $icon, string $color = 'blue', ?string $subtitle = null): string
{
    $colorClasses = [
        'blue' => 'bg-blue-100 text-blue-600',
        'green' => 'bg-green-100 text-green-600',
        'red' => 'bg-red-100 text-red-600',
        'orange' => 'bg-orange-100 text-orange-600',
        'gray' => 'bg-gray-100 text-gray-600'
    ];
    
    $bgClass = $colorClasses[$color] ?? $colorClasses['blue'];
    
    $html = '<div class="bg-white rounded-2xl border border-gray-200 p-6">';
    $html .= '<div class="flex items-center justify-between">';
    $html .= '<div>';
    $html .= '<p class="text-sm font-medium text-gray-600">' . htmlspecialchars($title) . '</p>';
    $html .= '<p class="text-3xl font-semibold text-gray-900 mt-2">' . htmlspecialchars($value) . '</p>';
    $html .= '</div>';
    $html .= '<div class="' . $bgClass . ' rounded-full p-3">';
    $html .= $icon;
    $html .= '</div>';
    $html .= '</div>';
    
    if ($subtitle) {
        $html .= '<p class="mt-4 text-sm text-gray-600">' . $subtitle . '</p>';
    }
    
    $html .= '</div>';
    
    return $html;
}
