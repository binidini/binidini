<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DffBotCommand  extends ContainerAwareCommand
{
        private $token = 'NmI4NTg1MzdkMGYxMDUzYTFiYzRiMTJhM2Y0NmFjNTc5NmUwNjcwNDE0MGIwMzMzMDdmNDU5ZjE4MGVjY2YzZg';

    protected function configure()
    {
        $this
            ->setName('bd:dff:order:create')
            ->setDescription('Creates a dff fake order')
            ->setHelp(
                <<<EOT
                    The <info>%command.name%</info> command creates a dff fake order.

<info>php %command.full_name%</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('ddd');
    }
}