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


class DffBotCommand  extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bd:dff:order:create')
            ->setDescription('Creates a dff fake order')
            ->addOption(
                'random-mode',
                'r',
                InputOption::VALUE_REQUIRED,
                'Frequency of bot running. Needed for crontab.',
                1
            )
            ->setHelp(
                <<<EOT
                    The <info>%command.name%</info> command creates a dff fake order.

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
        $token = $this->getContainer()->getParameter('dff_token');
        $names = [
            'Цветы',
            'Цветы',
            'Цветы',
            'Цветы',
            'Цветы',
            'Цветы СПб',
            'Розы',
            'Букет',
            'Документы',
            'Пицца',
            'Суши',
            'Мягкая игрушка'
        ];
        $pickupAddresses = [
            'Санкт-Петербург, Савушкина ул., 137',
            'Санкт-Петербург, Байконурская ул., 26',
            'Санкт-Петербург, Ланское шоссе, 20',
            'Санкт-Петербург, Непокоренных пр., 47',
            'Санкт-Петербург, Асафьева ул., 5',
            'Санкт-Петербург, Просвещения пр., 68',
            'Санкт-Петербург, Ланское ш., д. 20, к.1',
            'Санкт-Петербург, Гражданский пр., 114',
            'Санкт-Петербург, Кораблестроителей ул.,д.32, к.1',
            'Санкт-Петербург, 6-я Линия В.О. , 53',
            'Санкт-Петербург, Наставников пр., 5',
            'Санкт-Петербург, Солидарности пр., 11',
            'Санкт-Петербург, Российский пр. 8',
            'Санкт-Петербург, Новочеркасский пр., 47',
            'Санкт-Петербург, Матвеева пер., 3',
            'Санкт-Петербург, Марата ул., 76',
            'Санкт-Петербург, Стачек пр., 17',
            'Санкт-Петербург, Зенитчиков ул., 5',
            'Санкт-Петербург, Ленинский пр., 111',
            'Санкт-Петербург, Ветеранов пр., 130',
            'Санкт-Петербург, Звездная ул. , 11',
            'Санкт-Петербург, Караваевская ул., 25/1',
            'Санкт-Петербург, Седова ул., 84',
            'Санкт-Петербург, Славы пр., 55',
            'Санкт-Петербург, Дунайский пр., 31',
        ];
        $deliveryAddresses = [
            'Санкт-Петербург, Славы пр., 40',
            'Санкт-Петербург, Стремянная ул., 21/5, лит. А',
            'Санкт-Петербург, Ефимова ул., 2',
            'Санкт-Петербург, Энгельса пр., 97',
            'Санкт-Петербург, Нахимова ул., 5',
            'Санкт-Петербург, Льва Толстого ул., 9',
            'Санкт-Петербург, Бухарестская ул., 47',
            'Санкт-Петербург, Чайковского ул., 17 (отель «Индиго»)',
            'Санкт-Петербург, Индустриальный пр., 40/1',
            'Санкт-Петербург, Энгельса пр., 124 (ТРК «Вояж», 2 этаж)',
            'Санкт-Петербург, Южная дорога, 8',
            'Санкт-Петербург, Свердловская наб., 60',
            'Санкт-Петербург, Синопская наб., 78',
            'Санкт-Петербург, Есенина ул., 1',
            'Санкт-Петербург, Казанская ул., 2',
            'Санкт-Петербург, Мойки реки наб., 1 / Марсово поле, 7',
            'Санкт-Петербург, Маршала Казакова ул., 12, корп.2, литер А',
            'Санкт-Петербург, Санкт-Петербургское шоссе, 130, корп. 7',
            'Санкт-Петербург, Приморское шоссе, 418',
            'Санкт-Петербург, Малый пр. П.С., 48',
            'Санкт-Петербург, Петровская коса, 9 (территория Яхт-клуба)',
            'Санкт-Петербург, Конногвардейский бульвар, 4',
            'Санкт-Петербург, Просвещения пр., 53, к. 1',
            'Санкт-Петербург, Конногвардейский бульвар, 4',
            'Санкт-Петербург, Мытнинская наб., 6',
            'Санкт-Петербург, Юрия Гагарина пр., 14',
            'Санкт-Петербург, Малый пр. В.О., 11',
            'Санкт-Петербург, Невский пр., 60',
            'Санкт-Петербург, Приморский пр., 137, корп. 1',
            'Санкт-Петербург, Полюстровский пр., 84А (ТРЦ «Европолис»)',
            'Санкт-Петербург, Большая Зеленина ул., 18 (Корпусная ул., 9)',
            'Санкт-Петербург, Нахимова ул., 20',
            'Санкт-Петербург, Энгельса пр., 124 (ТРК «Вояж», 2 этаж)',
            'Санкт-Петербург, 6-я линия В.О., 25',
            'Санкт-Петербург, Невский пр., 22-24',
            'Санкт-Петербург, Колокольная ул., 9',
            'Санкт-Петербург, Крестовский пр., 21, литера Б',
            'Санкт-Петербург, Богатырский пр., 55',
            'Санкт-Петербург, Итальянская ул., 17',
            'Санкт-Петербург, Космонавтов пр., 65/2',
            'Санкт-Петербург, Бакунина пр., 5',
            'Санкт-Петербург, Комендантский пр., 42, корп. 1'
        ];
        $minutes = ['00', '15', '30', '45'];
        $prices = [100, 150, 150, 200, 200, 250, 250, 300, 350];
        $insurance = [0, 0, 0, 0, 0, 0, 0, 0, 1000, 2000];

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
                'guarantee'=>rand(0,5)?0:$price,
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
