<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Model\Document;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

final class DocumentStorage
{
    private readonly Filesystem $filesystem;
    
    public function __construct(
        #[Autowire('%kernel.project_dir%/assets/documents')]
        private readonly string $storageDirectory,
    ) {
        $this->filesystem = new Filesystem();
        
        if (!$this->filesystem->exists($this->storageDirectory)) {
            $this->filesystem->mkdir($this->storageDirectory);
        }
    }
    
    public function readFile(string $path): string
    {
        if (!$this->hasFile($path)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist.', $path));
        }
        
        return $this->filesystem->readFile($this->getAbsolutePath($path));
    }
    
    public function hasFile(string $path): bool
    {
        return $this->filesystem->exists($this->getAbsolutePath($path));
    }
    
    public function getFile(string $path): Document
    {
        if (!$this->hasFile($path)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist.', $path));
        }
        
        return new Document($this->getAbsolutePath($path));
    }
    
    private function getAbsolutePath(string $path): string
    {
        try {
            $absolutePath = Path::makeAbsolute($path, $this->storageDirectory);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException(sprintf('The file "%s" is not valid.', $path), 0, $e);
        }
        
        return $absolutePath;
    }
    
}
