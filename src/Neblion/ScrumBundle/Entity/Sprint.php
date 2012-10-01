<?php

namespace Neblion\ScrumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\ExecutionContext;

/**
 * Neblion\ScrumBundle\Entity\Sprint
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Neblion\ScrumBundle\Entity\SprintRepository")
 * @Assert\Callback(methods={"endBeforeStart"})
 */
class Sprint
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
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\ProjectRelease", inversedBy="sprints")
     * @ORM\JoinColumn(name="project_release_id", referencedColumnName="id")
     */
    private $projectRelease;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Story", mappedBy="sprint")
     */
    private $stories;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\ProcessStatus", inversedBy="sprints")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    private $status;
    
    /**
     * @ORM\OneToMany(targetEntity="Neblion\ScrumBundle\Entity\Retrospective", mappedBy="sprint")
     */
    private $retrospective;
    
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
     */
    private $description;

    /**
     * @var date $start
     *
     * @ORM\Column(name="start", type="date")
     * @Assert\Date()
     */
    private $start;

    /**
     * @var date $end
     *
     * @ORM\Column(name="end", type="date")
     * @Assert\Date()
     */
    private $end;
    
    /**
     * @var integer $velocity
     *
     * @ORM\Column(name="velocity", type="smallint")
     * @Assert\Min(limit = "0")
     */
    private $velocity;
    
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
                $context->addViolation('The sprint could not start in the past!', array(), null);
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
                    $context->addViolation('The end of sprint could not be before start!', array(), null);
                }
            }
    }
    
    public function __construct()
    {
        $this->stories = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Set velocity
     *
     * @param smallint $velocity
     */
    public function setVelocity($velocity)
    {
        $this->velocity = $velocity;
    }

    /**
     * Get velocity
     *
     * @return smallint 
     */
    public function getVelocity()
    {
        return $this->velocity;
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
     * Set projectRelease
     *
     * @param Neblion\ScrumBundle\Entity\ProjectRelease $projectRelease
     */
    public function setProjectRelease(\Neblion\ScrumBundle\Entity\ProjectRelease $projectRelease)
    {
        $this->projectRelease = $projectRelease;
    }

    /**
     * Get projectRelease
     *
     * @return Neblion\ScrumBundle\Entity\ProjectRelease 
     */
    public function getProjectRelease()
    {
        return $this->projectRelease;
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
     * Set retrospective
     *
     * @param Neblion\ScrumBundle\Entity\Retrospective $retrospective
     */
    public function setRetrospective(\Neblion\ScrumBundle\Entity\Retrospective $retrospective)
    {
        $this->retrospective = $retrospective;
    }

    /**
     * Get retrospective
     *
     * @return Neblion\ScrumBundle\Entity\Retrospective 
     */
    public function getRetrospective()
    {
        return $this->retrospective;
    }

    /**
     * Add retrospective
     *
     * @param Neblion\ScrumBundle\Entity\Retrospective $retrospective
     */
    public function addRetrospective(\Neblion\ScrumBundle\Entity\Retrospective $retrospective)
    {
        $this->retrospective[] = $retrospective;
    }

    /**
     * Add stories
     *
     * @param Neblion\ScrumBundle\Entity\Story $stories
     * @return Sprint
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

    /**
     * Remove retrospective
     *
     * @param Neblion\ScrumBundle\Entity\Retrospective $retrospective
     */
    public function removeRetrospective(\Neblion\ScrumBundle\Entity\Retrospective $retrospective)
    {
        $this->retrospective->removeElement($retrospective);
    }
}