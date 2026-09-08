<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall\Tests\Unit;

use JohnRivera7\FilamentIssabelClickToCall\Support\AmiExtensionState;
use PHPUnit\Framework\TestCase;

final class AmiExtensionStateTest extends TestCase
{
    public function test_idle_and_unavailable_do_not_occupy_the_extension(): void
    {
        $this->assertFalse(AmiExtensionState::occupies(AmiExtensionState::IDLE));
        $this->assertFalse(AmiExtensionState::occupies(AmiExtensionState::UNAVAILABLE));
    }

    public function test_ringing_talking_and_hold_occupy_the_extension(): void
    {
        $this->assertTrue(AmiExtensionState::occupies(AmiExtensionState::IN_USE));
        $this->assertTrue(AmiExtensionState::occupies(AmiExtensionState::BUSY));
        $this->assertTrue(AmiExtensionState::occupies(AmiExtensionState::RINGING));
        $this->assertTrue(AmiExtensionState::occupies(AmiExtensionState::ON_HOLD));
        // InUse + Ringing (second call arriving while talking).
        $this->assertTrue(AmiExtensionState::occupies(9));
    }

    public function test_missing_hint_is_not_treated_as_idle(): void
    {
        $this->assertFalse(AmiExtensionState::isKnown(AmiExtensionState::NOT_FOUND));
        $this->assertFalse(AmiExtensionState::occupies(AmiExtensionState::NOT_FOUND));
    }

    public function test_matches_sip_pjsip_and_local_channels(): void
    {
        $this->assertTrue(AmiExtensionState::channelBelongsTo(['Channel' => 'SIP/2150-0000a1b2'], '2150'));
        $this->assertTrue(AmiExtensionState::channelBelongsTo(['Channel' => 'PJSIP/2150-00000042'], '2150'));
        $this->assertTrue(AmiExtensionState::channelBelongsTo(
            ['Channel' => 'Local/2150@filament-click-to-call-00000003;1'],
            '2150',
        ));
    }

    public function test_does_not_match_a_different_extension(): void
    {
        $this->assertFalse(AmiExtensionState::channelBelongsTo(['Channel' => 'SIP/2151-0000a1b2'], '2150'));
        $this->assertFalse(AmiExtensionState::channelBelongsTo(['Channel' => 'SIP/21500-0000a1b2'], '2150'));
    }

    public function test_matches_by_caller_id_and_destination_channel(): void
    {
        $this->assertTrue(AmiExtensionState::channelBelongsTo(
            ['Channel' => 'SIP/trunk-0001', 'DestinationChannel' => 'SIP/2150-0002'],
            '2150',
        ));
        $this->assertTrue(AmiExtensionState::channelBelongsTo(
            ['Channel' => 'SIP/trunk-0001', 'CallerIDNum' => '2150'],
            '2150',
        ));
    }

    public function test_empty_extension_never_matches(): void
    {
        $this->assertFalse(AmiExtensionState::channelBelongsTo(['Channel' => 'SIP/2150-0001'], ''));
    }
}
