<?php

namespace Tests\Unit;

use App\Models\Apartament;
use PHPUnit\Framework\TestCase;

class ApartamentWatchlistTest extends TestCase
{
    public function test_watchlist_status_has_label_and_badge(): void
    {
        $apartament = new Apartament(['status' => 'de_urmarit']);

        $this->assertSame('De urmarit', $apartament->status_label);
        $this->assertSame('bg-warning text-dark', $apartament->status_badge);
    }

    public function test_watchlist_price_difference_is_calculated(): void
    {
        $apartament = new Apartament([
            'pret_initial' => 460000,
            'pret_curent' => 445000,
        ]);

        $this->assertSame(-15000, $apartament->watchlist_price_difference);
    }
}
