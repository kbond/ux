<?php

namespace App\Model\Recipe;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final readonly class File implements \JsonSerializable
{
    public function __construct(public string $path, public string $source)
    {
    }

    public function name(): string
    {
        return basename($this->path);
    }

    public function extension(): string
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'path' => $this->path,
            'source' => $this->source,
        ];
    }
}
