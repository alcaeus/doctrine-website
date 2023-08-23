<?php

declare(strict_types=1);

namespace Doctrine\Website\RST\Directive;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser;

class RoleDirective extends SubDirective
{
    public function getName(): string
    {
        return 'role';
    }

    /** @param string[] $options */
    public function processSub(
        Parser $parser,
        Node|null $document,
        string $variable,
        string $data,
        array $options,
    ): Node|null {
        return new WrapperNode($document, '<div class="role">', '</div>');
    }
}
