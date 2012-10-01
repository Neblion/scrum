<?php

namespace Neblion\ScrumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Neblion\ScrumBundle\Entity\Retrospective
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Neblion\ScrumBundle\Entity\RetrospectiveRepository")
 */
class Retrospective
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
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Sprint", inversedBy="retrospective")
     * @ORM\JoinColumn(name="sprint_id", referencedColumnName="id")
     */
    private $sprint;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Account", inversedBy="retrospectives")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var text $comment
     *
     * @ORM\Column(name="comment", type="text")
     * @Assert\NotBlank()
     */
    private $comment;
    
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
     * Set comment
     *
     * @param text $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get comment
     *
     * @return text 
     */
    public function getComment()
    {
        return $this->comment;
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
     * Set sprint
     *
     * @param Neblion\ScrumBundle\Entity\Sprint $sprint
     */
    public function setSprint(\Neblion\ScrumBundle\Entity\Sprint $sprint)
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
     * Set user
     *
     * @param Neblion\ScrumBundle\Entity\Account $user
     */
    public function setUser(\Neblion\ScrumBundle\Entity\Account $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Neblion\ScrumBundle\Entity\Account 
     */
    public function getUser()
    {
        return $this->user;
    }
}