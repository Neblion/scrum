<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Neblion\ScrumBundle\Entity\Review;
use Neblion\ScrumBundle\Form\ReviewType;

use Doctrine\ORM\Query;

/**
 * Review controller.
 *
 * @Route("/review")
 */
class ReviewController extends Controller
{
    /**
     * Lists all Review entities.
     *
     * @Route("/{id}", name="review_list")
     * @Template()
     */
    public function indexAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $sprint = $em->getRepository('NeblionScrumBundle:Sprint')->load($id);
        if (!$sprint) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }
        
        $project = $sprint->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        // Load stories for this sprint
        $stories = $em->getRepository('NeblionScrumBundle:Story')
                ->getSprintDetails($sprint->getId(), true);
        
        $forms = array();
        
        if ($member->getRole()->getId() == 1 or $member->getAdmin()) {
            foreach ($stories as $story) {
                if ($story->getReview()) {
                    $review = $story->getReview();
                } else {
                    $review = new \Neblion\ScrumBundle\Entity\Review();
                }
                $forms[$story->getId()] = $this->createForm(new ReviewType(), $review)->createView();
            }
        }
            
        return array(
            'project'       => $project,
            'sprint'        => $sprint,
            //'review'        => $review,
            'stories'       => $stories,
            'forms'         => $forms,
            'member'        => $member,
        );
    }

    /**
     * Creates a new Review entity.
     *
     * @Route("/{id}/create", name="review_create")
     * @Method("post")
     * @Template("NeblionScrumBundle:Review:new.html.twig")
     */
    public function createAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $story = $em->getRepository('NeblionScrumBundle:Story')->find($id);
        if (!$story) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }
        
        $project = $story->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            throw new AccessDeniedException();
        }
        
        $review  = new Review();
        $request = $this->getRequest();
        $form    = $this->createForm(new ReviewType(), $review);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em->persist($review);
            $story->setReview($review);
            
            // store activity            
            $this->get('scrum_activity')->add($project, $user, 'create review', 
                    $this->generateUrl('review_list', array('id' => $story->getId())), 
                    'Story #' . $story->getId());
            
            $em->flush();

            // Set flash message
            $this->get('session')->setFlash('success', 'Review was created with success!');
            return $this->redirect($this->generateUrl('review_list', array('id' => $story->getSprint()->getId())));
        }

        return array(
            'project'   => $project,
            'story'     => $story,
            'review'    => $review,
            'form'      => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Review entity.
     *
     * @Route("/{id}/edit", name="review_edit")
     * @Template("NeblionScrumBundle:Review:edit.html.twig")
     */
    public function editAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $review = $em->getRepository('NeblionScrumBundle:Review')->load($id);
        if (!$review) {
            throw $this->createNotFoundException('Unable to find Review entity.');
        }
        
        $project = $review->getStory()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            throw new AccessDeniedException();
        }

        $editForm   = $this->createForm(new ReviewType(), $review);

        return array(
            'review'        => $review,
            'form'          => $editForm->createView(),
        );
    }

    /**
     * Edits an existing Review entity.
     *
     * @Route("/{id}/update", name="review_update")
     * @Method("post")
     * @Template("NeblionScrumBundle:Review:edit.html.twig")
     */
    public function updateAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $story = $em->getRepository('NeblionScrumBundle:Story')->load($id, Query::HYDRATE_OBJECT);
        if (!$story) {
            throw $this->createNotFoundException('Unable to find Story entity.');
        }
        
        $project = $story->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            throw new AccessDeniedException();
        }
        
        $review = $story->getReview();

        $editForm   = $this->createForm(new ReviewType(), $review);
        $request = $this->getRequest();
        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($review);
            
            // store activity            
            $this->get('scrum_activity')->add($project, $user, 'update review', 
                    $this->generateUrl('review_list', array('id' => $story->getId())), 
                    'Story #' . $story->getId());
            
            $em->flush();
            
            // Set flash message
            $this->get('session')->setFlash('success', 'Review was updated with success!');
            return $this->redirect($this->generateUrl('review_list', 
                    array('id' => $story->getSprint()->getId())));
        }

        return array(
            'review'        => $review,
            'form'          => $editForm->createView(),
        );
    }

    /**
     * Deletes a Review entity.
     *
     * @Route("/{id}/delete", name="review_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('NeblionScrumBundle:Review')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Review entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('review'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
