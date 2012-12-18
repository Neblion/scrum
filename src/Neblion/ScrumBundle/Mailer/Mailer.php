<?php

namespace Neblion\ScrumBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;

class Mailer
{
    protected $mailer;
    protected $router;
    protected $templating;
    protected $parameters;

    public function __construct($mailer, RouterInterface $router, EngineInterface $templating, array $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    public function sendInvitationEmailMessage($email, $user, $project, $account)
    {
        $template = 'NeblionScrumBundle:Mailer:invitation.txt.twig';
        
        //$url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
        $rendered = $this->templating->render($template, array(
            'user'              => $account,
            'from'              => $user,
            'project'           => $project,
            //'confirmationUrl'   => $url,
        ));
        $this->sendEmailMessage($rendered, $this->parameters['from_email'], $email);
    }
    
    public function sendMemberRemoveNotification($member, $from, $project)
    {
        $template = 'NeblionScrumBundle:Mailer:member-remove.txt.twig';
        $rendered = $this->templating->render($template, array(
            'member'    => $member,
            'from'      => $from,
            'project'   => $project,
        ));
        $this->sendEmailMessage($rendered, $this->parameters['from_email'], $member->getAccount()->getEmail());
    }
    
    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($body);

        $this->mailer->send($message);
    }
}
