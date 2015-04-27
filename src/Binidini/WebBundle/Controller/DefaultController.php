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

namespace Binidini\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Binidini\CoreBundle\Entity\User;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user && $user->hasRole('ROLE_USER')) {
            return $this->redirect($this->generateUrl('binidini_search_shipment_index'));
        }

        return $this->redirect($this->generateUrl('binidini_landing_page'));
    }
}