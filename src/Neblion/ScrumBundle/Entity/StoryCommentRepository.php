<?php

namespace Neblion\ScrumBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * StoryCommentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StoryCommentRepository extends EntityRepository
{
    public function loadForStory(\Neblion\ScrumBundle\Entity\Story $story)
    {
        return $this->getEntityManager()
                ->createQuery(
                    'SELECT st, s, m, a, p
                    FROM NeblionScrumBundle:StoryComment st
                    INNER JOIN st.story s
                    INNER JOIN st.member m
                    INNER JOIN m.account a
                    INNER JOIN a.profile p
                    WHERE s.id = :story_id
                    ORDER BY st.created'
                )
                ->setParameter('story_id', $story->getId())
                ->getResult();
    }
}
