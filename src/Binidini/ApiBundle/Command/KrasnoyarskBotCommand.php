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


class KrasnoyarskBotCommand  extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bd:krasnoyarsk:order:create')
            ->setDescription('Creates a krasnoyarsk fake order')
            ->addOption(
                'random-mode',
                'r',
                InputOption::VALUE_REQUIRED,
                'Frequency of bot running. Needed for crontab.',
                1
            )
            ->setHelp(
                <<<EOT
                    The <info>%command.name%</info> command creates a krasnoyarsk fake order.

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
        $token = $this->getContainer()->getParameter('krasnoyarsk_token');
        $names = [
            'Цветы',
            'Цветы',
            'Цветы',
            'Цветы',
            'Цветы',
            'Цветы',
            'Розы',
            'Букет',
            'Документы',
            'Пицца',
            'Суши'
        ];
        $pickupAddresses = [
            'Красноярск, ул. Молокова, 1a',
            'Красноярск, ул. Ленина, 49',
            'Красноярск, ул.Копылова, 76'
        ];
        $deliveryAddresses = [
            'Свердловская ул., 140, к. 5, Красноярск',
            'ул. Карла Маркса, 155А, Красноярск',
            'ул. Молокова, 56, Красноярск',
            'Центральный пр-д, 7Г, Красноярск',
            'Взлетная ул., 12, Красноярск',
            'ул. Красной Армии, 16А, Красноярск',
            'ул. Бограда, 21, Красноярск',
            'пр. Мира, 102, Красноярск',
            'ул. Александра Матросова, 2, Красноярск'
        ];
        $minutes = ['00', '15', '30', '45'];
        $prices = [100, 150, 150, 200, 200, 250];
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
                //'guarantee'=>rand(0,5)?0:$price,
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
