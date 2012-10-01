<?php

namespace Neblion\ScrumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Neblion\ScrumBundle\Entity\Feature
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Neblion\ScrumBundle\Entity\FeatureRepository")
 */
class Feature
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
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Project", inversedBy="features")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    private $project;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Story", mappedBy="feature")
     */
    private $stories;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\MaxLength(50)
     */
    private $name;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank()
     */
    private $description;
    
    /**
     * @var string $color
     *
     * @ORM\Column(name="color", type="string", length=10)
     * @Assert\NotBlank()
     * @Assert\MaxLength(7)
     * @Assert\Regex(
     *  pattern="/^#(([a-fA-F0-9]{3}$)|([a-fA-F0-9]{6}$))/", 
     *  match=true, 
     *  message="This is not a valid hexadecimal color!")
     */
    private $color;
    
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

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }
    public function __construct()
    {
        $this->stories = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return datetime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param datetime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * Get updated
     *
     * @return datetime 
     */
    public function getUpdated()
    {
        return $this->updated;
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
     *
     * @return type 
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set color
     *
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Add stories
     *
     * @param Neblion\ScrumBundle\Entity\Story $stories
     * @return Feature
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