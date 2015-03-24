<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\CoreBundle\EventListener;

use FOS\UserBundle\EventListener\AuthenticationListener as FOSAuthenticationListener;

class AuthenticationListener extends FOSAuthenticationListener
{

    public static function getSubscribedEvents()
    {
        return array(
        );
    }

}