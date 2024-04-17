<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\Injections;

use Tempest\Console\Highlight\IsTagInjection;
use Tempest\Highlight\Injection;
use Tempest\Highlight\Themes\TerminalStyle;

final readonly class ErrorInjection implements Injection
{
    use IsTagInjection;

    public function getTag(): string
    {
        return 'error';
    }

    public function style(string $content): string
    {
        return TerminalStyle::BOLD(TerminalStyle::FG_WHITE(TerminalStyle::BG_RED(TerminalStyle::FG_WHITE(" {$content} "))));
    }
}
