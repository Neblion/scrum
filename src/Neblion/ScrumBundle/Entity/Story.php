<?php

namespace Neblion\ScrumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Neblion\ScrumBundle\Entity\Story
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Neblion\ScrumBundle\Entity\StoryRepository")
 */
class Story
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
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Project", inversedBy="releases")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    private $project;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\StoryType", inversedBy="stories")
     * @ORM\JoinColumn(name="story_type_id", referencedColumnName="id", nullable=false)
     */
    private $type;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Feature", inversedBy="stories")
     * @ORM\JoinColumn(name="feature_id", referencedColumnName="id", nullable=true)
     */
    private $feature;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Sprint", inversedBy="stories")
     * @ORM\JoinColumn(name="sprint_id", referencedColumnName="id", nullable=true)
     */
    private $sprint;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\ProcessStatus", inversedBy="stories")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    private $status;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Task", mappedBy="story", cascade={"remove"})
     */
    private $tasks;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\StoryComment", mappedBy="story", cascade={"remove"})
     */
    private $comments;
    
    /**
     * @ORM\OneToOne(targetEntity="Neblion\ScrumBundle\Entity\Review", cascade={"remove"})
     */
    private $review;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=150)
     * @Assert\NotBlank()
     * @Assert\MaxLength(150)
     */
    private $name;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank()
     * @Assert\MaxLength(200)
     */
    private $description;

    /**
     * @var smallint $estimate
     *
     * @ORM\Column(name="estimate", type="smallint")
     * @Assert\Type(type="integer", message="The value {{ value }} is not a valid {{ type }}.")
     * @Assert\Min(limit = "0")
     */
    private $estimate = 0;
    
    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer")
     */
    private $position;
    
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
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Set estimate
     *
     * @param smallint $estimate
     */
    public function setEstimate($estimate)
    {
        $this->estimate = $estimate;
    }

    /**
     * Get estimate
     *
     * @return smallint 
     */
    public function getEstimate()
    {
        return $this->estimate;
    }

    /**
     * Set position
     *
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
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
     * Set feature
     *
     * @param Neblion\ScrumBundle\Entity\Feature $feature
     */
    public function setFeature(\Neblion\ScrumBundle\Entity\Feature $feature)
    {
        $this->feature = $feature;
    }

    /**
     * Get feature
     *
     * @return Neblion\ScrumBundle\Entity\Feature 
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Set sprint
     *
     * @param Neblion\ScrumBundle\Entity\Sprint $sprint
     */
    public function setSprint(\Neblion\ScrumBundle\Entity\Sprint $sprint = null)
    {
        $this->sprint = $sprint;
    }

    /**
     * Get sprint
     *
     * @return Neblion\ScrumBundle\Entity\Sprint 
     */
    public function getSprint()
    {
        return $this->sprint;
    }

    /**
     * Set status
     *
     * @param Neblion\ScrumBundle\Entity\ProcessStatus $status
     */
    public function setStatus(\Neblion\ScrumBundle\Entity\ProcessStatus $status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return Neblion\ScrumBundle\Entity\ProcessStatus 
     */
    public function getStatus()
    {
        return $this->status;
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
     * Set review
     *
     * @param Neblion\ScrumBundle\Entity\Review $review
     */
    public function setReview(\Neblion\ScrumBundle\Entity\Review $review)
    {
        $this->review = $review;
    }

    /**
     * Get review
     *
     * @return Neblion\ScrumBundle\Entity\Review 
     */
    public function getReview()
    {
        return $this->review;
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
     * Set type
     *
     * @param Neblion\ScrumBundle\Entity\StoryType $type
     * @return Story
     */
    public function setType(\Neblion\ScrumBundle\Entity\StoryType $type = null)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return Neblion\ScrumBundle\Entity\StoryType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add comments
     *
     * @param Neblion\ScrumBundle\Entity\StoryComment $comments
     * @return Story
     */
    public function addComment(\Neblion\ScrumBundle\Entity\StoryComment $comments)
    {
        $this->comments[] = $comments;
    
        return $this;
    }

    /**
     * Remove comments
     *
     * @param Neblion\ScrumBundle\Entity\StoryComment $comments
     */
    public function removeComment(\Neblion\ScrumBundle\Entity\StoryComment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Get comments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }
}