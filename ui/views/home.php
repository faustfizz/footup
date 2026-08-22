<?php
/**
 * A plain-PHP template. Data passed to View::render() is extract()'ed
 * into local variables, so $title and $items below come straight from
 * HomeController::welcome(). Always escape output with htmlspecialchars()
 * unless you specifically want raw HTML.
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title ?? 'Micro Framework') ?></title>
</head>
<body>
    <h1><?= htmlspecialchars($title ?? '') ?></h1>
    <p>Rendered by <code>Footup\View</code> from <code>ui/views/home.php</code>.</p>

    <?php if (! empty($items)): ?>
        <ul>
            <?php foreach ($items as $item): ?>
                <li><?= htmlspecialchars($item) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
