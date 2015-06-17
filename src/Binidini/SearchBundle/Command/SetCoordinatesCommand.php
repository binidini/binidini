<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Binidini\SearchBundle\Command;

use Binidini\SearchBundle\Document\Coordinates;
use Binidini\SearchBundle\Document\Shipment;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetCoordinatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bd:shipment:coordinates:update')
            ->setDescription('Finds shipment addresses without coordinates and updates them')
            ->setHelp(
                <<<EOT
                    The <info>%command.name%</info> command finds shipment addresses without coordinates and updates them.

<info>php %command.full_name% </info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $totalCounter = 0;
        $updatedCounter = 0;

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $geocode = $this->getContainer()->get('binidini.geocode.yandex.client');

        $shipments =$dm->getRepository('BinidiniSearchBundle:Shipment')->findBy(['pickup_coordinates'=>null]);
        $totalCounter+=count($shipments);

        /** @var Shipment $shipment  */
        foreach ($shipments as $shipment) {
            $output->write($shipment->getPickupAddress());

            $params = array(
                'geocode' => $shipment->getPickupAddress(),
                'format'  => 'json',
                'results' => 1,
            );
            $res = $geocode->get('?'.http_build_query($params, '', '&'))->send();

            if ($res->getStatusCode() == 200) {
                $response = json_decode($res->getBody(true));
                if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {
                    list($longitude, $latitude) = explode(' ', $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
                    $shipment->setPickupCoordinates(new Coordinates($longitude, $latitude));
                    $dm->persist($shipment);
                    $dm->flush();

                    $updatedCounter++;
                    $output->writeln(sprintf(': <info>%s</info>, <info>%s</info>', $longitude, $latitude));
                }
            }
        }

        $shipments =$dm->getRepository('BinidiniSearchBundle:Shipment')->findBy(['delivery_coordinates'=>null]);
        $totalCounter+=count($shipments);

        /** @var Shipment $shipment  */
        foreach ($shipments as $shipment) {
            $output->write($shipment->getPickupAddress());

            $params = array(
                'geocode' => $shipment->getPickupAddress(),
                'format'  => 'json',
                'results' => 1,
            );
            $res = $geocode->get('?'.http_build_query($params, '', '&'))->send();

            if ($res->getStatusCode() == 200) {
                $response = json_decode($res->getBody(true));
                if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {
                    list($longitude, $latitude) = explode(' ', $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
                    $shipment->setDeliveryCoordinates(new Coordinates($longitude, $latitude));
                    $dm->persist($shipment);
                    $dm->flush();

                    $updatedCounter++;
                    $output->writeln(sprintf(': <info>%s</info>, <info>%s</info>', $longitude, $latitude));
                }
            }
        }

        $output->writeln(sprintf('Адреса без координат: всего <info>%s</info>, обновлено <info>%s</info>.', $totalCounter, $updatedCounter));
    }
}