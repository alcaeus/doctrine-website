<?php

declare(strict_types=1);

namespace Doctrine\Website;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\Console\Command as DBALCommand;
use Doctrine\DBAL\Tools\Console\ConnectionProvider\SingleConnectionProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Command as ORMCommand;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\Website\Commands\BuildAllCommand;
use Doctrine\Website\Commands\BuildDocsCommand;
use Doctrine\Website\Commands\BuildWebsiteCommand;
use Doctrine\Website\Commands\BuildWebsiteDataCommand;
use Doctrine\Website\Commands\ClearBuildCacheCommand;
use Doctrine\Website\Commands\EventParticipantsCommand;
use Doctrine\Website\Commands\SyncRepositoriesCommand;
use Stripe;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use function assert;
use function date_default_timezone_set;
use function file_exists;
use function getenv;
use function is_string;
use function realpath;
use function sprintf;

class Application
{
    public const ENV_PROD    = 'prod';
    public const ENV_STAGING = 'staging';

    public function __construct(
        private BaseApplication $application,
        EntityManager $em,
        Connection $connection,
        BuildAllCommand $buildAllCommand,
        BuildDocsCommand $buildDocsCommand,
        BuildWebsiteCommand $buildWebsiteCommand,
        BuildWebsiteDataCommand $buildWebsiteDataCommand,
        ClearBuildCacheCommand $clearBuildCacheCommand,
        SyncRepositoriesCommand $syncRepositoriesCommand,
        EventParticipantsCommand $eventParticipantsCommand,
    ) {
        $this->application->add($buildAllCommand);
        $this->application->add($buildDocsCommand);
        $this->application->add($buildWebsiteCommand);
        $this->application->add($buildWebsiteDataCommand);
        $this->application->add($clearBuildCacheCommand);
        $this->application->add($syncRepositoriesCommand);
        $this->application->add($eventParticipantsCommand);

        $this->application->setHelperSet(new HelperSet([
            'question'      => new QuestionHelper(),
            //'db'            => new ConnectionHelper($connection),
            'em'            => new EntityManagerHelper($em),
            //'configuration' => new ConfigurationHelper($connection, $migrationsConfiguration),
        ]));

        $connectionProvider = new SingleConnectionProvider($connection);

        $this->application->addCommands([
            // DBAL Commands
            new DBALCommand\ReservedWordsCommand($connectionProvider),
            new DBALCommand\RunSqlCommand($connectionProvider),

            // ORM Commands
            new ORMCommand\ClearCache\CollectionRegionCommand(),
            new ORMCommand\ClearCache\EntityRegionCommand(),
            new ORMCommand\ClearCache\MetadataCommand(),
            new ORMCommand\ClearCache\QueryCommand(),
            new ORMCommand\ClearCache\QueryRegionCommand(),
            new ORMCommand\ClearCache\ResultCommand(),
            new ORMCommand\SchemaTool\CreateCommand(),
            new ORMCommand\SchemaTool\UpdateCommand(),
            new ORMCommand\SchemaTool\DropCommand(),
            new ORMCommand\EnsureProductionSettingsCommand(),
            new ORMCommand\GenerateProxiesCommand(),
            new ORMCommand\RunDqlCommand(),
            new ORMCommand\ValidateSchemaCommand(),
            new ORMCommand\InfoCommand(),
            new ORMCommand\MappingDescribeCommand(),
        ]);
    }

    public function run(InputInterface $input): int
    {
        $inputOption = new InputOption(
            'env',
            'e',
            InputOption::VALUE_REQUIRED,
            'The environment.',
            'dev',
        );
        $this->application->getDefinition()->addOption($inputOption);

        return $this->application->run($input);
    }

    public function getConsoleApplication(): BaseApplication
    {
        return $this->application;
    }

    public static function getContainer(string $env): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('doctrine.website.env', $env);
        $container->setParameter('doctrine.website.debug', $env !== self::ENV_PROD);
        $container->setParameter('doctrine.website.root_dir', realpath(__DIR__ . '/..'));
        $container->setParameter('doctrine.website.config_dir', realpath(__DIR__ . '/../config'));
        $container->setParameter('doctrine.website.cache_dir', realpath(__DIR__ . '/../cache'));
        $container->setParameter('doctrine.website.github.http_token', getenv('doctrine_website_github_http_token'));
        $container->setParameter('doctrine.website.mysql.password', getenv('doctrine_website_mysql_password'));
        $container->setParameter('doctrine.website.algolia.admin_api_key', getenv('doctrine_website_algolia_admin_api_key') ?: '1234');
        $container->setParameter('doctrine.website.stripe.secret_key', getenv('doctrine_website_stripe_secret_key') ?: '');
        $container->setParameter('doctrine.website.send_grid.api_key', getenv('doctrine_website_send_grid_api_key') ?: '');

        $xmlConfigLoader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
        $xmlConfigLoader->load('services.xml');

        $yamlConfigLoader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
        $yamlConfigLoader->load('routes.yml');

        $yamlConfigLoader->load(sprintf('config_%s.yml', $env));

        $configDir = $container->getParameter('doctrine.website.config_dir');
        assert(is_string($configDir));
        if (file_exists($configDir . '/local.yml')) {
            $yamlConfigLoader->load('local.yml');
        }

        $dataLoader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../data'));
        $dataLoader->load('events.yml');
        $dataLoader->load('partners.yml');
        $dataLoader->load('projects.yml');
        $dataLoader->load('sponsors.yml');
        $dataLoader->load('team_members.yml');

        $container->compile();

        $apiKey = $container->getParameter('doctrine.website.stripe.secret_key');
        assert(is_string($apiKey));
        Stripe\Stripe::setApiKey($apiKey);

        date_default_timezone_set('America/New_York');

        return $container;
    }
}
