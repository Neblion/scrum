<?php
namespace Neblion\ScrumBundle\Listener;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $locale = $event->getRequest()->getPreferredLanguage(array('en', 'fr'));
        $event->getRequest()->setLocale($locale);
    }
}