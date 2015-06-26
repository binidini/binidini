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
            'Букет цветов',
            'Букет роз',
            'Желтые розы',
            'Радужные розы',
            'Красные розы',
            'Нежно - розовые розы',
            'Ярко - розовые розы',
            'Бело - вишневая роза',
            'Желто - красная роза',
            'Белые розы'
        ];
        $pickupAddresses = [
            'Санкт-Петербург, пр. Науки, д. 8, к. 1',
            'Санкт-Петербург, пр. Авиаконструкторов, д. 2',
            'Санкт-Петербург, Богатырский пр., д. 49',
            'Санкт-Петербург, Комендантский пр., д. 9, к. 2, лит. А, ТРК «Променад»',
            'Санкт-Петербург, ул. Федора Абрамова, д. 4',
            'Санкт-Петербург, пр. Энгельса, д. 60',
            'Санкт-Петербург, Ланское ш., д. 20, к.1',
            'Санкт-Петербург, Каменоостровский пр., д. 26',
            'Санкт-Петербург, пер. Крылова, д. 2',
            'Санкт-Петербург, Лиговский пр., д. 65',
            'Санкт-Петербург, ул. Маяковского, д. 22',
            'Санкт-Петербург, Гончарная, 20',
            'Санкт-Петербург, пр. Чернышевского, д. 9',
            'Санкт-Петербург, ул. Тульская, д. 2',
            'Санкт-Петербург, 1-ая линия ВО, д. 34',
            'Санкт-Петербург, Наличная ул., д. 49',
            'Санкт-Петербург, ул. Ленсовета, д. 93',
            'Санкт-Петербург, ул. Ярослава Гашека д. 4, к.1',
            'Санкт-Петербург, ул. Бухарестская, д. 78',
            'Санкт-Петербург, ул. Типанова, д. 3',
            'Санкт-Петербург, ул. Типанова, д. 5',
            'Санкт-Петербург, Пятилеток, 3',
            'Санкт-Петербург, ул. Дыбенко, д. 24, к. 1',
            'Санкт-Петербург, ул. Антонова-Овсеенко, д. 25, к. 1',
            'Санкт-Петербург, Московский, 73',
            'Выборг г. Выборг, ул. Северная, д. 6',
            'Ломоносов г. Ломоносов, ул. Владимирская, д. 25',
            'Пушкин г. Пушкин, Октябрьский бул., д. 17'
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
        $prices = [150, 200, 250, 300, 350];
        $insurance = [0, 1000, 2000];

        $now= new \DateTime();
        $deliveryDatetime = $now->add(new \DateInterval('PT'.rand(2,10).'H'));
        $post = [
                'name'=>$names[array_rand($names)],
                'pickupAddress'=>$pickupAddresses[array_rand($pickupAddresses)],
                'deliveryAddress'=>$deliveryAddresses[array_rand($deliveryAddresses)],
                'deliveryDatetime'=>$deliveryDatetime->format('d.m.y H').':'.$minutes[array_rand($minutes)],
                'deliveryPrice'=>$prices[array_rand($prices)],
                'insurance'=>$insurance[array_rand($insurance)],
                'paymentGuarantee'=>rand(0,1)?null:1,
                'access_token'=>$token];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        $response = curl_exec($ch);

        $output->writeln($response);
    }
}
