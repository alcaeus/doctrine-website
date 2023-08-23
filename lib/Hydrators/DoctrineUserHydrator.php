<?php

declare(strict_types=1);

namespace Doctrine\Website\Hydrators;

use Doctrine\Website\Model\DoctrineUser;

/**
 * @property string $name
 * @property string $url
 * @template-extends ModelHydrator<DoctrineUser>
 */
final class DoctrineUserHydrator extends ModelHydrator
{
    /** @return class-string<DoctrineUser> */
    protected function getClassName(): string
    {
        return DoctrineUser::class;
    }

    /** @param mixed[] $data */
    protected function doHydrate(array $data): void
    {
        $this->name = (string) $data['name'];
        $this->url  = (string) $data['url'];
    }
}
