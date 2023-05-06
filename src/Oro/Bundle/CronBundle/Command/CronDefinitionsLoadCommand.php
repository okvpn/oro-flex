<?php
declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CronBundle\Loader\ScheduleTableLoader;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Updates cron commands definitions stored in the database.
 */
class CronDefinitionsLoadCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'oro:cron:definitions:load';

    public function __construct(
        protected ManagerRegistry $doctrine,
        protected ScheduleTableLoader $scheduleLoader
    ) {
        parent::__construct();
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function configure()
    {
        $this->setDescription('Updates cron commands definitions stored in the database.')
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command updates cron commands definitions stored in the database.

The previously loaded command definitions are removed from the database, and all command definitions
from <info>oro:cron</info> namespace that implement <info>\Oro\Bundle\CronBundle\Command\CronCommandInterface</info>
are saved to the database. 

  <info>php %command.full_name%</info>

HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output, [
            LogLevel::EMERGENCY => OutputInterface::VERBOSITY_QUIET,
            LogLevel::ALERT => OutputInterface::VERBOSITY_QUIET,
            LogLevel::CRITICAL => OutputInterface::VERBOSITY_QUIET,
            LogLevel::ERROR => OutputInterface::VERBOSITY_QUIET,
            LogLevel::WARNING => OutputInterface::VERBOSITY_QUIET,
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ]);

        $this->scheduleLoader->refreshTable([], $logger);

        return 0;
    }
}
