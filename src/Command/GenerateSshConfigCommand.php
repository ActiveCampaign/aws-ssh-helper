<?php
declare(strict_types=1);

namespace Bangpound\Assh\Command;

use Aws\Ec2\Ec2Client;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;
use function JmesPath\search;

/**
 * Class GenerateSshConfigCommand
 *
 * @package App\Command
 */
class GenerateSshConfigCommand extends Command
{
    protected static $defaultName = 'generate:ssh-config';

    /**
     * @var ContainerInterface
     */
    private $serviceLocator;

    /**
     * @var Ec2Client
     */
    private $client;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * Selects bastion instances by their name.
     *
     * @var string
     */
    private $bastionExpression = '[?Tags[?Key==`Name`].Value[] | [?contains(@, `bastion`)]]';

    /**
     * Selects the name from the collection of tags in instance metadata
     *
     * @var string
     */
    private $nameTagExpression = 'Tags[?Key==`Name`] | [0].Value';

    /**
     * Reduces the instances to a list of objects with fewer and flattened
     * properties.
     *
     * @var string
     */
    private $reductionExpression = <<<EOT
Reservations[].Instances[].{
    InstanceId: @.InstanceId,
    KeyName: @.KeyName,
    ImageId: @.ImageId,
    VpcId: @.VpcId,
    SubnetId: @.SubnetId,
    PrivateDnsName: @.PrivateDnsName,
    PrivateIpAddress: @.PrivateIpAddress,
    PublicDnsName: @.PublicDnsName,
    PublicIpAddress: @.PublicIpAddress,
    AvailabilityZone: @.Placement.AvailabilityZone,
    LaunchTime: @.LaunchTime,
    SecurityGroups: @.SecurityGroups,
    Tags: @.Tags,
    IamInstanceProfile: @.IamInstanceProfile.Arn,
    InstanceType: @.InstanceType
}
EOT;

    /**
     * GenerateSshConfigCommand constructor.
     *
     * @param ContainerInterface $serviceLocator
     */
    public function __construct(ContainerInterface $serviceLocator)
    {
        parent::__construct();
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @suppress PhanTypeMismatchArgument
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Create a SSH config file based on AWS instances')
            ->addOption('profile', null, InputOption::VALUE_REQUIRED, 'Profile')
            ->addOption('region', null, InputOption::VALUE_REQUIRED, 'Region')
            ->addOption('prefix', null, InputOption::VALUE_REQUIRED, 'Add a prefix to all names derived from tags', '')
            ->addOption('ssh', null, InputOption::VALUE_NONE, 'Generate plain SSH configuration')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->client = $this->serviceLocator->get(Ec2Client::class);
        $this->twig = $this->serviceLocator->get(Environment::class);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws \Twig_Error
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $prefix = $input->getOption('prefix');
        $profile = $input->getOption('profile');
        $region = $input->getOption('region');
        $templateName = $input->getOption('ssh') ? 'ssh-config.txt.twig' : 'assh.yaml.txt.twig';

        // VPC names are important for labelling instances in the output.
        $vpcs = $this->client->describeVpcs()->search(sprintf(
            'Vpcs[].{ VpcId: VpcId, Name: %s }',
            $this->nameTagExpression
        ));

        // Reduce the object properties to ones that matter for us.
        $instances = $this->client->getPaginator('DescribeInstances', [
            'Filters' => [
                ['Name' => 'instance-state-name', 'Values' => ['running']],
            ],
        ])->search($this->reductionExpression);

        $bastions = search($this->bastionExpression, $instances);

        $ret = $this->twig->render($templateName, [
            'ec2' => $instances,
            'bastions' => $bastions,
            'region' => $region,
            'profile' => $profile,
            'vpcs' => $vpcs,
            'prefix' => $prefix,
        ]);

        $io->writeln($ret);

        return 0;
    }
}
