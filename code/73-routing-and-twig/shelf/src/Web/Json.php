<?php

declare(strict_types=1);

namespace Shelf\Web;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Answers for programs: JSON (chapter 67), and errors as problem details (chapter 68), as
 * Web\Response made them before chapter 73. Readable, with slashes and letters as they are.
 */
final class Json
{
    private const int FLAGS = JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    /** @param array<string, string> $headers */
    public static function response(mixed $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse(json_encode($data, self::FLAGS) . "\n", $status, $headers, json: true);
    }

    /**
     * Problem details, as RFC 9457 describes them. Without a $type of its own, the problem is just the
     * status: "about:blank", with the status's usual name as $title.
     *
     * @param array<string, mixed> $more more members, such as "errors"
     * @param array<string, string> $headers such as Allow or WWW-Authenticate
     */
    public static function problem(int $status, string $title, string $detail, string $type = 'about:blank', array $more = [], array $headers = []): JsonResponse
    {
        $problem = ['type' => $type, 'title' => $title, 'status' => $status, 'detail' => $detail, ...$more];

        return self::response($problem, $status, ['Content-Type' => 'application/problem+json', ...$headers]);
    }
}
