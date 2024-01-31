<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Registry;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\UX\Icons\Exception\IconNotFoundException;
use Symfony\UX\Icons\Exception\IconPackNotFoundException;
use Symfony\UX\Icons\IconPack;
use Symfony\UX\Icons\IconRegistryInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class IconifyIconRegistry implements IconRegistryInterface
{
    public function __construct(private string $iconDir)
    {
    }

    public function get(string $name): array
    {
        $parts = explode(':', $name, 2);

        if (2 !== \count($parts)) {
            throw new IconNotFoundException(sprintf('The icon name "%s" is invalid.', $name));
        }

        [$pack, $icon] = $parts;

        try {
            $iconResource = $this->fetchPack($pack);
        } catch (IconPackNotFoundException) {
            throw new IconNotFoundException(sprintf('The iconify pack "%s" does not exist.', $pack));
        }

        $attributes = ['viewBox' => sprintf('0 0 %d %d', $iconResource['width'], $iconResource['height'])];

        return [
            $iconResource['icons'][$icon] ?? throw new IconNotFoundException(sprintf('The icon "%s" does not exist in iconify pack "%s".', $name, $pack)),
            $attributes,
        ];
    }

    public function add(string $prefix, array $data): void
    {
        $filename = sprintf('%s/%s.php', $this->iconDir, $prefix);

        $data['icons'] = array_map(
            fn (array $icon) => $icon['body'],
            $data['icons'] ?? [],
        );

        unset($data['categories'], $data['aliases'], $data['suffixes']);

        (new Filesystem())->dumpFile($filename, sprintf('<?php return %s;', var_export($data, true)));
    }

    public function packs(): array
    {
        return array_map(
            fn (string $name) => $this->pack($name),
            $this->packNames()
        );
    }

    public function pack(string $name): IconPack
    {
        $resource = $this->fetchPack($name);

        return new IconPack(
            $name,
            $resource['info']['total'] ?? 0,
            array_filter([
                'Name' => $resource['info']['name'],
                'Version' => $resource['info']['version'] ?? null,
                'Author' => sprintf('%s <%s>', $resource['info']['author']['name'], $resource['info']['author']['url']),
                'License' => sprintf('%s <%s>', $resource['info']['license']['title'], $resource['info']['license']['url']),
            ]),
        );
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->packNames() as $prefix) {
            yield from array_map(
                fn (string $icon) => sprintf('%s:%s', $prefix, $icon),
                array_keys($this->fetchPack($prefix)['icons'])
            );
        }
    }

    public function count(): int
    {
        return iterator_count($this);
    }

    private function fetchPack(string $prefix): array
    {
        if (!file_exists($filename = sprintf('%s/%s.php', $this->iconDir, $prefix))) {
            throw new IconPackNotFoundException(sprintf('The icon pack "%s" does not exist.', $prefix));
        }

        return require $filename;
    }

    private function packNames(): array
    {
        return array_values(
            array_map(
                fn (SplFileInfo $file) => basename($file->getBasename(), '.php'),
                iterator_to_array(Finder::create()->files()->in($this->iconDir)->name('*.php')->depth(0))
            )
        );
    }
}
