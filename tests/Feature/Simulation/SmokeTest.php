<?php

namespace Tests\Feature\Simulation;

use App\Models\ClubConfig;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_migrations_apply_and_club_config_singleton_works(): void
    {
        $cfg = $this->clubConfig();

        $this->assertSame(1, $cfg->current_day);
        $this->assertSame(75, $cfg->reputation);
        $this->assertSame(1, ClubConfig::singleton()->id);
    }

    public function test_club_config_fixture_applies_overrides(): void
    {
        $cfg = $this->clubConfig(['bad_day_mode' => true, 'reputation' => 40]);

        $this->assertTrue($cfg->bad_day_mode);
        $this->assertSame(40, $cfg->reputation);
        $this->assertSame(1, $cfg->current_day);
    }
}
