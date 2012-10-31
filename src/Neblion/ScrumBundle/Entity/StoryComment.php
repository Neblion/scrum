<?php

namespace Neblion\ScrumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Neblion\ScrumBundle\Entity\StoryComment
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Neblion\ScrumBundle\Entity\StoryCommentRepository")
 */
class StoryComment
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
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Story", inversedBy="comments")
     * @ORM\JoinColumn(name="story_id", referencedColumnName="id")
     */
    private $story;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Member", inversedBy="storyComments")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id")
     */
    private $member;

    /**
     * @var string $comment
     *
     * @ORM\Column(name="comment", type="text")
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
     * @param string $comment
     * @return StoryComment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    
        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set story
     *
     * @param Neblion\ScrumBundle\Entity\Story $story
     * @return StoryComment
     */
    public function setStory(\Neblion\ScrumBundle\Entity\Story $story = null)
    {
        $this->story = $story;
    
        return $this;
    }

    /**
     * Get story
     *
     * @return Neblion\ScrumBundle\Entity\Story 
     */
    public function getStory()
    {
        return $this->story;
    }

    /**
     * Set user
     *
     * @param Neblion\ScrumBundle\Entity\Account $user
     * @return StoryComment
     */
    public function setUser(\Neblion\ScrumBundle\Entity\Account $user = null)
    {
        $this->user = $user;
    
        return $this;
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

    /**
     * Set member
     *
     * @param Neblion\ScrumBundle\Entity\Member $member
     * @return StoryComment
     */
    public function setMember(\Neblion\ScrumBundle\Entity\Member $member = null)
    {
        $this->member = $member;
    
        return $this;
    }

    /**
     * Get member
     *
     * @return Neblion\ScrumBundle\Entity\Member 
     */
    public function getMember()
    {
        return $this->member;
    }
}