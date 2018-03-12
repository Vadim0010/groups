<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Groups;
use AppBundle\Entity\User;
use AppBundle\Form\SearchFormType;
use AppBundle\Form\SendMessageFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class HomeController extends Controller
{
    const GROUP_PER_PAGE = 16;

    /**
     * @Route("/", name="home")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchFrom = $this->createForm(SearchFormType::class);
        $searchFrom->handleRequest($request);

        if ($searchFrom->isSubmitted() && $searchFrom->isValid()) {
            $searchData = $searchFrom->getData();
            $query = $em
                ->getRepository(Groups::class)
                ->searchDqlGroups($searchData)
            ;
        } else {
            $query = $em
                ->getRepository('AppBundle:Groups')
                ->getDqlOrderedGroups()
            ;
        }

        try{
            $paginator  = $this->get('knp_paginator');
            $paginator = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                static::GROUP_PER_PAGE
            );

            $groups = $paginator->getItems();
        }catch(\Exception $e){
            dump($e->getMessage());die;
            // TODO: подумать что делать в случае поломки
        }


        return $this->render('AppBundle:pages:home.html.twig', [
            'groups' => $groups,
            'paginator' => $paginator,
            'searchForm' => $searchFrom->createView()
        ]);
    }

    /**
     * @Route("/{slug}/inside", name="group_inside")
     * @param Groups $group
     * @param Request $request
     * @return Response
     */
    public function insideAction(Groups $group, Request $request)
    {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $sendMessageForm = $this->createForm(SendMessageFormType::class);
        $sendMessageForm->handleRequest($request);
        
        $upHistories = $em->getRepository('AppBundle:UpHistory')
            ->findBy(['group' => $group], ['id' => 'DESC']);

        if ($group->getUser() !== $this->getUser()) {
            if ($session->has('viewed_groups')) {
                $viewedGroups = $session->get('viewed_groups');

                if (! in_array($group->getId(), $viewedGroups)) {
                    $this->refreshCountViews($group, $session, $viewedGroups);
                }
            } else {
                $this->refreshCountViews($group, $session);
            }
        }

        return $this->render('AppBundle:Groups:inside.html.twig', [
            'group' => $group,
            'upHistories' => $upHistories,
            'sendMessageForm' => $sendMessageForm->createView()
        ]);
    }

    /**
     * @Route("/{username}/profile", name="profile_show")
     *
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function showProfileAction(User $user, Request $request)
    {
        if (!is_object($user) || !$user instanceof User) {
            throw $this->createNotFoundException();
        }

        if ($this->getUser() === $user) {
            return $this->redirectToRoute('profile_edit');
        }

        $sendMessageForm = $this->createForm(SendMessageFormType::class);
        $sendMessageForm->handleRequest($request);

        return $this->render('@App/Profile/show_profile.html.twig', [
            'user' => $user,
            'sendMessageForm' => $sendMessageForm->createView()
        ]);
    }

    /**
     * Обновить количество просмотров у группы
     *
     * @param Groups $group
     * @param $session
     * @param array $viewedGroups
     */
    private function refreshCountViews($group, $session, array $viewedGroups = [])
    {
        $em = $this->getDoctrine()->getManager();
        $newCountViews = ((int) $group->getVisitors()) + 1;
        array_push($viewedGroups, $group->getId());
        $session->set('viewed_groups', $viewedGroups);
        $em->getRepository(Groups::class)->updateVisitors($group, $newCountViews);
        $em->refresh($group);
    }
}
