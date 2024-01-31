<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\Icons\Exception\IconNotFoundException;
use Symfony\UX\Icons\Registry\IconifyIconRegistry;
use Symfony\UX\Icons\Registry\LocalSvgIconRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
#[AsCommand(
    name: 'ux:icons:import',
    description: 'Import icon(s) or icon pack(s) from iconify.design',
)]
final class ImportIconCommand extends Command
{
    public function __construct(
        private LocalSvgIconRegistry $localRegistry,
        private IconifyIconRegistry $iconifyRegistry,
        private ?HttpClientInterface $http = null,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'names',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Icon or pack name from iconify.design (suffix with "@<name>" to rename locally)',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $names = $input->getArgument('names');

        foreach ($names as $name) {
            if (preg_match('#^(([\w-]+):([\w-]+))(@([\w-]+))?$#', $name, $matches)) {
                $this->importIcon($io, $matches[2], $matches[3], $matches[5] ?? $matches[3]);

                continue;
            }

            if (preg_match('#^([\w-]+)(@([\w-]+))?$#', $name, $matches)) {
                $this->importPack($io, $matches[1], $matches[3] ?? $matches[1]);

                continue;
            }

            $io->error(sprintf('Invalid icon name "%s".', $name));
        }

        return Command::SUCCESS;
    }

    private function importIcon(SymfonyStyle $io, string $prefix, string $name, string $localName): void
    {
        $io->comment(sprintf('Importing <info>%s:%s</info> as <info>%s</info>...', $prefix, $name, $localName));

        try {
            $svg = $this->fetchSvg($prefix, $name);
        } catch (IconNotFoundException $e) {
            $io->error($e->getMessage());

            return;
        }

        $this->localRegistry->add($localName, $svg);

        $io->text(sprintf("<info>Imported Icon</info>, render with <comment>{{ ux_icon('%s') }}</comment>.", $localName));
        $io->newLine();
    }

    private function importPack(SymfonyStyle $io, string $name, string $localName): void
    {
        $io->comment(sprintf('Downloading set <info>%s</info>...', $name));

        try {
            $data = $this->http()
                ->request('GET', sprintf('https://raw.githubusercontent.com/iconify/icon-sets/master/json/%s.json', $name))
                ->toArray()
            ;
        } catch (ClientExceptionInterface) {
            $io->error(sprintf('The icon pack "%s" does not exist on iconify.design.', $name));

            return;
        }

        $this->iconifyRegistry->add($localName, $data);

        $io->comment(sprintf('Imported set <info>%s</info> as <info>%s</info>...', $name, $localName));
    }

    private function http(): HttpClientInterface
    {
        return $this->http ?? throw new \LogicException('The "symfony/http-client" package is required to import icons. Try running "composer require symfony/http-client".');
    }

    /**
     * @return resource|string
     */
    private function fetchSvg(string $prefix, string $name)
    {
        $content = $this->http()
            ->request('GET', sprintf('https://api.iconify.design/%s/%s.svg', $prefix, $name))
            ->getContent()
        ;

        if (!str_starts_with($content, '<svg')) {
            throw new IconNotFoundException(sprintf('The icon "%s:%s" does not exist on iconify.design.', $prefix, $name));
        }

        return $content;
    }
}
