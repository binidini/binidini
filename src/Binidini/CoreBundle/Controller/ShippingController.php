<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Binidini\CoreBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;

/**
 * Shipping controller
 *
 *  @author Denis Manilo <denis@binidini.com>
 */
class ShippingController extends ResourceController
{
    public function changeStateAction(Request $request, $id)
    {
        $shipping = $this->findOr404(array('id' => $id));

        $this->persistAndFlush($shipping);

        return $this->redirect($this->generateUrl('binidini_core_shipping_show', ['id' => $id]));
    }

}