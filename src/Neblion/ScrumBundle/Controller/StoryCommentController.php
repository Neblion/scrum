<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Neblion\ScrumBundle\Entity\StoryComment;
use Neblion\ScrumBundle\Entity\Activity;
use Neblion\ScrumBundle\Form\StoryCommentType;

use Doctrine\ORM\Query;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * StoryComment controller.
 *
 * @Route("/storycomment")
 */
class StoryCommentController extends Controller
{
    

    /**
     * Displays a form to create a new StoryComment entity.
     *
     * @Route("/new", name="storycomment_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new StoryComment();
        $form   = $this->createForm(new StoryCommentType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new StoryComment entity.
     *
     * @Route("/{id}/create", name="storycomment_create")
     * @Method("POST")
     * @Template("NeblionScrumBundle:StoryComment:new.html.twig")
     */
    public function createAction(Request $request, $id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        // Load project
        $story = $em->getRepository('NeblionScrumBundle:Story')->load($id, Query::HYDRATE_OBJECT);
        if (!$story) {
            throw $this->createNotFoundException('Unable to find Story entity.');
        }
        $project = $story->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        $entity  = new StoryComment();
        $entity->setStory($story);
        $entity->setMember($member);
        $form = $this->createForm(new StoryCommentType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            
            // store activity            
            $this->get('scrum_activity')->add($project, $user, 'add comment on', 
                    $this->generateUrl('story_edit', array('id' => $story->getId())),
                    'Story #' . $story->getId());
            
            $em->flush();

            return $this->redirect($this->generateUrl('story_edit', array('id' => $story->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Edits an existing StoryComment entity.
     *
     * @Route("/{id}/update", name="storycomment_update")
     * @Method("POST")
     * @Template("NeblionScrumBundle:StoryComment:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NeblionScrumBundle:StoryComment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find StoryComment entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new StoryCommentType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('storycomment_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a StoryComment entity.
     *
     * @Route("/{id}/delete", name="storycomment_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NeblionScrumBundle:StoryComment')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find StoryComment entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('storycomment'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
