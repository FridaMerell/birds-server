<?php

namespace App\Command;

use App\Entity\Birdnet\Device;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:device:create', description: 'Register a new BirdNet device')]
class CreateDeviceCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Human-readable device name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io   = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $key  = bin2hex(random_bytes(32)); // 64-char hex key

        $device = new Device(
        );
        $device->setName($name);
        $device->setInstalledAt(new \DateTime());
        $device->setApiKey($key);
        $device->setActive(true);
        

        $this->em->persist($device);
        $this->em->flush();

        $io->success("Device \"$name\" created.");
        $io->table(['Field', 'Value'], [
            ['ID',            $device->getId()],
            ['Name',          $device->getName()],
            ['Installed at',  $device->getInstalledAt()->format('Y-m-d')],
            ['API key',       $key],
        ]);

        $io->note('Store this API key now — it is not recoverable from the database.');

        return Command::SUCCESS;
    }
}