<?php

declare(strict_types=1);

// The same moment, shown in four time zones.
$event = new DateTimeImmutable('2026-11-17 18:00', new DateTimeZone('Europe/Berlin'));

foreach (['Europe/Berlin', 'America/Sao_Paulo', 'Asia/Tokyo', 'UTC'] as $zone) {
    $local = $event->setTimezone(new DateTimeZone($zone));
    echo str_pad($zone, 18), $local->format('Y-m-d H:i'), "\n";
}
