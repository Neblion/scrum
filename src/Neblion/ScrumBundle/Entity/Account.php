<?php

namespace Neblion\ScrumBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
Use Symfony\Component\Validator\Constraints as Assert;
//use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Neblion\ScrumBundle\Entity\Account
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Neblion\ScrumBundle\Entity\AccountRepository")
 */
class Account extends BaseUser
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\OneToOne(targetEntity="Neblion\ScrumBundle\Entity\Profile", mappedBy="account")
     */
    private $profile;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Member", mappedBy="account")
     */
    private $members;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Activity", mappedBy="account")
     */
    private $activities;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Member", mappedBy="sender")
     */
    private $invitations;
    
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Retrospective", mappedBy="user")
     */
    private $retrospectives;
    
    public function __construct()
    {
        parent::__construct();
    }

    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set profile
     *
     * @param Neblion\ScrumBundle\Entity\Profile $profile
     */
    public function setProfile(\Neblion\ScrumBundle\Entity\Profile $profile)
    {
        $this->profile = $profile;
    }

    /**
     * Get profile
     *
     * @return Neblion\ScrumBundle\Entity\Profile 
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Add members
     *
     * @param Neblion\ScrumBundle\Entity\Member $members
     */
    public function addMember(\Neblion\ScrumBundle\Entity\Member $members)
    {
        $this->members[] = $members;
    }

    /**
     * Get members
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Add tasks
     *
     * @param Neblion\ScrumBundle\Entity\Task $tasks
     */
    public function addTask(\Neblion\ScrumBundle\Entity\Task $tasks)
    {
        $this->tasks[] = $tasks;
    }

    /**
     * Get tasks
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Add retrospectives
     *
     * @param Neblion\ScrumBundle\Entity\Retrospective $retrospectives
     */
    public function addRetrospective(\Neblion\ScrumBundle\Entity\Retrospective $retrospectives)
    {
        $this->retrospectives[] = $retrospectives;
    }

    /**
     * Get retrospectives
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getRetrospectives()
    {
        return $this->retrospectives;
    }

    /**
     * Remove members
     *
     * @param Neblion\ScrumBundle\Entity\Member $members
     */
    public function removeMember(\Neblion\ScrumBundle\Entity\Member $members)
    {
        $this->members->removeElement($members);
    }

    /**
     * Add invitations
     *
     * @param Neblion\ScrumBundle\Entity\Member $invitations
     * @return Account
     */
    public function addInvitation(\Neblion\ScrumBundle\Entity\Member $invitations)
    {
        $this->invitations[] = $invitations;
    
        return $this;
    }

    /**
     * Remove invitations
     *
     * @param Neblion\ScrumBundle\Entity\Member $invitations
     */
    public function removeInvitation(\Neblion\ScrumBundle\Entity\Member $invitations)
    {
        $this->invitations->removeElement($invitations);
    }

    /**
     * Get invitations
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getInvitations()
    {
        return $this->invitations;
    }

    /**
     * Remove retrospectives
     *
     * @param Neblion\ScrumBundle\Entity\Retrospective $retrospectives
     */
    public function removeRetrospective(\Neblion\ScrumBundle\Entity\Retrospective $retrospectives)
    {
        $this->retrospectives->removeElement($retrospectives);
    }

    /**
     * Add storyComments
     *
     * @param Neblion\ScrumBundle\Entity\StoryComment $storyComments
     * @return Account
     */
    public function addStoryComment(\Neblion\ScrumBundle\Entity\StoryComment $storyComments)
    {
        $this->storyComments[] = $storyComments;
    
        return $this;
    }

    /**
     * Remove storyComments
     *
     * @param Neblion\ScrumBundle\Entity\StoryComment $storyComments
     */
    public function removeStoryComment(\Neblion\ScrumBundle\Entity\StoryComment $storyComments)
    {
        $this->storyComments->removeElement($storyComments);
    }

    /**
     * Get storyComments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getStoryComments()
    {
        return $this->storyComments;
    }
}