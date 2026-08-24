<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall\Tests\Unit;

use JohnRivera7\FilamentIssabelClickToCall\Support\ChilePhoneNormalizer;
use PHPUnit\Framework\TestCase;

final class ChilePhoneNormalizerTest extends TestCase
{
    public function test_normalizes_mobile_with_spaces(): void
    {
        $this->assertSame('56957592274', ChilePhoneNormalizer::normalize('9 5759 2274'));
    }

    public function test_normalizes_without_country_code_for_dial(): void
    {
        $this->assertSame('957592274', ChilePhoneNormalizer::normalize('957592274', withCountryCode: false));
    }

    public function test_returns_null_for_empty(): void
    {
        $this->assertNull(ChilePhoneNormalizer::normalize(''));
        $this->assertNull(ChilePhoneNormalizer::normalize(null));
    }

    public function test_formats_chile_mobile_for_display(): void
    {
        $this->assertSame('9 5517 0937', ChilePhoneNormalizer::formatLocalDisplay('+56955170937'));
    }

    public function test_for_dial_e164_cl(): void
    {
        $this->assertSame('56955170937', ChilePhoneNormalizer::forDial('+56955170937', 'e164_cl'));
        $this->assertSame('955170937', ChilePhoneNormalizer::forDial('+56955170937', 'local_9'));
        $this->assertSame('9955170937', ChilePhoneNormalizer::forDial('+56955170937', 'outside_9'));
    }
}
