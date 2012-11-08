<?php

namespace Neblion\ScrumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Neblion\ScrumBundle\Entity\Activity
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Neblion\ScrumBundle\Entity\ActivityRepository")
 */
class Activity
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
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Project", inversedBy="activities")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    private $project;
    
    /**
     * @ORM\ManyToOne(targetEntity="Neblion\ScrumBundle\Entity\Account", inversedBy="activities")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    private $account;

    /**
     * @var string $text
     *
     * @ORM\Column(name="text", type="string", length=255)
     */
    private $text;

    /**
     * @var string $link_url
     *
     * @ORM\Column(name="link_url", type="string", length=255)
     */
    private $link_url;

    /**
     * @var string $link_text
     *
     * @ORM\Column(name="link_text", type="string", length=255)
     */
    private $link_text;


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
     * Set text
     *
     * @param string $text
     * @return Activity
     */
    public function setText($text)
    {
        $this->text = $text;
    
        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set link_url
     *
     * @param string $linkUrl
     * @return Activity
     */
    public function setLinkUrl($linkUrl)
    {
        $this->link_url = $linkUrl;
    
        return $this;
    }

    /**
     * Get link_url
     *
     * @return string 
     */
    public function getLinkUrl()
    {
        return $this->link_url;
    }

    /**
     * Set link_text
     *
     * @param string $linkText
     * @return Activity
     */
    public function setLinkText($linkText)
    {
        $this->link_text = $linkText;
    
        return $this;
    }

    /**
     * Get link_text
     *
     * @return string 
     */
    public function getLinkText()
    {
        return $this->link_text;
    }
}
