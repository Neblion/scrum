<?php

namespace Neblion\ScrumBundle\Controller;

use FOS\UserBundle\Controller\ProfileController as BaseController;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;

class ProfileController extends BaseController
{
    /**
     * Generate the redirection url when editing is completed.
     * 
     * @param \FOS\UserBundle\Model\UserInterface $user
     * 
     * @return string
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->container->get('router')->generate('profile_username_email');
    }
    
    /**
     * @param string $action
     * @param string $value
     */
    protected function setFlash($action, $value)
    {
        $this->container->get('session')->setFlash('success', 'Username and/or email was updated with success !');
    }
}
