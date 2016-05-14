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

class NewsletterCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bd:newsletter')
            ->setDescription('Tytymyty newsletter')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'File with email list'
            )
            ->setHelp(
                <<<EOT
                    The <info>%command.name%</info> command send newsletter to all recipient from file.

<info>php %command.full_name% filename</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $file = fopen( $filename, "r" ) or die("Couldn't open $filename");
        while (!feof($file)) {
            $link = 'http://tytymyty.ru/public/unsubscribe?id='. rand(1000000, 9999999);
            $line = rtrim(fgets($file));
            if (empty($line)) break;
            list($company, $email) = explode(";", $line);

            try {
                $message = new \Swift_Message();
                $headers = $message->getHeaders();
                $headers->addTextHeader('Precedence', 'bulk');
                $headers->addTextHeader('List-Unsubscribe', '<' . $link . '>');

                $message->setContentType("text/html");
                $message->setTo($email, $company);
                $message->setSubject("Народная доставка Титимити - " . $company . ".");
                $message->setFrom("info@tytymyty.ru", "Титимити.ру");
                $message->setBody(
                    $this->getContainer()->get('templating')->render(
                        'BinidiniWebBundle::Template/Email/newsletter.html.twig',
                        ['link' => $link, 'company' => $company]
                    )
                );

                $mailer = $this->getContainer()->get('mailer');
                $response = $mailer->send($message);
                $output->writeln($email . " - ok");
                sleep(18);
            } catch (\Exception $ex) {
                $output->writeln('<error>'.$ex->getMessage().'</error>');
            }


        }
        fclose($file);

    }
}