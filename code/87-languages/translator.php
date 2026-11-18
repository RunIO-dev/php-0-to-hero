<?php

declare(strict_types=1);

// Symfony's translator, without the rest of Symfony (chapter 87). The key is the English text; each
// language has a catalog for it. A text that a catalog doesn't have falls back to English, so a page
// shows English, never a key.

require __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

$translator = new Translator('en');
$translator->setFallbackLocales(['en']);
$translator->addLoader('array', new ArrayLoader());
// The "+intl-icu" domain: the texts are ICU messages, formatted by MessageFormatter.
$translator->addResource('array', [
    'Welcome, {name}.' => 'Welcome, {name}.',
    'Log out' => 'Log out',
], 'en', 'messages+intl-icu');
$translator->addResource('array', [
    'Welcome, {name}.' => 'Willkommen, {name}.',
], 'de', 'messages+intl-icu');
$translator->addResource('array', [
    'Welcome, {name}.' => 'أهلًا بك يا {name}.',
    'Log out' => 'تسجيل الخروج',
], 'ar', 'messages+intl-icu');

foreach (['en', 'de', 'ar'] as $language) {
    $welcome = $translator->trans('Welcome, {name}.', ['name' => 'Amira'], locale: $language);
    echo $language, ': ', $welcome, ' / ', $translator->trans('Log out', locale: $language), "\n";
}
