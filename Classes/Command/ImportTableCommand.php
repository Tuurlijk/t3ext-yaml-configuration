<?php
declare(strict_types = 1);

namespace MichielRoos\YamlConfiguration\Command;

/**
 * This file is part of the "yaml_configuration" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImportTableCommand extends AbstractTableCommand
{

    /**
     * Fields used to match configurations to database records
     *
     * @var array
     */
    protected $matchFields = [];

    protected function configure(): void
    {
        $this
            ->setDescription('Imports data into tables from YAML configuration')
            ->setAliases(['yaml_configuration:yaml:import', 'yaml_configuration:import:table'])
            ->addArgument(
                'table',
                InputArgument::REQUIRED,
                'The name of the table which you want to import'
            )
            ->addArgument(
                'file',
                InputArgument::OPTIONAL,
                'Path to the yml file you wish to import. If none is given, all yml files in directories named \'Configuration/YamlConfiguration\' will be parsed',
                null
            )
            ->addOption(
                'matchFields',
                null,
                InputOption::VALUE_REQUIRED,
                'A comma separated list of fields used to match configurations to database records.',
                'uid'
            );
    }

    /**
     * Initialize variables used in the rest of the command methods
     *
     * This method is executed before the interact() and the execute() method.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->matchFields = GeneralUtility::trimExplode(',', $input->getOption('matchFields'), true);
    }

    /**
     * The command main method
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->informationalHeader($io, $input);

        $this->importData($io);
    }

    /**
     * Process the YAML file and run import DB queries
     *
     * @param SymfonyStyle $io
     */
    protected function importData(SymfonyStyle $io): void
    {
        $table = $this->table;
        $matchFields = $this->matchFields;
        $columnNames = $this->getColumnNames();
        $this->doMatchFieldsExists($matchFields, $columnNames, $io);
        $queryBuilder = $this->queryBuilderForTable($table);
        $countUpdates = 0;
        $countInserts = 0;
        if ($this->file === null) {
            // When no YAML file is given: all yaml files in active packages will be take into account
            $configurationFiles = $this->findYamlFiles();
        } else {
            $configurationFiles = [$this->file];
        }

        $io->title('Importing ' . $table . ' configuration');

        foreach ($configurationFiles as $configurationFile) {
            $configuration = $this->parseConfigurationFile($configurationFile);
            $io->note('Parsing: ' . str_replace(Environment::getPublicPath() . '/', '', $configurationFile));
            $records = $this->getDataConfiguration($configuration, $table);
            $io->writeln('Found ' . count($records) . ' records in the parsed file.');
            $countUpdates = 0;
            $countInserts = 0;
            foreach ($records as $record) {
                $record = $this->flattenYamlFields($record);
                $row = false;
                $whereClause = false;
                $queryResult = false;
                $matchClauseParts = [];
                foreach ($matchFields as $matchField) {
                    if (isset($record[$matchField])) {
                        // @TODO: Use named parameters based on the column configuration in the DB
                        $matchClauseParts[] = [
                            $matchField,
                            $record[$matchField]];
                    }
                }
                if (!empty($matchClauseParts)) {
                    $queryBuilderWithoutRestrictions = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
                    $queryBuilderWithoutRestrictions->getRestrictions()->removeAll();
                    $row = $queryBuilderWithoutRestrictions
                        ->select('*')
                        ->from($table);
                    $whereClause = [];
                    foreach ($matchClauseParts as $matchClausePart) {
                        $whereClause[] = $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->eq(
                                $matchClausePart[0],
                                // @TODO: Use named parameters based on the column configuration in the DB
                                $matchClausePart[1]
                            )
                        );
                    }
                    $row = $row->where(...$whereClause)->execute()->fetch();
                }
                if ($row) {
                    // Update row as the matched row exists in the table
                    // @TODO: re-implement beUserMatchGroupByTitle()
                    $record = $this->updateTimeFields($record, $columnNames, ['tstamp']);
                    $updateRecord = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table)
                        ->update(
                            $table,
                            $record,
                            $this->convertArrayToKeyValuePairArray($matchClauseParts)
                        );
                    if ($updateRecord) {
                        $countUpdates++;
                    }
                } else {
                    // Insert new row as no matched row exists in the table
                    $record = $this->updateTimeFields($record, $columnNames, ['crdate', 'tstamp'], true);
                    $insertRecord = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table)
                        ->insert(
                            $table,
                            $record
                        );
                    if ($insertRecord) {
                        $countInserts++;
                    }
                }
            }
            $io->newLine();
            $io->listing(
                [
                    $countUpdates . ' records were updated. ',
                    $countInserts . ' records where newly inserted.'
                ]
            );
        }

        $io->success('Successfully finished the import of ' . \count($configurationFiles) . ' configuration file(s).');
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @return void
     */
    protected function informationalHeader(SymfonyStyle $io, InputInterface $input): void
    {
        $io->title($this->getName());
        $io->table(
            ['Table Name', 'Matching Fields', 'File Path'],
            [
                [
                    $input->getArgument('table'),
                    $input->getOption('matchFields'),
                    $input->getArgument('file') ?? '<info>no path given</info>'
                ]
            ]
        );
    }

}
