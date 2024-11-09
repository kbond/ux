<?php

namespace App\Model;

use App\Model\Recipe\File;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @phpstan-type Manifest array{
 *     title: string,
 *     description: string,
 *     credit?: string|array<array-key,string>,
 *     dependencies?: array{
 *          js?: string[],
 *          php?: string[],
 *          recipes?: string[],
 *     },
 *     files?: string|string[],
 *     demo_files?: string|string[],
 *     tags?: string[],
 * }
 */
final readonly class Recipe
{
    public string $title;
    public string $description;

    /** @var array<array-key,string> */
    public array $references;

    /** @var array{js: string[], php: string[], recipes: string[]} */
    public array $dependencies;

    /** @var File[] */
    public array $files;

    /** @var File[] */
    public array $demoFiles;

    /** @var string[] */
    public array $tags;

    /**
     * @param Manifest $manifest
     */
    public function __construct(public string $name, public string $demo, array $manifest, string $projectDir)
    {
        $this->title = $manifest['title'] ?? throw new \LogicException(sprintf('Missing title for recipe "%s"', $name));
        $this->description = $manifest['description'] ?? throw new \LogicException(sprintf('Missing description for recipe "%s"', $name));
        $this->references = (array) ($manifest['references'] ?? []);
        $this->dependencies = [
            'js' => $manifest['dependencies']['js'] ?? [],
            'php' => $manifest['dependencies']['php'] ?? [],
            'recipes' => $manifest['dependencies']['recipes'] ?? [],
        ];
        $this->files = array_map(
            static fn (string $path) => new File(
                $path,
                file_get_contents(sprintf('%s/%s', $projectDir, $path)) ?: throw new \RuntimeException(sprintf('Unable to read file "%s"', $path)),
            ),
            (array) ($manifest['files'] ?? [])
        );
        $this->demoFiles = array_map(
            static fn (string $path) => new File(
                $path,
                file_get_contents(sprintf('%s/%s', $projectDir, $path)) ?: throw new \RuntimeException(sprintf('Unable to read file "%s"', $path)),
            ),
            (array) ($manifest['demo_files'] ?? [])
        );
        $this->tags = (array) ($manifest['tags'] ?? []);
    }

    public function template(): string
    {
        return sprintf('@Recipes/%s.twig', $this->name);
    }
}
