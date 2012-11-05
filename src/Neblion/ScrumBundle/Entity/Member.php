<?php

namespace Neblion\ScrumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Neblion\ScrumBundle\Entity\Member
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Neblion\ScrumBundle\Entity\MemberRepository")
 */
class Member
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Project", inversedBy="members")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    private $project;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Account", inversedBy="members")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    private $account;

    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Role", inversedBy="members")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     */
    private $role;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\MemberStatus", inversedBy="members")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    private $status;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Task", mappedBy="member")
     */
    private $tasks;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\StoryComment", mappedBy="member")
     */
    private $storyComments;
    
    /**
     * @var boolean $admin
     * @ORM\Column(name="admin", type="boolean", nullable=false)
     */
    private $admin;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Account", inversedBy="invitations")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id", nullable=true)
     */
    private $sender;
    
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(name="updated", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updated;
    
    
    public function __construct()
    {
        $this->admin    = false;
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
     * Set team
     *
     * @param Neblion\ScrumBundle\Entity\Team $team
     */
    public function setTeam(\Neblion\ScrumBundle\Entity\Team $team)
    {
        $this->team = $team;
    }

    /**
     * Get team
     *
     * @return Neblion\ScrumBundle\Entity\Team 
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set account
     *
     * @param Neblion\ScrumBundle\Entity\Account $account
     */
    public function setAccount(\Neblion\ScrumBundle\Entity\Account $account)
    {
        $this->account = $account;
    }

    /**
     * Get account
     *
     * @return Neblion\ScrumBundle\Entity\Account 
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set role
     *
     * @param Neblion\ScrumBundle\Entity\Role $role
     */
    public function setRole(\Neblion\ScrumBundle\Entity\Role $role)
    {
        $this->role = $role;
    }

    /**
     * Get role
     *
     * @return Neblion\ScrumBundle\Entity\Role 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set status
     *
     * @param Neblion\ScrumBundle\Entity\MemberStatus $status
     */
    public function setStatus(\Neblion\ScrumBundle\Entity\MemberStatus $status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return Neblion\ScrumBundle\Entity\MemberStatus 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set admin
     *
     * @param boolean $admin
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    /**
     * Get admin
     *
     * @return boolean 
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
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
     * Remove tasks
     *
     * @param Neblion\ScrumBundle\Entity\Task $tasks
     */
    public function removeTask(\Neblion\ScrumBundle\Entity\Task $tasks)
    {
        $this->tasks->removeElement($tasks);
    }

    /**
     * Set sender
     *
     * @param Neblion\ScrumBundle\Entity\Account $sender
     * @return Member
     */
    public function setSender(\Neblion\ScrumBundle\Entity\Account $sender = null)
    {
        $this->sender = $sender;
    
        return $this;
    }

    /**
     * Get sender
     *
     * @return Neblion\ScrumBundle\Entity\Account 
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Member
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Member
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    
        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Add storyComments
     *
     * @param Neblion\ScrumBundle\Entity\StoryComment $storyComments
     * @return Member
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

    /**
     * Set project
     *
     * @param Neblion\ScrumBundle\Entity\Project $project
     * @return Member
     */
    public function setProject(\Neblion\ScrumBundle\Entity\Project $project = null)
    {
        $this->project = $project;
    
        return $this;
    }

    /**
     * Get project
     *
     * @return Neblion\ScrumBundle\Entity\Project 
     */
    public function getProject()
    {
        return $this->project;
    }
}