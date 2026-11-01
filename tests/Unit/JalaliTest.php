<?php

namespace Tests\Unit;

use App\Support\Jalali;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JalaliTest extends TestCase
{
    /** @return array<string, array{0:int[],1:int[]}> */
    public static function knownDates(): array
    {
        return [
            'Nowruz 1403' => [[2024, 3, 20], [1403, 1, 1]],
            'last day of leap 1403' => [[2025, 3, 20], [1403, 12, 30]],
            'Nowruz 1404' => [[2025, 3, 21], [1404, 1, 1]],
            'Nowruz 1405' => [[2026, 3, 21], [1405, 1, 1]],
            'Aban 1405' => [[2026, 10, 28], [1405, 8, 6]],
            'Y2K' => [[2000, 3, 20], [1379, 1, 1]],
        ];
    }

    /** @param int[] $g @param int[] $j */
    #[DataProvider('knownDates')]
    public function test_known_reference_dates(array $g, array $j): void
    {
        $this->assertSame($j, Jalali::fromGregorian(...$g));
        $this->assertSame($g, Jalali::toGregorian(...$j));
    }

    public function test_every_day_round_trips_and_advances_by_one(): void
    {
        $day = new \DateTimeImmutable('1990-01-01');
        $previous = null;
        for ($i = 0; $i < 20000; $i++, $day = $day->modify('+1 day')) {
            $g = [(int) $day->format('Y'), (int) $day->format('n'), (int) $day->format('j')];
            $j = Jalali::fromGregorian(...$g);
            $this->assertSame($g, Jalali::toGregorian(...$j));
            if ($previous !== null) {
                $this->assertGreaterThan($previous, $j[0] * 10000 + $j[1] * 100 + $j[2]);
            }
            $previous = $j[0] * 10000 + $j[1] * 100 + $j[2];
        }
    }

    public function test_format(): void
    {
        $this->assertSame('1405/08/06', Jalali::format(new \DateTimeImmutable('2026-10-28')));
    }
}
