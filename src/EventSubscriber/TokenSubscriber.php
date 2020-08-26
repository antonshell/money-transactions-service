<?php

namespace App\EventSubscriber;

use App\Controller\AuthenticatedController;
use App\Http\Response\ValidationErrorResponse;
use App\Repository\UserRepository;
use App\Service\AuthenticationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class TokenSubscriber implements EventSubscriberInterface
{
    private const ERROR_ACCESS_DENIED = 'Invalid username or password';

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    public function __construct(
        AuthenticationService $authenticationService
    )
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof AuthenticatedController) {
            $user = $this->authenticationService->getUserFromRequest();
            $password = $this->authenticationService->getPasswordFromRequest();

            if (!$user || !password_verify($password, $user->getPassword())) {
                throw new AccessDeniedHttpException(self::ERROR_ACCESS_DENIED);
            }
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}