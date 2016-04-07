<?php

namespace Binidini\CoreBundle\Controller;

use Apple\ApnPush\Notification\Message;
use Apple\ApnPush\Queue\Adapter\ArrayAdapter;
use Apple\ApnPush\Queue\Queue;
use Binidini\CoreBundle\Entity\Bid;
use Binidini\CoreBundle\Entity\BidRepository;
use Binidini\CoreBundle\Entity\Payment;
use Binidini\CoreBundle\Entity\Shipping;
use Binidini\CoreBundle\Entity\ShippingRepository;
use Binidini\CoreBundle\Exception\IncorrectDeliveryCode;
use Binidini\CoreBundle\Exception\TransitionCannotBeApplied;
use Binidini\CoreBundle\Form\Type\BidType;
use Binidini\CoreBundle\Form\Type\MessageType;
use Binidini\CoreBundle\Form\Type\ReviewType;
use FOS\UserBundle\Doctrine\UserManager;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Pagerfanta\Pagerfanta;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\DateTime;
use Apple\ApnPush\Certificate\Certificate;
use Apple\ApnPush\Notification;
use Apple\ApnPush\Notification\Connection;

class ShippingController extends ResourceController
{
    protected $stateMachineGraph = Shipping::GRAPH;

    public function checkDeliveryCodeAction(Request $request)
    {
        /** @var $shipping Shipping */
        $shipping = $this->findOr404($request);

        if ($shipping->getCarrier() == $this->getUser() && $shipping->getDeliveryCode() > 0) {

            $shipping->setDeliveryCode($shipping->getDeliveryCode() + 1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($shipping);
            $em->flush();

            $deliveryCode = (int)$request->get('delivery_code');
            if ($deliveryCode != (10000 + $shipping->getId() * $this->container->getParameter('delivery_code_prime') % 10000)) {
                throw new IncorrectDeliveryCode('Код подтверждения доставки некорректен.');
            }

        }

        return $this->updateStateAction($request, 'deliver');
    }

    public function showAction(Request $request)
    {
        $request->attributes->remove('slug');

        if ($this->config->isApiRequest()) {
            return parent::showAction($request);
        } else {
            /** @var $shipping Shipping */
            $shipping = $this->findOr404($request);

            // мы закрываем для отображения старые заказы
            if (new \DateTime() > $shipping->getDeliveryDatetime() &&
                (is_null($this->getUser()) || ($this->getUser() != $shipping->getSender() && $this->getUser() != $shipping->getCarrier()) && !$this->getUser()->isAdmin()) &&
                !$this->isBot()
            ) {
                $this->flashHelper->setFlash('danger', 'show.error');
                return $this->redirectHandler->redirectToRoute('binidini_search_shipment_index');
            }

            $view = $this
                ->view()
                ->setTemplate($this->config->getTemplate('show.html'))
                ->setTemplateVar($this->config->getResourceName())
                ->setData(
                    [
                        'shipping' => $shipping,
                        'bid_form' => $this->createForm(new BidType())->createView(),
                        'message_form' => $this->createForm(new MessageType())->createView(),
                        'review_form' => $this->createForm(new ReviewType())->createView(),
                    ]
                );
            return $this->handleView($view);
        }
    }

    public function historyIndexAction(Request $request)
    {
        /** @var $shipping Shipping */
        $shipping = $this->findOr404($request);
        /** @var $repository LogEntryRepository */
        $repository = $this->getDoctrine()->getManager()->getRepository('Gedmo\Loggable\Entity\LogEntry');
        $entries = $repository->getLogEntries($shipping);
        $usersInHistory = [];
        /** @var $userManager UserManager */
        $userManager = $this->get('fos_user.user_manager');
        foreach ($entries as $key => $entry) {
            if (!isset($usersInHistory[$entry->getUsername()])) {
                $usersInHistory[$entry->getUsername()] = $userManager->findUserBy(['id' => $entry->getUsername()]);;
            }
        }
        $data = [
            'entries' => $entries,
        ];
        if (!$this->config->isApiRequest()) {
            $data['history_users'] = $usersInHistory;
        }
        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('history_index.html'))
            ->setData(
                $data
            );
        return $this->handleView($view);

    }

