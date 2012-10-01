<?php

namespace Neblion\ScrumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Neblion\ScrumBundle\Entity\Team
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Neblion\ScrumBundle\Entity\TeamRepository")
 */
class Team
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
     * @ORM\OneToOne(targetEntity="Neblion\ScrumBundle\Entity\Project", inversedBy="team")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    private $project;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Member", mappedBy="team", cascade={"remove"})
     */
    private $members;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    public function __construct()
    {
        $this->members = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set project
     *
     * @param Neblion\ScrumBundle\Entity\Project $project
     */
    public function setProject(\Neblion\ScrumBundle\Entity\Project $project)
    {
        $this->project = $project;
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
     * Remove members
     *
     * @param Neblion\ScrumBundle\Entity\Member $members
     */
    public function removeMember(\Neblion\ScrumBundle\Entity\Member $members)
    {
        $this->members->removeElement($members);
    }
}