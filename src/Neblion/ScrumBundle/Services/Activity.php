<?php

namespace Neblion\ScrumBundle\Services;

use Doctrine\ORM\EntityManager;
use Neblion\ScrumBundle\Entity\Activity as ActivityEntity;

class Activity
{
    private $em;
    private $activity;
    
    public function __construct(EntityManager $entityManager)
    {
        $this->em       = $entityManager;
        $this->activity = new ActivityEntity();
    }
    
    public function add(\Neblion\ScrumBundle\Entity\Project $project,
                        \Neblion\ScrumBundle\Entity\Account $account,
                        $text, $link_url, $link_text) {
        $this->activity->setProject($project);
        $this->activity->setAccount($account);
        $this->activity->setText($text);
        $this->activity->setLinkUrl($link_url);
        $this->activity->setLinkText($link_text);
        
        $this->em->persist($this->activity);
    }
}
