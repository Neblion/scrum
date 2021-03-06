<?php

namespace Neblion\ScrumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Neblion\ScrumBundle\Entity\ProcessStatus
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Neblion\ScrumBundle\Entity\ProcessStatusRepository")
 */
class ProcessStatus
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
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\ProjectRelease", mappedBy="status")
     */
    private $releases;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Sprint", mappedBy="status")
     */
    private $sprints;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Task", mappedBy="status")
     */
    private $tasks;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Story", mappedBy="status")
     */
    private $stories;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=50)
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
        $this->releases = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sprints = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add releases
     *
     * @param Neblion\ScrumBundle\Entity\ProjectRelease $releases
     */
    public function addProjectRelease(\Neblion\ScrumBundle\Entity\ProjectRelease $releases)
    {
        $this->releases[] = $releases;
    }

    /**
     * Get releases
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getReleases()
    {
        return $this->releases;
    }

    /**
     * Add sprints
     *
     * @param Neblion\ScrumBundle\Entity\Sprint $sprints
     */
    public function addSprint(\Neblion\ScrumBundle\Entity\Sprint $sprints)
    {
        $this->sprints[] = $sprints;
    }

    /**
     * Get sprints
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSprints()
    {
        return $this->sprints;
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
    
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Add stories
     *
     * @param Neblion\ScrumBundle\Entity\Story $stories
     */
    public function addStory(\Neblion\ScrumBundle\Entity\Story $stories)
    {
        $this->stories[] = $stories;
    }

    /**
     * Get stories
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getStories()
    {
        return $this->stories;
    }

    /**
     * Add releases
     *
     * @param Neblion\ScrumBundle\Entity\ProjectRelease $releases
     * @return ProcessStatus
     */
    public function addRelease(\Neblion\ScrumBundle\Entity\ProjectRelease $releases)
    {
        $this->releases[] = $releases;
    
        return $this;
    }

    /**
     * Remove releases
     *
     * @param Neblion\ScrumBundle\Entity\ProjectRelease $releases
     */
    public function removeRelease(\Neblion\ScrumBundle\Entity\ProjectRelease $releases)
    {
        $this->releases->removeElement($releases);
    }

    /**
     * Remove sprints
     *
     * @param Neblion\ScrumBundle\Entity\Sprint $sprints
     */
    public function removeSprint(\Neblion\ScrumBundle\Entity\Sprint $sprints)
    {
        $this->sprints->removeElement($sprints);
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
     * Add stories
     *
     * @param Neblion\ScrumBundle\Entity\Story $stories
     * @return ProcessStatus
     */
    public function addStorie(\Neblion\ScrumBundle\Entity\Story $stories)
    {
        $this->stories[] = $stories;
    
        return $this;
    }

    /**
     * Remove stories
     *
     * @param Neblion\ScrumBundle\Entity\Story $stories
     */
    public function removeStorie(\Neblion\ScrumBundle\Entity\Story $stories)
    {
        $this->stories->removeElement($stories);
    }
}