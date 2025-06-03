<?php

namespace Tests\Misery\Component\Modifier;

use Misery\Component\Modifier\ReferenceCodeModifier;
use PHPUnit\Framework\TestCase;

class ReferenceCodeModifierTest extends TestCase
{
    function test_it_should_reference_code_a_value(): void
    {
        $modifier = new ReferenceCodeModifier();

        $this->assertEquals('myNewValue', $modifier->modify('myNewValue'));
        $this->assertEquals('My_New_Value', $modifier->modify('My-New-Value'));

        $this->assertEquals('myNewValue___And_His_New_Value', $modifier->modify('myNewValue   And His New Value'));

        $this->assertEquals('myNewValue_And_His_New_Value1', $modifier->modify('@myNewValue And His New Value1'));

        $this->assertEquals('myNewValue_And_His__New__Value1', $modifier->modify('@myNewValue$And&His{}New[]Value1'));

        // ───────────────────────────────────────────────────────────────────
        // New assertions for accent‐to‐ASCII transliteration
        $this->assertEquals('geintegreerd',  $modifier->modify('geïntegreerd'));
        $this->assertEquals('Geintegreerd',  $modifier->modify('Geïntegreerd'));
        $this->assertEquals('Uber_Cool',     $modifier->modify('Über Cool'));
        $this->assertEquals('Strasse',       $modifier->modify('Straße'));
        $modifier->setOption('use_pattern', 'new_pattern');
        $this->assertEquals('220_230V_max_300W_Niet_dimbaar', $modifier->modify('220-230V / max 300W / Niet dimbaar'));
        // more complex cases
        $this->assertEquals('simple', $modifier->modify('simple'));
        $this->assertEquals('Simple', $modifier->modify('Simple'));
        $this->assertEquals('simple_test', $modifier->modify('simple test'));
        $this->assertEquals('simple_test_123', $modifier->modify('simple test 123'));
        $this->assertEquals('A_B_C', $modifier->modify('A B C'));
        $this->assertEquals('A_B_C', $modifier->modify('A-B-C'));
        $this->assertEquals('A_B_C', $modifier->modify('A_B_C'));
        $this->assertEquals('A_B_C', $modifier->modify('A--B__C'));
        $this->assertEquals('A_B_C', $modifier->modify('A!@#B$%^C&*()'));
        $this->assertEquals('A_B_C', $modifier->modify('A   B   C'));
        $this->assertEquals('A_B_C', $modifier->modify('A/B\\C'));
        $this->assertEquals('A_B_C_C', $modifier->modify('A|B:C;C'));
        $this->assertEquals('A_B_C', $modifier->modify('A.B,C'));
        $this->assertEquals('AssBCC', $modifier->modify('AßBÇC'));
        $this->assertEquals('AeBeC', $modifier->modify('AéBèC'));
        $this->assertEquals('A_B_C', $modifier->modify('A--B--C--'));
        $this->assertEquals('A_B_C', $modifier->modify('---A---B---C---'));
        $this->assertEquals('A_B_C', $modifier->modify('A___B___C'));
    }
}