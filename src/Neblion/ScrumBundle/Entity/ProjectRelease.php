<?php

namespace Neblion\ScrumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Neblion\ScrumBundle\Validator\Constraints\ProjectRelease as ReleaseAssert;
use Symfony\Component\Validator\ExecutionContext;


/**
 * Neblion\ScrumBundle\Entity\ProjectRelease
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Neblion\ScrumBundle\Entity\ProjectReleaseRepository")
 * @Assert\Callback(methods={"endBeforeStart"})
 */
class ProjectRelease
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
     * //@ReleaseAssert\NoDueDateInProgress
     */
    private $project;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Sprint", mappedBy="projectRelease", cascade={"remove"})
     */
    private $sprints;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\ProcessStatus", inversedBy="releases")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=false)
     */
    private $status;

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
     * @var date $start
     *
     * @ORM\Column(name="start", type="date", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    private $start;

    /**
     * @var date $end
     *
     * @ORM\Column(name="end", type="date", nullable=true)
     * @Assert\Date()
     */
    private $end;
    
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
     *
     * @param ExecutionContext $context 
     */
    public function startInPast(ExecutionContext $context)
    {
        if ($this->getStart()) {
            $now = new \DateTime('now');
            $interval = $now->diff($this->getStart());

            if ($interval->format('%R%a') < 0) {
                $propertyPath = $context->getPropertyPath() . '.start';
                $context->setPropertyPath($propertyPath);
                $context->addViolation('The release could not start in the past!', array(), null);
            }
        }
    }
    
    /**
     *
     * @param ExecutionContext $context 
     */
    public function endBeforeStart(ExecutionContext $context)
    {
            if ($this->getEnd()) {
                if ($this->getEnd() <= $this->getStart()) {
                    $propertyPath = $context->getPropertyPath() . '.end';
                    $context->setPropertyPath($propertyPath);
                    $context->addViolation('The end of release could not be before start!', array(), null);
                }
            }
    }
    
    public function __toString()
    {
        return $this->getName();
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
     * Set start
     *
     * @param date $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * Get start
     *
     * @return date 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param date $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * Get end
     *
     * @return date 
     */
    public function getEnd()
    {
        return $this->end;
    }
    public function __construct()
    {
        $this->sprints = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Remove sprints
     *
     * @param Neblion\ScrumBundle\Entity\Sprint $sprints
     */
    public function removeSprint(\Neblion\ScrumBundle\Entity\Sprint $sprints)
    {
        $this->sprints->removeElement($sprints);
    }
}