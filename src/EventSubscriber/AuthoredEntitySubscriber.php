<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\AuthoredEntityInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;

/**
 * Class AuthoredEntitySubscriber
 * @package App\EventSubscriber
 */
class AuthoredEntitySubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;

    /**
     * AuthoredEntitySubscriber constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['getAuthenticatedUser', EventPriorities::PRE_WRITE],
        ];
    }

    /**
     * @param ViewEvent $event
     */
    public function getAuthenticatedUser(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        /** @var UserInterface $author */
        $author = $this->tokenStorage->getToken()->getUser();

        if (!($entity instanceof AuthoredEntityInterface) || Request::METHOD_POST !== $method) {
            return;
        }

        $entity->setAuthor($author);
    }
}
