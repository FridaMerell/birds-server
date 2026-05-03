<?php

namespace App\Command;

use App\Repository\Birdnet\DetectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:prune-detections', description: 'Delete detections older than 24 hours')]
class PruneDetectionsCommand extends Command
{
    public function __construct(
        private readonly DetectionRepository $repo,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $deleted = $this->repo->deleteOlderThan24Hours();
        $output->writeln("Pruned $deleted detection(s).");
        return Command::SUCCESS;
    }
}