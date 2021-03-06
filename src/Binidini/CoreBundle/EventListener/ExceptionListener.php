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

use Binidini\CoreBundle\Exception\AppException;
use Binidini\CoreBundle\Exception\TransitionCannotBeApplied;
use Gedmo\Exception\UploadableInvalidMimeTypeException;
use Gedmo\Exception\UploadableMaxSizeException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ExceptionListener
{
    private $router;
    private $session;

    public function __construct(Router $router, Session $session)
    {
        $this->router = $router;
        $this->session = $session;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        //if api request just skip
        if ($event->getRequest()->getRequestFormat() !== 'html') {
            return;
        }

        $exception = $event->getException();
        $request = $event->getRequest();

        if ($exception instanceof AppException) {
            $this->session->getFlashBag()->add('error', $exception->getMessage());
            $event->setResponse(new RedirectResponse($this->getRedirectReferer($request)));
        }
        if ($exception instanceof UploadableInvalidMimeTypeException) {
            $this->session->getFlashBag()->add('error', 'Вы можете загрузить изображение в формате JPG, GIF или PNG');
            $event->setResponse(new RedirectResponse($this->getRedirectReferer($request)));
        }

        if ($exception instanceof  UploadableMaxSizeException) {
            $this->session->getFlashBag()->add('error', 'Файл с фотографией превышает допустимый размер.');
            $event->setResponse(new RedirectResponse($this->getRedirectReferer($request)));
        }

    }

    /**
     * Get redirect referer, This will detected by configuration
     * If not exists, The `referrer` from headers will be used.
     *
     * @return null|string
     */
    private function getRedirectReferer(Request $request)
    {
        $redirect = $request->get('redirect');
        $referer = $request->headers->get('referer');

        if (!is_array($redirect) || empty($redirect['referer'])) {
            return $referer;
        }

        if ($redirect['referer'] === true) {
            return $referer;
        }

        return $redirect['referer'];
    }
}