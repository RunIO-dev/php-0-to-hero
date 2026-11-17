<?php

declare(strict_types=1);

// Exercise 50, advanced: flash messages of two kinds, several at once.

require_once __DIR__ . '/Session.php';

final readonly class Flash
{
    private const array TYPES = ['success', 'error'];

    public function __construct(private Session $session) {}

    public function add(string $type, string $text): void
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException("Unknown message type \"$type\"");
        }
        $messages = $this->read($this->session->get('flash'));
        $messages[] = ['type' => $type, 'text' => $text];
        $this->session->set('flash', json_encode($messages, JSON_THROW_ON_ERROR));
    }

    /** @return list<array{type: string, text: string}> */
    public function all(): array
    {
        return $this->read($this->session->pull('flash'));
    }

    /** @return list<array{type: string, text: string}> */
    private function read(?string $json): array
    {
        $data = json_decode($json ?? '[]', true);
        if (!is_array($data) || !array_is_list($data)) {
            return [];
        }
        $messages = [];
        foreach ($data as $item) {
            if (!is_array($item) || !in_array($item['type'] ?? null, self::TYPES, true) || !is_string($item['text'] ?? null)) {
                return [];
            }
            $messages[] = ['type' => $item['type'], 'text' => $item['text']];
        }

        return $messages;
    }
}
