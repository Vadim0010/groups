<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\User;
use AppBundle\Entity\Groups;
use AppBundle\Entity\Message;
use AppBundle\Entity\Comments;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;

class AdminController extends BaseAdminController
{
    const PERIOD = '-1 month';

    const USER_LIMIT = 40;

    const GROUP_LIMIT = 20;

    /**
     * @Route("/dashboard", name="admin_dashboard")
     */
    public function dashboardAction()
    {
        $em = $this->getDoctrine()->getManager();
        $datePeriod = new \DateTime(self::PERIOD);

        $countUsers = $em->getRepository(User::class)->getCountUsers();
        $countGroups = $em->getRepository(Groups::class)->getCountGroups();
        $countMessages = $em->getRepository(Message::class)->getCountMessages();
        $countComments = $em->getRepository(Comments::class)->getCountComments();
        $lastUsers = $em->getRepository(User::class)->getRecentlyRegisteredUsers($datePeriod, self::GROUP_LIMIT);
        $lastGroups = $em->getRepository(Groups::class)->getRecentlyAddedGroups($datePeriod, self::USER_LIMIT);


        return $this->render('@App/admin/dashboard.html.twig', [
            'period' => self::PERIOD,
            'countUsers' => $countUsers,
            'countGroups' => $countGroups,
            'countMessages' => $countMessages,
            'countComments' => $countComments,
            'lastUsers' => $lastUsers,
            'lastGroups' => $lastGroups
        ]);
    }
}
