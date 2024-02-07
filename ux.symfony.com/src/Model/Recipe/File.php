<?php

namespace App\Model\Recipe;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class File
{
    public function __construct(public readonly string $path, public readonly string $source)
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
}
