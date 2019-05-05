<?php declare(strict_types=1);

namespace APPointer\Command;

use APPointer\RemoteEntity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

class LoginCommand extends Command
{
    /** @var EntityManagerInterface $em */
    private $em;

    /** @var string $dbName */
    private $dbName;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager('remote');
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('login')
            ->setDescription('Log start/end of work')
            ->addOption('list', 'l', INPUTOPTION::VALUE_NONE, 'See logs')
            ->addOption('test', null, INPUTOPTION::VALUE_NONE, 'Show tables')
            ->addOption('login', 'i', INPUTOPTION::VALUE_NONE, 'Log session start')
            ->addOption('wait', null, INPUTOPTION::VALUE_NONE, 'Insert a sleep before continuing, quick and dirty fix for login script')
            ->addOption('logout', 'o', INPUTOPTION::VALUE_NONE, 'Log session end')
            ->addOption('stats', 's', INPUTOPTION::VALUE_NONE, 'Show statistics of MySQL-Server')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->output = $output;
        $this->input  = $input;

        $commands = ['login', 'logout', 'stats', 'list', 'test'];

        foreach ($commands as $command) {
            if ($input->getOption($command)) {
                $this->$command();
            }
        }
    }

    private function login(): void
    {
        if ($this->input->getOption('wait')) {
            sleep(10);
        }
        $this->log('Login');
    }

    private function logout(): void
    {
        $this->log('Logout');
    }

    public function log(string $event): void
    {
        $log = new Log($event . ' ' . trim(`hostname`));

        $this->em->persist($log);
        $this->em->flush($log);
    }

    private function test()
    {
        # $this->output->writeln('Checking ORM');
        $sql = 'show tables';
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $this->output->writeln(print_r($stmt->fetchAll()), true);
    }

    public function getDBName(): string
    {
        if (!$this->dbName) {
            if (!preg_match('@^mysql://([^:]*):@', getenv('DATABASE_URL'), $matches)) {
                throw new \Exception('DB name is supposed to equal sql username, but not found in DATABASE_URL');
            }

            $this->dbName = $matches[1];
        }

        return $this->dbName;
    }

    public function stats(): void
    {
        $dbName = $this->getDBName();

        $sql="SELECT table_schema '{$dbName}', 
            sum( data_length + index_length ) / 1024 / 1024 'Data Base Size in MB', 
            sum( data_free )/ 1024 / 1024 'Free Space in MB' 
            FROM information_schema.TABLES 
            GROUP BY table_schema;";

        $result = $this->executeSql($sql);

        $table = new Table($this->output);
        $table->setHeaders(['DB', 'Used [MB]', 'Free [MB]'])
            ->setRows($result);
        $table->render();

        $this->output->writeln('');
        $this->output->writeln("Also see https://www.freemysqlhosting.net/account/");
    }

    public function executeSql($sql): array
    {
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function list()
    {
        $sql = 'SELECT * FROM log ORDER BY id DESC LIMIT 60';
        $result = $this->executeSql($sql);

        $table = new Table($this->output);
        $table->setHeaders(['id', 'time', 'event'])
            ->setRows($result);
        $table->render();

        $this->output->writeln('');
    }
}
