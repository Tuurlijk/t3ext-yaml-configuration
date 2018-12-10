<?php
declare(strict_types = 1);

namespace MichielRoos\YamlConfiguration\Command;

/**
 * This file is part of the "yaml_configuration" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExportTableCommand extends AbstractTableCommand
{
    /**
     * A comma separated list of column names of a table to skip per default while exporting
     */
    protected const EXPORT_SKIP_COLUMNS_DEFAULT = 'crdate,cruser_id,lastlogin,tstamp,uc';

    /**
     * Columns to skip in export
     *
     * @var array
     */
    protected $skipColumns = [];

    /**
     * Columns to use in export
     *
     * Set this property to ignore $this->skipColumns and use explicitly this columns for export
     *
     * @var array
     */
    protected $useOnlyColumns = [];

    /**
     * @var bool
     */
    protected $includeDeleted = false;

    /**
     * @var bool
     */
    protected $includeHidden = false;

    /**
     * @var int
     */
    protected $indentLevel = 2;

    /**
     * @var bool
     */
    protected $forceOverride = false;

    /**
     * @var bool
     */
    protected $backendUserMatchGroupByTitle = false;

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
        $this->setSkipColumns(GeneralUtility::trimExplode(',', $input->getOption('skip-columns'), true));
        if ($input->getOption('use-only-columns')) {
            $this->setUseOnlyColumns(GeneralUtility::trimExplode(',', $input->getOption('use-only-columns'), true));
        }
        $this->includeDeleted = (bool)$input->getOption('include-deleted');
        $this->includeHidden = (bool)$input->getOption('include-hidden');
        $this->forceOverride = (bool)$input->getOption('force-override');
        $this->backendUserMatchGroupByTitle = (bool)$input->getOption('backend-user-match-group-by-title');
        $optionIndentLevel = $input->getOption('indent-level');
        if ($optionIndentLevel !== null) {
            if ((int)$optionIndentLevel < 0) {
                throw new RuntimeException(
                    'The provided indention level for the generated yaml file (--indent-level option) must be a positive integer value',
                    1543822486
                );
            }
            $this->indentLevel = (int)$optionIndentLevel;
        }
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Exports a database table into a YAML file')
            ->setAliases(['yaml_configuration:yaml:export', 'yaml_configuration:export:table'])
            ->addArgument(
                'table',
                InputArgument::REQUIRED,
                'The name of the table which you want to export'
            )
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Path to the resulting yml file outside of the web root',
                null
            )
            ->addOption(
                'skip-columns',
                'skip',
                InputOption::VALUE_OPTIONAL,
                'A comma separated list of column names to skip',
                self::EXPORT_SKIP_COLUMNS_DEFAULT
            )
            ->addOption(
                'use-only-columns',
                'only',
                InputOption::VALUE_OPTIONAL,
                'A comma separated list of column names to use in the export. Overrides --skip-columns option',
                null
            )
            ->addOption(
                'include-deleted',
                'deleted',
                null,
                'Export deleted records'
            )
            ->addOption(
                'include-hidden',
                'hidden',
                null,
                'Export hidden/disabled records'
            )
            ->addOption(
                'indent-level',
                null,
                InputOption::VALUE_OPTIONAL,
                'Indent level to make the yaml file human readable',
                2
            )
            ->addOption(
                'force-override',
                null,
                null,
                'Force override an existing file'
            )
            ->addOption(
                'backend-user-match-group-by-title',
                null,
                null,
                'Match backend user groups by usergroup title'
            );
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
        // Output information about the command
        $this->informationalHeader($io, $input);
        $this->checkGivenFilepathBeforeExport();
        // Export the table
        $this->exportTable($io);
    }

    /**
     * Export table to yaml file
     *
     * @param SymfonyStyle $io
     */
    protected function exportTable(SymfonyStyle $io): void
    {
        $table = $this->table;
        $skipColumns = $this->skipColumns;
        $includeDeleted = $this->includeDeleted;
        $includeHidden = $this->includeHidden;
        $yaml = '';
        $io->title('Exporting ' . $table . ' configuration');

        if ($this->useOnlyColumnsModeActive()) {
            $io->note('--use-only-columns mode active. --skip-columns and its defaults will be ignored for this export.');
        }

        $result = $this->queryBuilderForTable($table);
        if ($includeDeleted || $includeHidden) {
            if ($includeDeleted) {
                $result->getRestrictions()->removeByType(DeletedRestriction::class);
            }
            if ($includeHidden) {
                $result->getRestrictions()->removeByType(HiddenRestriction::class);
            }
        }
        $result = $result
            ->select('*')
            ->from($table)
            ->execute();
        // Rows were found in the table
        if ($result->rowCount()) {
            $explodedResult = [];
            foreach ($result as $row) {
                $explodedRow = [];
                foreach ($row as $column => $value) {
                    // If --use-only-columns option is used, the column is only added if it is part of the option value.
                    // Using --use-only-columns overrides the functionality of --skip-columns completely (also for defaults)
                    if ($this->useOnlyColumnsModeActive() && !\in_array($column, $this->useOnlyColumns, true)) {
                        continue;
                    // Fallback to normal --skip-columns mode
                    } elseif (\in_array($column, $skipColumns, true)) {
                        // The column is skipped if it's name is found in $this->skipColumns.
                        continue;
                    }

                    // Pass non-string values and non-special cases ​​as they are
                    if (!($this->backendUserMatchGroupByTitle && $table === 'be_users' && $column === 'usergroup')
                        && (\is_int($value) || \is_float($value) || $value === null || \is_bool($value))) {
                        $explodedRow[$column] = $value;
                        continue;
                    }

                    // Do not update usergroups by UID when exporting to other systems
                    // UID maybe different for the same usergroup name
                    if ($this->backendUserMatchGroupByTitle && $table === 'be_users' && $column === 'usergroup' && $value) {
                        $usergroups = $this->queryBuilderForTable(self::TYPO3_BACKEND_USERGROUP_TABLE)
                            ->select('title')
                            ->from(self::TYPO3_BACKEND_USERGROUP_TABLE)
                            ->where(
                                $this->queryBuilderForTable('be_users')
                                    ->expr()->in(
                                        'uid',
                                        $this->queryBuilderForTable('be_users')->createNamedParameter(
                                            GeneralUtility::intExplode(',', $value),
                                            Connection::PARAM_INT_ARRAY
                                        )
                                    )
                            )
                            ->execute();
                        $usergroupsTitles = [];
                        foreach ($usergroups as $singleUserGroup) {
                            $usergroupsTitles[] = $singleUserGroup['title'];
                        }
                        $explodedValue = $usergroupsTitles;
                        if (!empty($usergroupsTitles) && $usergroupsTitles[0] !== '') {
                            $value = $usergroupsTitles[0];
                        } else {
                            $value = '';
                        }

                        if (count($explodedValue) > 1) {
                            $explodedRow[$column] = $explodedValue;
                        } elseif ($value !== '') {
                            $explodedRow[$column] = $value;
                        }
                        continue;
                    }
                    // The column value is treated as normal string if the string $value was not processed until now
                    $explodedRow[$column] = $value;
                }
                // Add row iteration to result
                $explodedResult[] = $explodedRow;
            }
            $dump = [
                'TYPO3' => [
                    'Data' => [
                        $table => $explodedResult
                    ],
                ],
            ];
            // Convert the resulting array into a friendly YAML
            $yaml = Yaml::dump($dump, 20, $this->indentLevel);
        }
        // Generated YAML is written to file
        if ($yaml !== '' && $result->rowCount() > 0) {
            $io->note('Export to ' . GeneralUtility::getFileAbsFileName($this->file));
            $persistYamlFile = GeneralUtility::writeFile(
                $filePath = GeneralUtility::getFileAbsFileName($this->file),
                $yaml
            );
            if ($persistYamlFile) {
                $io->listing(
                    [
                        $result->rowCount() . ' records were exported.'
                    ]
                );
                $io->success('Successfully finished the export of ' . $table . ' into ' . GeneralUtility::getFileAbsFileName($this->file));
            }
        } else {
            $io->warning('No records found in ' . $table . ' table.');
        }
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     */
    protected function informationalHeader(SymfonyStyle $io, InputInterface $input): void
    {
        $io->title($this->getName());
        $io->table(
            ['Table', 'File', 'Skipped Columns <comment>*</comment>', 'Use only Columns', 'Incl. deleted', 'Incl. hidden', 'Indent Level <comment>*</comment>'],
            [
                [
                    $this->table,
                    !empty($this->file) ? $this->file : '(<info>no path given</info>)',
                    $input->getOption('skip-columns'),
                    $input->getOption('use-only-columns') ?? 'no',
                    $input->getOption('include-deleted') ? 'yes' : 'no',
                    $input->getOption('include-hidden') ? 'yes' : 'no',
                    $input->getOption('indent-level')
                ]
            ]
        );
        $io->writeln(' <comment>*</comment> Option has default fallbacks');
    }

    /**
     * @param array $skipColumns
     */
    protected function setSkipColumns(array $skipColumns): void
    {
        $this->skipColumns = $skipColumns;
    }

    /**
     * @param array $useOnlyColumns
     */
    public function setUseOnlyColumns(array $useOnlyColumns): void
    {
        $this->useOnlyColumns = $useOnlyColumns;
    }

    /**
     * @return bool
     */
    protected function useOnlyColumnsModeActive(): bool
    {
        return count($this->useOnlyColumns) > 0;
    }

    /**
     * Check whether the given export file path
     *
     *  - is outside of web root (if applicationContext IS NOT Development)
     *  - is available
     *  - exists already
     */
    protected function checkGivenFilepathBeforeExport(): void
    {
        $filePath = GeneralUtility::getFileAbsFileName($this->file);
        $fileSystem = new Filesystem();
        $filePathDirectory = GeneralUtility::dirname($filePath);
        if (strpos($filePath, Environment::getPublicPath() . '/') !== false
            && !GeneralUtility::getApplicationContext()->isDevelopment()) {
            throw new \RuntimeException(
                'Please specify an absolute file path outside of the web root.',
                1543830137
            );
        }
        if ($fileSystem->exists($filePathDirectory) === false) {
            throw new \RuntimeException(
                'The directory of the given file path does not exist. Please create the path in advance. ' .
                '( mkdir -p ' . $filePathDirectory . ' )',
                1543831384
            );
        }
        if (!$this->forceOverride && $fileSystem->exists($filePath)) {
            throw new \RuntimeException(
                'The given file path already exists. Add option "--force-override" if you want to ' .
                'override the existing file.',
                1543832349
            );
        }
    }
}
