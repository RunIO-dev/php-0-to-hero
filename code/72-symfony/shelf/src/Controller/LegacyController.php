<?php

declare(strict_types=1);

namespace Shelf\Controller;

use Shelf\Accounts;
use Shelf\ApiTokens;
use Shelf\Config;
use Shelf\FileLogger;
use Shelf\Library;
use Shelf\PdoBookRepository;
use Shelf\PdoLoanRepository;
use Shelf\PdoMemberRepository;
use Shelf\PdoTokenRepository;
use Shelf\Web\App;
use Shelf\Web\Covers;
use Shelf\Web\PhpSession;
use Shelf\Web\Request;
use Shelf\Web\Response;
use Shelf\Web\Templates;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

use function Shelf\openDatabase;

/**
 * Chapter 72: Symfony answers every request now, and hands each one to the App of Parts 7 to 10,
 * unchanged: what the old front controller did is here. Chapter 73 moves the pages into
 * controllers of their own, and this one goes.
 */
final class LegacyController
{
    #[Route('/{path}', name: 'legacy', requirements: ['path' => '.*'], priority: -100)]
    public function __invoke(#[Autowire('%kernel.project_dir%')] string $dir): SymfonyResponse
    {
        $config = Config::load($dir);
        $logger = new FileLogger($config->log);
        $request = Request::fromGlobals();
        try {
            $db = openDatabase($config->database);
            $members = new PdoMemberRepository($db);
            $library = new Library(new PdoBookRepository($db), $members, new PdoLoanRepository($db));
            $tokens = new ApiTokens(new PdoTokenRepository($db), $members);
            $app = new App($library, new Accounts($members), new Templates("$dir/templates"), new PhpSession(), new Covers($config->covers), $tokens, $logger);
            $response = $app->handle($request);
        } catch (Throwable $e) {
            $response = Response::error($e, $logger, showDetails: !$config->isProduction(), api: str_starts_with($request->path, '/api/'));
        }

        return new SymfonyResponse($response->body, $response->status, $response->headers);
    }
}
