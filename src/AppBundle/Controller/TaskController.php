<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Form\Type\TaskType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    /**
     * @Route("/tasks", name="task_list")
     * @Method("GET")
     */
    public function listAction()
    {
        $response =  $this->render('task/list.html.twig', ['tasks' => $this->getDoctrine()->getRepository('AppBundle:Task')->findAll()]);
        $response->setSharedMaxAge(45);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        return $response;
    }

    /**
     * @Route("/tasks/create", name="task_create")
     * @Method({"GET", "POST"})
     */
    public function createAction(Request $request, Response $response = NULL)
    {
        $task = new Task();
        $user = $this->getUser();
        $form = $this->createForm(TaskType::class, $task);



        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $task->setAuthor($user);
            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');
            if($response){
                $response->expire();
            }
            return $this->redirectToRoute('task_list');
        }

        $response =  $this->render('task/create.html.twig', ['form' => $form->createView()]);
        $response->setSharedMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Response $response = NULL, Task $task, Request $request)
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');
            if($response){
                $response->expire();
            }
            return $this->redirectToRoute('task_list');
        }

        $response =  $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
        $response->setSharedMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * @Method({"GET", "POST"})
     */
    public function toggleTaskAction(Response $response = null, Task $task)
    {
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();


        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));
        if($response){
            $response->expire();
        }
        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @Method({"GET", "POST"})
     */
    public function deleteTaskAction(Response $response = null, Task $task)
    {

        if ($task->getAuthor() != $this->getUser() && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        {

            $this->addFlash('error', sprintf("Vous ne pouvez pas supprimer la tâche '%s' car vous n'en êtes pas l'auteur", $task->getTitle()));
            if($response){
                $response->expire();
            }
            return $this->redirectToRoute('task_list');

        } else {
            $em = $this->getDoctrine()->getManager();
            $em->remove($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a bien été supprimée.');
            if($response){
                $response->expire();
            }
            return $this->redirectToRoute('task_list');
        }
    }
}
