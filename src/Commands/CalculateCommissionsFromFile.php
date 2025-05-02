<?php

declare(strict_types=1);

namespace App\CommissionTask\Commands;

use App\CommissionTask\Models\OperationsCollection;
use App\CommissionTask\Services\CommissionsCalculator;
use App\CommissionTask\Services\DataMapper\CsvFileMapper;
use App\CommissionTask\Services\DataMapper\OperationsCSVDataMapper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:calculate-commissions')]
class CalculateCommissionsFromFile extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Oblicza prowizje na podstawie danych z pliku.')
            ->addArgument('file', InputArgument::OPTIONAL, 'Ścieżka do pliku z danymi operacji', './data/input.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('file');

        if (!is_string($filePath)) {
            $output->writeln('<error>Nieprawidłowy typ argumentu "file".</error>');

            return Command::FAILURE;
        }

        if (!file_exists($filePath)) {
            $output->writeln('<error>Plik nie istnieje: ' . $filePath . '</error>');

            return Command::FAILURE;
        }

        $operations = $this->parseFile($filePath);
        $calculator = new CommissionsCalculator($operations);
        $commissions = $calculator->calculateCommissions();

        foreach ($commissions as $commission) {
            $output->writeln($commission->getFormattedAmount());
        }

        return Command::SUCCESS;
    }

    private function parseFile(string $filePath): OperationsCollection
    {
        $mapper = new CsvFileMapper(
            new OperationsCSVDataMapper(),
            new OperationsCollection(),
            $filePath
        );

        return $mapper->load();
    }
}