    public function resolveAction(Request $request)
    {
        /** @var Shipping $shipping */
        $shipping = $this->findOr404($request);
        $graph = $this->stateMachineGraph;
        $stateMachine = $this->get('sm.factory')->get($shipping, $graph);
        $transition = 'resolve';
        if (!$stateMachine->can($transition)) {
            throw new NotFoundHttpException(
                sprintf(
                    'The requested transition %s cannot be applied on the given %s with graph %s.',
                    $transition,
                    $this->config->getResourceName(),
                    $this->stateMachineGraph
                )
            );
        }
        $stateMachine->apply($transition);

        $payment = $request->get('payment');
        $insurance = $request->get('insurance');

        if ($payment == 'sender') {
            $shipping->releaseSender();
        } elseif ($payment == 'carrier') {

            $guarantee = new Payment();
            $guarantee
                ->setUser($shipping->getCarrier())
                ->setAmount($shipping->getDeliveryPrice())
                ->setRef($shipping->getId())
                ->setFlagCreditDebit(1)
                ->setType(Payment::TYPE_GUARANTEE)
                ->setMethod(Payment::METHOD_INTERNAL_PAYMENT)
                ->setState(Payment::STATE_COMPLETED)
                ->setBalance($shipping->getCarrier()->getBalance() + $shipping->getCarrier()->getHoldAmount() + $shipping->getDeliveryPrice())
                ->setDetails('Начисление гарантии по заказу №' . $shipping->getId());

            $guarantee2 = new Payment();
            $guarantee2
                ->setUser($shipping->getSender())
                ->setAmount($shipping->getDeliveryPrice())
                ->setRef($shipping->getId())
                ->setFlagCreditDebit(-1)
                ->setType(Payment::TYPE_GUARANTEE)
                ->setMethod(Payment::METHOD_INTERNAL_PAYMENT)
                ->setState(Payment::STATE_COMPLETED)
                ->setBalance($shipping->getSender()->getBalance() + $shipping->getSender()->getHoldAmount() - $shipping->getDeliveryPrice())
                ->setDetails('Списание гарантии по заказу №' . $shipping->getId());

            $shipping->payPayment();

            $em = $this->getDoctrine()->getManager();
            $em->persist($guarantee);
            $em->persist($guarantee2);
            $em->flush();
        }

        if ($insurance == 'carrier') {
            $shipping->releaseCarrier();
        } elseif ($insurance == 'sender') {
            $insurance = new Payment();
            $insurance
                ->setUser($shipping->getCarrier())
                ->setAmount($shipping->getInsurance())
                ->setRef($shipping->getId())
                ->setFlagCreditDebit(-1)
                ->setType(Payment::TYPE_INSURANCE)
                ->setMethod(Payment::METHOD_INTERNAL_PAYMENT)
                ->setState(Payment::STATE_COMPLETED)
                ->setBalance($shipping->getCarrier()->getBalance() + $shipping->getCarrier()->getHoldAmount() - $shipping->getInsurance())
                ->setDetails('Списание страховки по заказу №' . $shipping->getId())
                ->setPaymentAt($insurance->getPaymentAt()->modify("+1 second"));

            $insurance2 = new Payment();
            $insurance2
                ->setUser($shipping->getSender())
                ->setAmount($shipping->getInsurance())
                ->setRef($shipping->getId())
                ->setFlagCreditDebit(1)
                ->setType(Payment::TYPE_INSURANCE)
                ->setMethod(Payment::METHOD_INTERNAL_PAYMENT)
                ->setState(Payment::STATE_COMPLETED)
                ->setBalance($shipping->getSender()->getBalance() + $shipping->getSender()->getHoldAmount() + $shipping->getInsurance())
                ->setDetails('Начисление страховки по заказу №' . $shipping->getId())
                ->setPaymentAt($insurance2->getPaymentAt()->modify("+1 second"));

            $shipping->payInsurance();

            $em = $this->getDoctrine()->getManager();
            $em->persist($insurance);
            $em->persist($insurance2);
            $em->flush();

        }
        $this->domainManager->update($shipping);

        return $this->redirectHandler->redirectToReferer();
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function createLikeAction(Request $request)
    {
        $this->isGrantedOr403('createLike');

        /** @var Shipping $shipping */
        $shipping = $this->findOr404($request);

        $now = new \DateTime();

        if ($shipping->getDeliveryDatetime() < $now) {
            $deliveryDatetime = new \DateTime();
            $deliveryDatetime->modify('+3 hours');
            $deliveryDatetime->setTimestamp(floor($deliveryDatetime->getTimestamp() / 3600) * 3600);

            $shipping->setDeliveryDatetime($deliveryDatetime);
        }

        $form = $this->getForm($shipping);

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('create.html'))
            ->setData(array(
                $this->config->getResourceName() => $shipping,
                'form' => $form->createView(),
            ));

        return $this->handleView($view);
    }

