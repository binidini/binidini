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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class SamaraBotCommand  extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bd:samara:order:create')
            ->setDescription('Creates a samara fake order')
            ->addOption(
                'random-mode',
                'r',
                InputOption::VALUE_REQUIRED,
                'Frequency of bot running. Needed for crontab.',
                1
            )
            ->setHelp(
                <<<EOT
                    The <info>%command.name%</info> command creates a samara fake order.

<info>php %command.full_name%</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Этот if нужен для запуска в кроне в разное время
        if (rand(1, $input->getOption('random-mode')) != 1) {
            $output->writeln('Холостой запуск');
            return;
        }

        $url = $this->getContainer()->getParameter('dff_url');
        $token = $this->getContainer()->getParameter('samara_token');
        $names = [
            'Цветы',
            'Цветы',
            'Цветы',
            'Цветы',
            'Цветы',
            'Розы',
            'Букет',
            'Документы',
        ];
        $pickupAddresses = [
            'Самара, ул. Революционная 90',
            'Самара, ул. Молодогвардейская, 182',
            'пр. Масленникова, 49, Самара',
            'Волжский пр., 36, Самара'
        ];
        $deliveryAddresses = [
            'ул. Ново-Садовая, 184А, Самара',
            'ул. Сергея Лазо, 21, Самара',
            'ул. Осипенко, 3, Самара',
            'ул. Самарская, 51, Самара',
            'Московское ш., 4, стр. 15, Самара',
            'ул. Максима Горького, 82, Самара',
            'ул. Куйбышева, 72, Самара',
            'Галактионовская ул., 43, Самара',
            ' ул. Садовая, 251, Самара',
            'ул. Лесная, 23к5, Самара',
            'ул. Кольцевая, Самара',
            'ул. Дачная, 2, Самара',
            'ул. Ново-Садовая, 4, Самара'
        ];
        $minutes = ['00', '15', '30', '45'];
        $prices = [150, 200, 200, 250];
        $insurance = [0, 0, 0, 0, 0, 0, 0, 0, 1000];

        $description = [
            '', '', '', '', '',
            'Оплата наличными при доставке',
            'Предоплата наличными',
            'Оплата: на сотовый телефон после доставки',
            'Оплата - перевод на любую карту',
            'Оплата: перевод на карту, WebMoney, Yandex Деньги, наличные в офисе',
            'Оплата на QIWI кошелек после доставки',
            'Оплата на сотовый',
            'Наличные, Webmoney, Yandex, QIWI, на сотовый',
            'Оплата на карту сбербанка или на сотовый',
            'Оплата на карту ВТБ24, мобильный',
            'Оплата ка карту Альфа-банка, или на мобильный'
        ];

        $now= new \DateTime();
        $deliveryDatetime = $now->add(new \DateInterval('PT'.rand(2,10).'H'));
        $price = $prices[array_rand($prices)];
        $post = [
            'name'=>$names[array_rand($names)],
            'pickupAddress'=>$pickupAddresses[array_rand($pickupAddresses)],
            'deliveryAddress'=>$deliveryAddresses[array_rand($deliveryAddresses)],
            'deliveryDatetime'=>$deliveryDatetime->format('d.m.y H').':'.$minutes[array_rand($minutes)],
            'deliveryPrice'=>$price,
            'insurance'=>$insurance[array_rand($insurance)],
            'guarantee'=>rand(0,5)?null:$price,
            'description' => $description[array_rand($description)],
            'access_token'=>$token,
            'category'=>-1];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        $response = curl_exec($ch);

        $output->writeln($response);
    }
}
