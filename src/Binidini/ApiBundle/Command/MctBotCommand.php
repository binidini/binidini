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


class MctBotCommand  extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bd:mtc:order:create')
            ->setDescription('Creates a mtc fake order')
            ->addOption(
                'random-mode',
                'r',
                InputOption::VALUE_REQUIRED,
                'Frequency of bot running. Needed for crontab.',
                1
            )
            ->setHelp(
                <<<EOT
                    The <info>%command.name%</info> command creates a mct fake order.

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
        $token = $this->getContainer()->getParameter('mtc_token');
        $names = [
            'Цветы',
            'Цветы',
            'Цветы',
            'Цветы',
            'Цветы Москва',
            'Розы',
            'Букет',
            'Документы',
        ];
        $pickupAddresses = [
            'ул. Арбат, д.11, Москва',
            'Ленинградский пр-т, д. 62, Москва',
            'ул. Садовая-Кудринская, д. 3А, Москва',
            'ул. Спартаковская, д.17, Москва',
            'ул. 1-я Тверская-Ямская, д. 29, с. 3, Москва',
            'ул. Братиславская, д. 16, стр. 2, Москва',
            'пр. Мира, д. 176, Москва',
            'Сигнальный проезд, вл. 9Г, Москва',
            'Кронштадтский бульвар, д.7, Москва',
            'Ленинградское шоссе, д. 15, Москва',
            'Ореховый бульвар, д. 14Г, Москва',
            'ул. Привольная, д. 65/32, Москва',
            'Пролетарский пр-т, д. 33, к.1, Москва',
            'Лубянский пр-д, д.15, стр. 4, Москва',
            'Краснопрудная ул., д.1, Москва',
            'Хоромный туп., д. 2/6, Москва',
            'Рублевское шоссе, д.48/1, Москва',
            'Кутузовский пр-т, д. 36А, Москва',
            'Лермонтовский пр., д. 6, Москва',
            'ул. Красина, д. 9, стр. 2, Москва',
            'ул. Митинская, д. 36, корп. 2, Москва',
            'ул. Ярцевская, д. 34, корп.1, Москва',
            'Свободный проспект, д. 33, Москва',
            'Носовихинское шоссе, д.7, Москва',
            'ул. Пятницкая д.16, стр. 8, Москва',
            'ул. Новослободская, д. 14/19, стр.8, Москва',
            'улица Гарибальди, д. 30А, Москва',
            'ул. Маршала Бирюзова, д. 20, к. 2, стр. 1, Москва',
            'ул. Хачатуряна, д. 17г, Москва',
            'ул. Декабристов, д. 17, Москва',
            'ул. Первомайская, д. 74, Москва',
            'ул. Планерная, д. 12, корп. 1, Москва',
            'Гжельский пер., д. 19, Москва',
            'Проспект Маршала Жукова д.59 с.1, Москва',
            'пр-т Вернадского, д. 39, Москва',
            'ул. Красного Маяка, д. 2, Москва',
            'Преображенская площадь, д.6, Москва',
            'Нахимовский пр-т, д. 50, Москва',
            'Козицкий пер., д. 1А, Москва',
            '1-я Новокузьминская ул., д. 23, стр. 1, Москва',
            'ул. Сущевский вал, д. 5, стр. 22, Москва',
            'ул. Снежная, дом 26, Москва',
            'Семеновская пл., д. 7, корп. 17А, Москва',
            'Б. Серпуховская ул., д. 30, стр. 3, Москва',
            'Ленинградский пр-т, д. 75, к.1, Москва',
            'Сокольническая пл., д.4, к.1, Москва',
            'ул. 10-летия Октября, д.11, Москва',
            'Химкинский бульвар, д.16, к.1, Москва',
            'Н. Радищевская ул., д.5, стр.1, Москва',
            'Новоясеневский пр-т, вл. 2А, Москва',
            'Климентовский пер., д. 12, стр. 6, Москва',
            'Большая Тульская ул., д. 46, Москва',
            'Ломоносовский пр-т, д. 23, Москва',
            'Цветной бульвар, д.19, стр.5, Москва',
            'Окружной проезд, вл. 2А, стр. 1, Москва',
            'шоссе Энтузиастов, д. 31, стр. 39, Москва',
            'ул. Константина Федина, д.11, Москва',
            'ул. Академика Бочвара, д. 2, Москва',
            'ул. Большая Семеновская, вл. 15А, стр. 1, Москва',
            'Варшавское шоссе, д.132, Москва',
            'Рублево-Успенское шоссе, дом 123 Б, Москва',
            'р-н Павшинская Пойма, Подмосковный бул., д. 2, Москва',
            'Ярославское ш., 300 м от МКАД в обл., Москва',
            'Вокзальная ул., д. 2, ТЦ "Андромеда", Москва',
            'ул. Ленинградская, д. 16Б, Москва'
        ];
        $deliveryAddresses = [
            'Москва, Пресненская набережная, 2',
            'Москва, улица Дмитрия Ульянова, 51',
            'Москва, Таганская площадь, 12/4с5',
            'Москва, Мичуринский проспект, 36',
            'Москва, Новослободская улица, 16',
            'Москва, Старокачаловская улица, вл5с4',
            'Москва, улица Маршала Катукова, 23',
            'Москва, Профсоюзная улица, 64/66',
            'Москва, Тверская улица, 24/2С1',
            'Москва, Пятницкое шоссе, 3 (ТЦ "Пятница")',
            'Москва, проспект Мира, 211 (ТЦ "Золотой Вавилон")',
            'Москва, улица Большая Полянка, 56с1',
            'Москва, Рублевское шоссе, 52А (ТЦ "Западный")',
            'Москва, Олимпийский проспект, 14',
            'Москва, Садовая-Самотечная улица, 20',
            'Москва, Большая Грузинская улица, 4-6с1',
            'Москва, роспект Мира, 26с1 (Ботанический сад МГУ)',
            'Москва, площадь Киевского Вокзала, 2 (ТЦ "Европейский")',
            'Москва, МКАД, 66-й километр, 1 (ТРЦ Вегас)',
            'Москва, улица Удальцова, 1А',
            'Москва, улица Алабяна, 7',
            'Москва, улица Покровка, 50/2',
            'Москва, Днепропетровская улица, 2 (ТРК "Глобал Сити" 2-й этаж)',
            'Москва, Зеленый проспект, 81 (к/т Киргизия)',
            'Москва, Пушкинская площадь, 2',
            'Москва, Алтуфьевское шоссе, 16',
            'Москва, Люблинская улица, 96',
            'Москва, Пятницкое шоссе, 39 (ТЦ "Мандарин")',
            'Москва, Лодочная улица, 4',
            'Москва, улица Бутлерова, 22',
            'Москва, проспект Вернадского, 105к3',
            'Москва, улица Новый Арбат, 21',
            'Москва, улица Большая Полянка, 1/3',
            'Москва, улица Петровские Линии, 2/18',
            'Москва, 1-я Тверская-Ямская улица, 7',
            'Москва, улица Симоновский Вал, 14Б',
            'Москва, Ленинградское шоссе, 16Ас4 (ТЦ "Метрополис")',
            'Москва, Первомайская улица, 106А',
            'Москва, Флотская улица, 3'

        ];
        $minutes = ['00', '15', '30', '45'];
        $prices = [150, 200, 200, 250, 250, 300, 350, 400];
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
