<?php

namespace TodoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use TodoBundle\Entity\Todo;
use TodoBundle\Form\TodoType;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="todo_create")
     */
    public function indexAction(Request $request)
    {
        $todo = new Todo();

        $form = $this->createForm(TodoType::class,$todo);
        $form->add('submit', SubmitType::class, array(
            'label' => 'Add To-Do',
            'attr'  => array('class' => 'btn btn-primary pull-right')
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($todo);
            $em->flush();

            return $this->redirect($this->generateUrl(
                'todo_list'
            ));
        }

        return $this->render('TodoBundle:Default:index.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/my/todos", name="todo_list")
     */
    public function listAction()
    {
        $repository = $this->getDoctrine()->getRepository('TodoBundle:Todo');
        $todos = $repository->findAll();

        return $this->render('TodoBundle:Default:list.html.twig',[
            'todos' => $todos
        ]);
    }


    /**
     * @Route("/todo/delete/{id}", name="todo_delete", requirements={"id" = "\d+"}, defaults={"id" = 0})
     */
    public function deleteAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $todo = $em->getRepository('TodoBundle:Todo')->find($id);

        if (!$todo) {
            throw $this->createNotFoundException(
                'No todo found for id '.$id
            );
        } else {
            $em->remove($todo);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'alert',
                'Todo Deleted!'
            );
            return $this->redirect($this->generateUrl('todo_list'));
        }
    }
}
