<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\WebBundle\Twig;

use Binidini\CoreBundle\Entity\Shipping;
use Symfony\Component\Validator\Constraints\DateTime;

class WebExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
            new \Twig_SimpleFilter('weight', array($this, 'weightFilter')),
            new \Twig_SimpleFilter('timeago', array($this, 'timeagoFilter')),
            new \Twig_SimpleFilter('cell', array($this, 'cellFilter')),
        );
    }

    public function priceFilter($price)
    {
        return $price . ' руб.';
    }

    public function weightFilter($weight)
    {
        return $weight . ' кг';
    }

    public function timeagoFilter(\DateTime $datetime)
    {

        $time = time() - $datetime->getTimestamp();

        $units = array(
            31536000 => 'г',
            2592000 => 'м',
            604800 => 'н',
            86400 => 'д',
            3600 => 'ч',
            60 => 'мин',
            1 => 'сек'
        );

        foreach ($units as $unit => $val) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return ($val == 'сек') ? 'только что' :
                $numberOfUnits .$val . ' назад';
        }
    }

    public function cellFilter($username)
    {
        return '8 ('. substr($username, 0, 3) . ') ' . substr($username, 3);
    }

    public function getName()
    {
        return 'web_extension';
    }
}