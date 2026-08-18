<?php

if (!function_exists('nl2br_markdown')) {
    /**
     * Convert a simplified Markdown string to HTML for display in Blade views.
     * Supports: headings, bold, italic, tables, horizontal rules, lists, paragraphs.
     * Also highlights {{placeholder}} tokens with a styled span.
     */
    function nl2br_markdown(string $text): string
    {
        $lines = explode("\n", $text);
        $html  = '';
        $inTable = false;
        $inList  = false;

        foreach ($lines as $line) {
            $raw = rtrim($line);

            // --- Highlight {{placeholders}} ---
            $raw = preg_replace(
                '/\{\{([^}]+)\}\}/',
                '<span class="placeholder">{{$1}}</span>',
                $raw
            );

            // --- Table rows ---
            if (preg_match('/^\|/', $raw)) {
                // Skip separator rows like |---|---|
                if (preg_match('/^\|[\s\-:]+\|/', $raw)) {
                    continue;
                }
                if (!$inTable) {
                    if ($inList) { $html .= '</ul>'; $inList = false; }
                    $html .= '<table>';
                    $cells = array_filter(explode('|', $raw), fn($c) => trim($c) !== '');
                    // First table row = header
                    $html .= '<tr>';
                    foreach ($cells as $cell) {
                        $html .= '<th>' . trim($cell) . '</th>';
                    }
                    $html .= '</tr>';
                    $inTable = true;
                } else {
                    $cells = array_filter(explode('|', $raw), fn($c) => trim($c) !== '');
                    $html .= '<tr>';
                    foreach ($cells as $cell) {
                        $html .= '<td>' . trim($cell) . '</td>';
                    }
                    $html .= '</tr>';
                }
                continue;
            } else {
                if ($inTable) {
                    $html .= '</table>';
                    $inTable = false;
                }
            }

            // --- Headings ---
            if (preg_match('/^### (.+)$/', $raw, $m)) {
                if ($inList) { $html .= '</ul>'; $inList = false; }
                $html .= '<h3>' . applyInline($m[1]) . '</h3>';
                continue;
            }
            if (preg_match('/^## (.+)$/', $raw, $m)) {
                if ($inList) { $html .= '</ul>'; $inList = false; }
                $html .= '<h2>' . applyInline($m[1]) . '</h2>';
                continue;
            }
            if (preg_match('/^# (.+)$/', $raw, $m)) {
                if ($inList) { $html .= '</ul>'; $inList = false; }
                $html .= '<h1>' . applyInline($m[1]) . '</h1>';
                continue;
            }

            // --- Horizontal rule ---
            if (preg_match('/^---+$/', $raw)) {
                if ($inList) { $html .= '</ul>'; $inList = false; }
                $html .= '<hr>';
                continue;
            }

            // --- List items ---
            if (preg_match('/^[-*] (.+)$/', $raw, $m)) {
                if (!$inList) { $html .= '<ul>'; $inList = true; }
                $html .= '<li>' . applyInline($m[1]) . '</li>';
                continue;
            }
            if (preg_match('/^\d+\.\d* (.+)$/', $raw, $m)) {
                if (!$inList) { $html .= '<ul>'; $inList = true; }
                $html .= '<li>' . applyInline($m[1]) . '</li>';
                continue;
            }

            // --- Numbered list items like "3.1 ..." ---
            if (preg_match('/^\d+\.\d+ (.+)$/', $raw, $m)) {
                if (!$inList) { $html .= '<ul>'; $inList = true; }
                $html .= '<li>' . applyInline($m[1]) . '</li>';
                continue;
            }

            // --- Blank line ---
            if (trim($raw) === '') {
                if ($inList) { $html .= '</ul>'; $inList = false; }
                continue;
            }

            // --- Paragraph ---
            if ($inList) { $html .= '</ul>'; $inList = false; }
            $html .= '<p>' . applyInline($raw) . '</p>';
        }

        if ($inTable) $html .= '</table>';
        if ($inList)  $html .= '</ul>';

        return $html;
    }
}

if (!function_exists('applyInline')) {
    function applyInline(string $text): string
    {
        // Bold+Italic
        $text = preg_replace('/\*\*\*(.+?)\*\*\*/', '<strong><em>$1</em></strong>', $text);
        // Bold
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        // Italic
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
        // Inline code
        $text = preg_replace('/`(.+?)`/', '<code>$1</code>', $text);
        return $text;
    }
}
