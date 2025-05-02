<?php

declare(strict_types=1);

namespace App\CommissionTask\Commands;

use App\CommissionTask\Models\OperationsCollection;
use App\CommissionTask\Services\CommissionsCalculator;
use App\CommissionTask\Services\DataMapper\CsvFileMapper;
use App\CommissionTask\Services\DataMapper\OperationsCSVDataMapper;
use App\CommissionTask\Services\ExchangeRatesClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:calculate-commissions')]
class CalculateCommissionsFromFile extends Command
{
    private CommissionsCalculator $calculator;

    public function __construct(?string $name = null, ?ExchangeRatesClient $exchangeRatesClient = null)
    {
        parent::__construct($name);

        $this->calculator = new CommissionsCalculator($exchangeRatesClient ?? new ExchangeRatesClient());
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Calculate commisions using given file.')
            ->addArgument('file', InputArgument::OPTIONAL, 'File path', './data/input.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('file');

        if (!is_string($filePath)) {
            $output->writeln('<error>Nieprawidłowy typ argumentu "file".</error>');

            return Command::FAILURE;
        }

        if (!file_exists($filePath)) {
            $output->writeln("<error>Plik nie istnieje: $filePath </error>");

            return Command::FAILURE;
        }

        $operations = $this->parseFile($filePath);
        $commissions = $this->calculator->calculateCommissions($operations);

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
