<?php
/**
 * @var string $title
 * @var string $message
 */
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-lg mx-auto">
        <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($title) ?></h1>
        
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($message) ?>
        </div>
        
        <a href="/" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Back to Home
        </a>
    </div>
</div>
