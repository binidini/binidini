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

use Binidini\CoreBundle\Form\Type\BidType;
use Binidini\CoreBundle\Form\Type\MessageType;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Shipping controller
 *
 * @author Denis Manilo <denis@binidini.com>
 */
class ShippingController extends ResourceController
{

    public function showAction(Request $request)
    {
        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('show.html'))
            ->setTemplateVar($this->config->getResourceName())
            ->setData(
                [
                    'shipping' => $this->findOr404($request),
                    'bid_form' => $this->createForm(new BidType())->createView(),
                    'message_form' => $this->createForm(new MessageType())->createView()
                ]
            );
        return $this->handleView($view);

    }


}