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
use Symfony\UX\Icons\Exception\IconPackNotFoundException;
use Symfony\UX\Icons\IconPack;
use Symfony\UX\Icons\IconRegistryInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
#[AsCommand(
    name: 'ux:icons:list',
    description: 'List available icon packs',
)]
final class ListIconsCommand extends Command
{
    public function __construct(private IconRegistryInterface $registry)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('pack', InputArgument::OPTIONAL, 'The icon pack to show details for', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $packName = $input->getArgument('pack');

        try {
            $pack = $this->registry->pack($packName);
        } catch (IconPackNotFoundException $e) {
            $io->error($e->getMessage());
        }

        if (isset($pack)) {
            $io->section(sprintf('Details for Icon Pack "%s"', $pack->prefix ?: '(root)'));
            $io->definitionList(
                ['Prefix' => $pack->prefix ?: '(none)'],
                ['# Icons' => $pack->count()],
                ...array_map(
                    static fn (string $key, string $value) => [$key => $value],
                    array_keys($pack->metadata),
                    $pack->metadata
                )
            );
        }

        $io->section('Available Icon Packs');
        $io->table(
            ['Name', '# Icons'],
            array_map(static fn (IconPack $pack) => [$pack->prefix ?: '(root)', $pack->count()], $this->registry->packs())
        );

        return Command::SUCCESS;
    }
}
