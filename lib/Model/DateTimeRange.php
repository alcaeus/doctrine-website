<?php

declare(strict_types=1);

namespace Doctrine\Website\Model;

use DateTimeImmutable;

final class DateTimeRange
{
    private DateTimeImmutable $now;

    public function __construct(
        private DateTimeImmutable $start,
        private DateTimeImmutable $end,
        DateTimeImmutable|null $now = null,
    ) {
        $this->now = $now ?? new DateTimeImmutable();
    }

    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }

    public function isNow(): bool
    {
        return $this->start <= $this->now
            && $this->end > $this->now;
    }

    public function isOver(): bool
    {
        return $this->end < $this->now;
    }

    public function isUpcoming(): bool
    {
        return $this->start > $this->now;
    }

    public function getNumDays(): int
    {
        $days = (int) $this->end
            ->diff($this->start)
            ->days;

        if ($days > 0) {
            return $days + 1;
        }

        return 0;
    }

    public function getNumHours(): int
    {
        $diff = $this->end->diff($this->start);

        $numDays = $this->getNumDays();

        if ($numDays === 1) {
            return $diff->h;
        }

        return $diff->h + ($this->getNumDays() * 24);
    }

    public function getNumMinutes(): int
    {
        $diff = $this->end->diff($this->start);

        $minutes  = (int) $diff->days * 24 * 60;
        $minutes += $diff->h * 60;
        $minutes += $diff->i;

        return $minutes;
    }

    public function getDuration(): string
    {
        $numDays = $this->getNumDays();

        if ($numDays === 0) {
            $numMinutes = $this->getNumMinutes();

            if ($numMinutes >= 60) {
                return $this->getNumHours() . '-hour';
            }

            return $numMinutes . '-minute';
        }

        return $this->getNumDays() . '-day';
    }
}
