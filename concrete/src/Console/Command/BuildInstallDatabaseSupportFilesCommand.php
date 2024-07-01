<?php
namespace Concrete\Core\Console\Command;

use Concrete\Core\Console\Command;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Database\Connection\ConnectionFactory;
use Concrete\Core\Database\DatabaseStructureManager;
use Concrete\Core\Database\Schema\Schema;
use Doctrine\DBAL\Schema\Comparator as SchemaComparator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildInstallDatabaseSupportFilesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('c5:build:db-install')
            ->setDescription('Build support files for rapid database installation.')
            ->addEnvOption();
    }

    private function getDbXmlQueries(Connection $db): array
    {
        $xmlFile = DIR_BASE_CORE . '/config/db.xml';
        $parser = Schema::getSchemaParser(simplexml_load_file($xmlFile));
        $parser->setIgnoreExistingTables(false);
        $toSchema = $parser->parse($db);

        $comparator = new SchemaComparator();
        $schemaDiff = $comparator->compare(new \Doctrine\DBAL\Schema\Schema(), $toSchema);
        $saveQueries = $schemaDiff->toSaveSql($db->getDatabasePlatform());
        return $saveQueries;
    }

    private function getEntityQueries(Connection $db): array
    {
        $config = Setup::createConfiguration(true);
        \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('subpackages');
        \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('package');
        // Use default AnnotationReader
        $driverImpl = $config->newDefaultAnnotationDriver(DIR_BASE_CORE . '/' . DIRNAME_CLASSES . '/' . DIRNAME_ENTITIES, false);
        $config->setMetadataDriverImpl($driverImpl);
        $em = EntityManager::create($db, $config);
        $databaseStructureManager = new DatabaseStructureManager($em);
        $schemaTool = new SchemaTool($em);
        $metadatas = $databaseStructureManager->getMetadatas();
        $toSchema = $schemaTool->getSchemaFromMetadata($metadatas);
        $comparator = new SchemaComparator();
        $schemaDiff = $comparator->compare(new \Doctrine\DBAL\Schema\Schema(), $toSchema);
        $saveQueries = $schemaDiff->toSaveSql($db->getDatabasePlatform());
        return $saveQueries;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $db = app(ConnectionFactory::class)->createConnection([
            'driver' => 'concrete_pdo_mysql',
            'server' => '127.0.0.1',
            'database' => 'concrete',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ]);

        $file = DIR_BASE_CORE
            . DIRECTORY_SEPARATOR
            . DIRNAME_CONFIG
            . DIRECTORY_SEPARATOR
            . 'install'
            . DIRECTORY_SEPARATOR
            . 'database'
            . DIRECTORY_SEPARATOR
            . 'install.sql';

        $output->writeln(t('Gathering db.xml queries'));
        $queries = $this->getDbXmlQueries($db);

        $output->writeln(t('Gathering ORM entity queries'));
        $queries = array_merge($queries, $this->getEntityQueries($db));

        $output->writeln(t('Writing all queries to %s', $file));
        file_put_contents($file, implode(';', $queries));

        return static::SUCCESS;
    }
}
