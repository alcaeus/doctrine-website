<?php

declare(strict_types=1);

namespace Doctrine\Website\EventListener;

use Doctrine\RST\Event\PreNodeRenderEvent;
use Doctrine\RST\Nodes\Node;

use function str_replace;

final readonly class NodeValue
{
    public function preNodeRender(PreNodeRenderEvent $event): void
    {
        $value = $event->getNode()->getValue();

        if (! $value instanceof Node) {
            return;
        }

        $valueString = str_replace('"', '', $value->getValueString());
        $value->setValue($valueString);
    }
}
