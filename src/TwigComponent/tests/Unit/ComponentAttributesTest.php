<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\UX\StimulusBundle\Dto\StimulusAttributes;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Symfony\WebpackEncoreBundle\Dto\AbstractStimulusDto;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ComponentAttributesTest extends TestCase
{
    public function testCanConvertToString(): void
    {
        $attributes = new ComponentAttributes([
            'class' => 'foo',
            'style' => 'color:black;',
            'value' => '',
            'autofocus' => true,
        ]);

        $this->assertSame(' class="foo" style="color:black;" value="" autofocus', (string) $attributes);
    }

    public function testCanSetDefaults(): void
    {
        $attributes = new ComponentAttributes(['class' => 'foo', 'style' => 'color:black;']);

        $this->assertSame(
            ['class' => 'bar foo', 'style' => 'color:black;'],
            $attributes->defaults(['class' => 'bar', 'style' => 'font-size: 10;'])->all()
        );
        $this->assertSame(
            ' class="bar foo" style="color:black;"',
            (string) $attributes->defaults(['class' => 'bar', 'style' => 'font-size: 10;'])
        );

        $this->assertSame(['class' => 'foo'], (new ComponentAttributes([]))->defaults(['class' => 'foo'])->all());
    }

    public function testCanGetOnly(): void
    {
        $attributes = new ComponentAttributes(['class' => 'foo', 'style' => 'color:black;']);

        $this->assertSame(['class' => 'foo'], $attributes->only('class')->all());
    }

    public function testCanGetWithout(): void
    {
        $attributes = new ComponentAttributes(['class' => 'foo', 'style' => 'color:black;']);

        $this->assertSame(['class' => 'foo'], $attributes->without('style')->all());
    }

    /**
     * @group legacy
     */
    public function testCanAddStimulusController(): void
    {
        $attributes = new ComponentAttributes([
            'class' => 'foo',
            'data-controller' => 'live',
            'data-live-data-value' => '{}',
        ]);

        $controllerDto = $this->createMock(AbstractStimulusDto::class);
        $controllerDto->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'data-controller' => 'foo bar',
                'data-foo-name-value' => 'ryan',
            ]);

        $attributes = $attributes->add($controllerDto);

        $this->assertEquals([
            'class' => 'foo',
            'data-controller' => 'live foo bar',
            'data-live-data-value' => '{}',
            'data-foo-name-value' => 'ryan',
        ], $attributes->all());
    }

    /**
     * @group legacy
     */
    public function testCanAddStimulusControllerIfNoneAlreadyPresent(): void
    {
        $attributes = new ComponentAttributes([
            'class' => 'foo',
        ]);

        $controllerDto = $this->createMock(AbstractStimulusDto::class);
        $controllerDto->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'data-controller' => 'foo bar',
                'data-foo-name-value' => 'ryan',
            ]);

        $attributes = $attributes->add($controllerDto);

        $this->assertEquals([
            'class' => 'foo',
            'data-controller' => 'foo bar',
            'data-foo-name-value' => 'ryan',
        ], $attributes->all());
    }

    public function testCanAddStimulusControllerViaStimulusAttributes(): void
    {
        // if PHP less than 8.1, skip
        if (version_compare(\PHP_VERSION, '8.1.0', '<')) {
            $this->markTestSkipped('PHP 8.1+ required');
        }

        $attributes = new ComponentAttributes([
            'class' => 'foo',
            'data-controller' => 'live',
            'data-live-data-value' => '{}',
        ]);

        $stimulusAttributes = new StimulusAttributes(new Environment(new ArrayLoader()));
        $stimulusAttributes->addController('foo', ['name' => 'ryan', 'some_array' => ['a', 'b']]);
        $attributes = $attributes->defaults($stimulusAttributes);

        $this->assertEquals([
            'class' => 'foo',
            'data-controller' => 'foo live',
            'data-live-data-value' => '{}',
            'data-foo-name-value' => 'ryan',
            'data-foo-some-array-value' => '&#x5B;&quot;a&quot;,&quot;b&quot;&#x5D;',
        ], $attributes->all());
    }

    public function testCanAddStimulusActionViaStimulusAttributes(): void
    {
        // if PHP less than 8.1, skip
        if (version_compare(\PHP_VERSION, '8.1.0', '<')) {
            $this->markTestSkipped('PHP 8.1+ required');
        }

        $attributes = new ComponentAttributes([
            'class' => 'foo',
            'data-action' => 'live#foo',
        ]);

        $stimulusAttributes = new StimulusAttributes(new Environment(new ArrayLoader()));
        $stimulusAttributes->addAction('foo', 'barMethod');
        $attributes = $attributes->defaults([...$stimulusAttributes]);

        $this->assertEquals([
            'class' => 'foo',
            'data-action' => 'foo#barMethod live#foo',
        ], $attributes->all());
    }

    public function testBooleanBehaviour(): void
    {
        $attributes = new ComponentAttributes(['disabled' => true]);

        $this->assertSame(['disabled' => true], $attributes->all());
        $this->assertSame(' disabled', (string) $attributes);

        $attributes = new ComponentAttributes(['disabled' => false]);

        $this->assertSame(['disabled' => false], $attributes->all());
        $this->assertSame('', (string) $attributes);
    }

    /**
     * @group legacy
     */
    public function testNullBehaviour(): void
    {
        $attributes = new ComponentAttributes(['disabled' => null]);

        $this->assertSame(['disabled' => null], $attributes->all());
        $this->assertSame(' disabled', (string) $attributes);
    }

    public function testCannotPassNonScalarValues(): void
    {
        $this->expectException(\LogicException::class);

        (string) new ComponentAttributes(['data-foo' => new \stdClass()]);
    }

    public function testListAttributeValues(): void
    {
        $attributes = new ComponentAttributes([
            'style' => ['foo', null, false, true, '', new \stdClass(), 'bar'],
            'class' => ['baz', ''],
        ]);

        $this->assertSame(' style="foo bar" class="baz"', (string) $attributes);
        $this->assertSame(['style' => 'foo bar', 'class' => 'baz'], $attributes->all());

        $attributes = $attributes->defaults([
            'style' => ['baz', false],
            'class' => ['', 'qux'],
        ]);

        $this->assertSame(' style="foo bar" class="qux baz"', (string) $attributes);
        $this->assertSame(['style' => 'foo bar', 'class' => 'qux baz'], $attributes->all());
    }
}
