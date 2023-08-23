<?php

declare(strict_types=1);

namespace Doctrine\Website\RST\Directive;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\RawNode;
use Doctrine\RST\Parser;

class TocHeaderDirective extends SubDirective
{
    public function getName(): string
    {
        return 'tocheader';
    }

    /** @param string[] $options */
    public function processSub(
        Parser $parser,
        Node|null $document,
        string $variable,
        string $data,
        array $options,
    ): Node|null {
        return new RawNode('<h2 class="toc-header">' . $data . '</h2>');
    }
}
