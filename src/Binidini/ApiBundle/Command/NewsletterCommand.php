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
        $emails = ['rus', 'sonya', 'a', 'serg', 'agolovin'];
        $filename = $input->getArgument('filename');
        $file = fopen( $filename, "r" ) or die("Couldn't open $filename");
        $i = 0;
        while (!feof($file)) {
            $n = ($i % 5);
            $link = 'http://tytymyty.ru/public/unsubscribe?id='. rand(1000000, 9999999);
            $line = rtrim(fgets($file));
            if (empty($line)) break;
            list($company, $email) = explode(";", $line);

            try {
                $semail = $emails[$n].'@tytymyty.ru';

                $message = new \Swift_Message();
                $headers = $message->getHeaders();
                $headers->addTextHeader('Precedence', 'bulk');
                $headers->addTextHeader('List-Unsubscribe', '<' . $link . '>');

                $message->setContentType("text/html");
                $message->setTo(explode(",", $email));
                $message->setSubject("Народная доставка Титимити - " . $company . ".");
                $message->setFrom($semail, "Титимити.ру");
                $message->setBody(
                    $this->getContainer()->get('templating')->render(
                        'BinidiniWebBundle::Template/Email/newsletter.html.twig',
                        ['link' => $link, 'company' => $company]
                    )
                );


                #$mailer = $this->getContainer()->get('mailer');
                #$mailer->send($message);
                #$this->setContainer(null);
                #$output->writeln($email . " - ok");
                #$mailer->getTransport()->stop();

                // Create the Transport
                $transport = \Swift_SmtpTransport::newInstance('smtp.yandex.ru', 465, 'ssl')
                    ->setUsername($semail)
                    ->setPassword('rusden77')
                ;


                $mailer = \Swift_Mailer::newInstance($transport);


// Send the message
                $result = $mailer->send($message);
                $output->writeln($email . " - " . $semail . " - " . $result);

                sleep(rand(1,60));

            } catch (\Exception $ex) {
                $output->writeln('<error>'.$ex->getMessage().'</error>');
                if (strpos($ex->getMessage(), '554')) exit;
            }


        }
        fclose($file);

    }
}