    private function isBot(&$botname = '')
    {
        /* Эта функция будет проверять, является ли посетитель роботом поисковой системы */
        $bots = array(
            'rambler', 'googlebot', 'aport', 'yahoo', 'msnbot', 'turtle', 'mail.ru', 'omsktele',
            'yetibot', 'picsearch', 'sape.bot', 'sape_context', 'gigabot', 'snapbot', 'alexa.com',
            'megadownload.net', 'askpeter.info', 'igde.ru', 'ask.com', 'qwartabot', 'yanga.co.uk',
            'scoutjet', 'similarpages', 'oozbot', 'shrinktheweb.com', 'aboutusbot', 'followsite.com',
            'dataparksearch', 'google-sitemaps', 'appEngine-google', 'feedfetcher-google',
            'liveinternet.ru', 'xml-sitemaps.com', 'agama', 'metadatalabs.com', 'h1.hrn.ru',
            'googlealert.com', 'seo-rus.com', 'yaDirectBot', 'yandeG', 'yandex',
            'yandexSomething', 'Copyscape.com', 'AdsBot-Google', 'domaintools.com',
            'Nigma.ru', 'bing.com', 'dotnetdotcom'
        );
        foreach ($bots as $bot)
            if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false) {
                $botname = $bot;
                return true;
            }
        return false;
    }

    #region API 2

    public function listAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse("Forbidden", 403);
        }
        $type = $request->get('type');
        $status = $request->get('status');
        if ($status == 'active') {
            $states = ['new', 'dispatched', 'on_way', 'accepted', 'delivered', 'paid', 'rejected', 'refused'];
        } elseif ($status == 'completed') {
            $states = ['completed', 'canceled'];
        } elseif ($status == 'conflict') {
            $states = ['conflict'];
        } else {
            return new JsonResponse("Bad request", 400);
        }
        /**
         * @var ShippingRepository $repository
         */
        $repository = $this->getRepository();
        /**
         * @var $shippingResult Pagerfanta
         */
        if ($type == 'sender') {
            $shippingResult = $repository->findBySenderIdAndStates($user->getId(), $states);
        } else if ($type == 'carrier') {
            $shippingResult = $repository->findByCarrierIdAndStates($user->getId(), $states);
        } else {
            return new JsonResponse("Bad request", 400);
        }
        $shippingResult->setMaxPerPage(500);
        $shippingResult->setCurrentPage($request->get('page', 1));
        $result = [];
        foreach ($shippingResult->getIterator() as $iterate) {
            /**
             * @var $iterate Shipping
             */
            $result[] = array(
                'id' => $iterate->getId(),
                'name' => $iterate->getName(),
                'delivery_price' => $iterate->getDeliveryPrice(),
                'guarantee' => $iterate->getGuarantee(),
                'insurance' => $iterate->getInsurance(),
                'pickup_address' => $iterate->getPickupAddress(),
                'delivery_address' => $iterate->getDeliveryAddress(),
                'payment_guarantee' => $iterate->getPaymentGuarantee(),
                //'user_id'=>$iterate->getUser()->getId(),
                //'carrier_id'=>$iterate->getCarrier()->getId(),
                //'user' => $iterate->getUser(),
                //'carrier' => $iterate->getCarrier(),
                'state' => $iterate->getState(),
                'delivery_datetime' => $iterate->getDeliveryDatetime()->format(\DateTime::ISO8601),
            );
        }
        return new JsonResponse($result);
    }

    public function oneShippingAction(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            return new JsonResponse("Bad request", 400);
        }
        $user = $this->getUser();
        /**
         * @var ShippingRepository $repository
         */
        $repository = $this->getRepository();
        /**
         * @var Shipping $shippingResult
         */
        $shippingResult = $repository->find($id);
        if (!$shippingResult) {
            return new JsonResponse("Not found", 404);
        }

        if ($user) {
            if ($shippingResult->getUser()->getId() == $user->getId()) {
                $result = $shippingResult->getResultWrapper(true, $user, true, true);
                $result['is_mine_shipping'] = 1;
                if ($shippingResult->getDeliveryCode()) {
                    $dcprime = $this->container->getParameter('delivery_code_prime');
                    $result['code'] = 10000 + $shippingResult->getId() * $dcprime % 10000;
                }
                if ($shippingResult->getCarrier()) {
                    $em = $this->getDoctrine()->getManager();
                    $repository = $em->getRepository(get_class(new Bid()));
                    /**
                     * @var Bid[] $bids
                     */
                    $bids = $repository->findBy(["shipping" => $shippingResult->getId()]);
                    foreach ($bids as $bid) {
                        if ($bid->isAgreed()) {
                            $result['carrier_price'] = $bid->getPrice();
                            break;
                        }
                    }
                }

            } else {
                if ($shippingResult->getCarrier()) {
                    if ($shippingResult->getCarrier()->getId() == $user->getId()) {
                        $result = $shippingResult->getResultWrapper(true, $user, true, true);
                        if ($shippingResult->getDeliveryCode()) {
                            $dcprime = $this->container->getParameter('delivery_code_prime');
                            $result['code'] = 10000 + $shippingResult->getId() * $dcprime % 10000;
                        }
                        $result['is_mine_shipping'] = 0;
                        if ($shippingResult->getCarrier()) {
                            $em = $this->getDoctrine()->getManager();
                            $repository = $em->getRepository(get_class(new Bid()));
                            /**
                             * @var Bid[] $bids
                             */
                            $bids = $repository->findBy(["shipping" => $shippingResult->getId()]);

                            foreach ($bids as $bid) {
                                if ($bid->isAgreed()) {
                                    $result['carrier_price'] = $bid->getPrice();
                                    break;
                                }
                            }
                        }
                    } else {
                        $result = $shippingResult->getResultWrapper(true, $user);
                        $result['is_mine_shipping'] = 0;
                    }
                } else {
                    $result = $shippingResult->getResultWrapper(true, $user);
                    $result['is_mine_shipping'] = 0;
                }
            }
        } else {
            $result =  $shippingResult->getResultWrapper(true);
        }
        return new JsonResponse($result);
    }

    #endregion
}