<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Tests\Integration\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Console\Test\InteractsWithConsole;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ListIconsCommandTest extends KernelTestCase
{
    use InteractsWithConsole;

    public function testListLocalSvgIcons(): void
    {
        $this->executeConsoleCommand('ux:icons:list')
            ->assertSuccessful()
            ->assertOutputContains('Details for Icon Pack "(root)"')
            ->assertOutputContains('Prefix    (none)')
            ->assertOutputContains('# Icons   1')
            ->assertOutputContains('Available Icon Packs')
            ->assertOutputContains('(root)      1')
            ->assertOutputContains('sub         1')
            ->assertOutputContains('heroicons   2')
        ;
    }

    public function testListingPackDetails(): void
    {
        $this->executeConsoleCommand('ux:icons:list heroicons')
            ->assertSuccessful()
            ->assertOutputContains('Details for Icon Pack "heroicons"')
            ->assertOutputContains('Prefix    heroicons')
            ->assertOutputContains('# Icons   2')
            ->assertOutputContains('Name      HeroIcons')
            ->assertOutputContains('Version   2.1.1')
            ->assertOutputContains('Author    Refactoring UI Inc <https://github.com/tailwindlabs/heroicons>')
            ->assertOutputContains('License   MIT <https://github.com/tailwindlabs/heroicons/blob/master/LICENSE>')
            ->assertOutputContains('Available Icon Packs')
        ;
    }

    public function testInvalidPack(): void
    {
        $this->executeConsoleCommand('ux:icons:list invalid')
            ->assertSuccessful()
            ->assertOutputContains('[ERROR] The icon pack "invalid" does not exist.')
            ->assertOutputContains('Available Icon Packs')
        ;
    }
}
