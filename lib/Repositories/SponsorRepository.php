<?php

declare(strict_types=1);

namespace Doctrine\Website\Repositories;

use Doctrine\SkeletonMapper\ObjectRepository\BasicObjectRepository;
use Doctrine\Website\Model\Sponsor;

/**
 * @template T of Sponsor
 * @template-extends BasicObjectRepository<T>
 */
class SponsorRepository extends BasicObjectRepository
{
    /** @return Sponsor[] */
    public function findAllOrderedByHighlighted(): array
    {
        /** @var Sponsor[] $sponsors */
        $sponsors = $this->findBy([], ['highlighted' => 'desc']);

        return $sponsors;
    }
}
