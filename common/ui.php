<?php
require_once __DIR__ . '/bootstrap.php';
function ui_assets(): string {
    $version = (string)filemtime(__DIR__ . '/interface.css');
    return '<link rel="icon" href="/common/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="/common/interface.css?v=' . $version . '">';
}
function ui_start(string $title, string $description = '', bool $wide = false): void {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . h($title) . ' | Johnny\'s</title>' . ui_assets() . '</head><body class="ui-body public-body">';
    echo '<header class="public-header"><a class="wordmark" href="/">JOHNNY\'S<span>Dining &amp; Bar</span></a><nav aria-label="Public navigation"><a href="/">Home</a><a href="/customerSide/CustomerReservation/reservePage.php">Reservations</a></nav></header>';
    echo '<main class="public-main' . ($wide ? ' public-main--wide' : '') . '"><section class="surface public-card">';
    ui_heading($title, $description);
}
function ui_end(): void { echo '</section></main><footer class="public-footer">Johnny\'s Dining &amp; Bar &middot; ' . date('Y') . '</footer></body></html>'; }
function ui_heading(string $title, string $description = '', string $eyebrow = ''): void {
    echo '<div class="page-heading">';
    if ($eyebrow !== '') echo '<p class="eyebrow">' . h($eyebrow) . '</p>';
    echo '<h1>' . h($title) . '</h1>';
    if ($description !== '') echo '<p class="page-description">' . h($description) . '</p>';
    echo '</div>';
}
function ui_field(string $name, string $label, array $options = []): void {
    $id = $options['id'] ?? $name; $type = $options['type'] ?? 'text';
    $error = $options['error'] ?? ''; $hint = $options['hint'] ?? '';
    echo '<div class="field' . (!empty($options['full']) ? ' field--full' : '') . '"><label for="' . h($id) . '">' . h($label) . '</label>';
    $attrs = ' id="' . h($id) . '" name="' . h($name) . '" class="form-control' . ($error !== '' ? ' is-invalid' : '') . '"';
    foreach (['placeholder','autocomplete','min','max','step','maxlength','minlength','pattern','inputmode'] as $attribute) if (isset($options[$attribute])) $attrs .= ' ' . $attribute . '="' . h($options[$attribute]) . '"';
    if ($options['required'] ?? true) $attrs .= ' required';
    if (!empty($options['readonly'])) $attrs .= ' readonly';
    if ($error !== '') $attrs .= ' aria-invalid="true"';
    if ($error !== '' || $hint !== '') $attrs .= ' aria-describedby="' . h($id) . '-help"';
    if (isset($options['choices'])) {
        echo '<select' . $attrs . '><option value="">Choose an option</option>';
        foreach ($options['choices'] as $key => $value) {
            if (is_int($key)) $key = $value;
            echo '<option value="' . h($key) . '"' . ((string)($options['value'] ?? '') === (string)$key ? ' selected' : '') . '>' . h($value) . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'textarea') echo '<textarea rows="4"' . $attrs . '>' . h($options['value'] ?? '') . '</textarea>';
    else echo '<input type="' . h($type) . '"' . $attrs . ($type !== 'password' ? ' value="' . h($options['value'] ?? '') . '"' : '') . '>';
    if ($error !== '' || $hint !== '') echo '<p id="' . h($id) . '-help" class="field-help' . ($error !== '' ? ' field-error' : '') . '">' . h($error ?: $hint) . '</p>';
    echo '</div>';
}
function ui_actions(string $label, string $back, string $backLabel = 'Cancel'): void {
    echo '<div class="form-actions"><button type="submit" class="btn btn-primary">' . h($label) . '</button><a class="btn btn-secondary" href="' . h($back) . '">' . h($backLabel) . '</a></div>';
}
function ui_empty(string $title, string $detail = ''): void {
    echo '<div class="empty-state"><p class="empty-title">' . h($title) . '</p>';
    if ($detail !== '') echo '<p>' . h($detail) . '</p>';
    echo '</div>';
}
function ui_table_start(array $headings, string $caption): void {
    echo '<div class="table-wrap" role="region" aria-label="' . h($caption) . '" tabindex="0"><table class="data-table" style="--table-min:' . max(480, count($headings) * 120) . 'px"><caption class="sr-only">' . h($caption) . '</caption><thead><tr>';
    foreach ($headings as $heading) echo '<th scope="col">' . h($heading) . '</th>';
    echo '</tr></thead><tbody>';
}
function ui_table_end(): void { echo '</tbody></table></div>'; }
