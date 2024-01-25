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
use Symfony\Component\Filesystem\Filesystem;
use Zenstruck\Console\Test\InteractsWithConsole;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ImportIconCommandTest extends KernelTestCase
{
    use InteractsWithConsole;

    private const ICON_DIR = __DIR__.'/../../Fixtures/icons';
    private const ICONS = ['dashboard.svg', 'renamed.svg'];

    /**
     * @before
     *
     * @after
     */
    public static function cleanup(): void
    {
        $fs = new Filesystem();

        foreach (self::ICONS as $icon) {
            $fs->remove(self::ICON_DIR.'/'.$icon);
        }
    }

    public function testCanImportIcon(): void
    {
        $this->assertFileDoesNotExist($expectedFile = self::ICON_DIR.'/dashboard.svg');

        $this->executeConsoleCommand('ux:icons:import uiw:dashboard')
            ->assertSuccessful()
            ->assertOutputContains('Importing uiw:dashboard as dashboard')
            ->assertOutputContains("render with {{ ux_icon('dashboard') }}")
        ;

        $this->assertFileExists($expectedFile);
    }

    public function testCanImportIconAndRename(): void
    {
        $this->assertFileDoesNotExist($expectedFile = self::ICON_DIR.'/renamed.svg');

        $this->executeConsoleCommand('ux:icons:import uiw:dashboard@renamed')
            ->assertSuccessful()
            ->assertOutputContains('Importing uiw:dashboard as renamed')
            ->assertOutputContains("render with {{ ux_icon('renamed') }}")
        ;

        $this->assertFileExists($expectedFile);
    }

    public function testImportNonExistentIcon(): void
    {
        $this->executeConsoleCommand('ux:icons:import something:invalid')
            ->assertSuccessful()
            ->assertOutputContains('[ERROR] The icon "something:invalid" does not exist on iconify.design.')
        ;

        $this->assertFileDoesNotExist(self::ICON_DIR.'/invalid.svg');
    }
}
