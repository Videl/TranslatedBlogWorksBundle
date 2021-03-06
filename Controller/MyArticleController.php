<?php

namespace ALK\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ALK\WebBundle\Entity\Article;
use ALK\WebBundle\Form\ArticleType;

/**
 * Article controller.
 *
 */
class MyArticleController extends Controller
{
	public function showAction($id, $_locale)
	{
		$em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ALKWebBundle:Article')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }

        $deleteForm = $this->createDeleteForm($id);


        return $this->render('ALKWebBundle:MyArticles:showpageadmin.html.twig', array(
            'article'      => $entity,
            'delete_form' => $deleteForm->createView(),        
            ));
	}

    public function showWithSlugAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ALKWebBundle:Article')->myFindBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }

         $deleteForm = $this->createDeleteForm($entity->getId());

        return $this->render('ALKWebBundle:MyArticles:showpageadmin.html.twig', array(
            'article'      => $entity,
            'delete_form' => $deleteForm->createView(),        
            ));
    }

	public function editAction($id = null)
	{
        // Force defaultLocale into the translatableListener (Can be set by AOP for all new/edit method)

        $translatableListener = $this->get('stof_doctrine_extensions.listener.translatable');
        $translatableListener->setTranslatableLocale($translatableListener->getDefaultLocale());

		$em = $this->getDoctrine()->getManager();

		if($id)
		{
			$entity = $em->getRepository('ALKWebBundle:Article')->find($id);

			if (!$entity) {
                throw $this->createNotFoundException('Unable to find Article entity.');
            }

            $deleteForm = $this->createDeleteForm($id);
		} else {
            $entity = new Article();
        }

        $request = $this->getRequest();
        $editForm = $this->createForm(new ArticleType(), $entity);
        if ('POST' === $request->getMethod()) {
            $editForm->bindRequest($request);

            if ($editForm->isValid()) {
                $em->persist($entity);
                $em->flush();

                $this->get('session')->setFlash('info', ($id ? 'Edited!' : 'Created!'));

                return $this->redirect($this->generateUrl('alk_article', array('id' => $entity->getId())));
            }
        }

        
        return $this->render('ALKWebBundle:MyArticles:edit.html.twig',
        	array(
        		'article'  => $entity,
	            'editForm' => $editForm->createView()
				)
        + ($id ? array('delete_form' => $deleteForm->createView()) : array())
        );
	}

	public function deleteAction($id)
	{
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ALKWebBundle:Article')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }

        $this->get('session')->setFlash('info', 'Deleted ' . $entity->getTitle() . '.');

        $em->remove($entity);
        $em->flush();
    
		return $this->redirect($this->generateUrl('alk_web_homepage'));
	}

    public function listAllAction($tag)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ALKWebBundle:Article');

        if($tag == "Default")
        {
            $articles = $repository->myFindAllArticles();

        } else
        {
            $articles = $repository->myFindByTags(array($tag));
        }

        return $this->render('ALKWebBundle:MyArticles:showallarticles.html.twig', array(
            'articles' => $articles
            ));
    }


    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}