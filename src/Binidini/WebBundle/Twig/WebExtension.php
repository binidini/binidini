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

class WebExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
            new \Twig_SimpleFilter('weight', array($this, 'weightFilter')),
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


    public function getName()
    {
        return 'web_extension';
    }
}