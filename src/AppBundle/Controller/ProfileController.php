<?php

namespace AppBundle\Controller;

use AppBundle\AppBundleEvents;
use AppBundle\Entity\Groups;
use AppBundle\Entity\UserContacts;
use AppBundle\Event\DeleteGroupEvent;
use AppBundle\Form\AvatarFormType;
use AppBundle\Form\ProfileFormType;
use AppBundle\Service\InstagramApiClient;
use AppBundle\Service\Notice;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/profile")
 */
class ProfileController extends Controller
{
    /**
     * @Route("/", name="profile_edit")
     *
     * @param Request             $request
     * @param Notice              $noty
     * @param TranslatorInterface $translator
     * @param KernelInterface     $kernel
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Notice $noty, TranslatorInterface $translator, KernelInterface $kernel)
    {
        $user = $this->getUser();
        $user->getProfile();
        $profileForm = $this->createForm(ProfileFormType::class, $user->getProfile());
        $avatarForm = $this->createForm(AvatarFormType::class);

        $avatarForm->handleRequest($request);
        if($avatarForm->isSubmitted() && $avatarForm->isValid()){
            $avatar = $avatarForm->getData()['avatar'];
            $fileName = md5(uniqid('', false)).'.'.$avatar->guessExtension();

            $r = $avatar->move(
                sprintf('%s/%s', 'users', $user->getId()),
                $fileName
            );
            if($pathToOldAvatar = $user->getAvatar()){
                $fullPathToOldAvatar = $this->container->getParameter('kernel.root_dir') . '/../web/' . $pathToOldAvatar;
                @unlink($fullPathToOldAvatar);
                @$this->deleteImageFromCache($pathToOldAvatar, $kernel);
            }
            $user->setAvatar(str_replace('\\', '/', $r->getPathName()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $noty->success($translator->trans('Image uploaded successfully'));
            
            return $this->redirectToRoute('profile_edit');
        }

        $profileForm->handleRequest($request);
        if($profileForm->isSubmitted() && $profileForm->isValid()){
            $em = $this->getDoctrine()->getManager();
            /** @var UserContacts $profileData */
            $profileData = $profileForm->getData();
            $profileData->setUser($user);

            $em->persist($profileData);
            $em->flush();

            return $this->redirectToRoute('profile_edit');
        }

        return $this->render('AppBundle:Profile:edit.html.twig', [
            'profileForm' => $profileForm->createView(),
            'avatarForm' => $avatarForm->createView()
        ]);
    }

    /**
     * @Route("/list-of-groups", name="profile_group_list")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listOfGroupAction(Request $request)
    {
        $user = $this->getUser();
        $topGroup = null;

        $groupList = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Groups')
            ->getDqlUserGroups($user)
        ;

        try{
            $paginator  = $this->get('knp_paginator');
            $paginator = $paginator->paginate(
                $groupList,
                $request->query->getInt('p', 1),
                30
            );

            $groups = $paginator->getItems();
        }catch(\Exception $e){
            // TODO: подумать что делать в случае поломки
        }

        if($groupList and count($paginator) > 1){
            $topGroup = $this->getDoctrine()->getManager()->getRepository('AppBundle:Groups')->getUserTopGroup($user);
        }

        return $this->render('AppBundle:Profile:list_of_group.html.twig', [
            'topGroup' => $topGroup,
            'paginator' => $paginator
        ]);
    }

    /**
     * @Route("/delete-group/{id}", name="profile_delete_group")
     * @Method({"DELETE"})
     *
     * @param Groups $group
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteGroupAction(Groups $group, Request $request)
    {
        $token = $request->get('_token');
        $delete_slug = sprintf('%s__/DELETE_%s/', $group->getSlug(), time());
        $event = new DeleteGroupEvent($this->getUser(), $group);
        $this->get('event_dispatcher')->dispatch(AppBundleEvents::DELETING_GROUP, $event);

        if(!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete_group', $token) || ! $event->getCanDeleteGroup()){
            return new JsonResponse([
                'msg' => 'Unprocessable Entity',
            ], 422);
        }

        $em = $this->getDoctrine()->getManager();
        $group->setSlug( $delete_slug );
        $em->persist($group);
        $em->flush();
        $em->remove($group);
        $em->flush();

        return new JsonResponse([
            'msg' => $this->get('translator')->trans('Record was deleted')
        ]);
    }

    /**
     * @Route("/validate-group", name="ajax_validate_group")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param InstagramApiClient $instagramApiClient
     * @return JsonResponse
     */
    public function validateGroupAction(Request $request, InstagramApiClient $instagramApiClient)
    {
        $jsonResponse = new JsonResponse();

        try {
            $em = $this->getDoctrine()->getManager();
            $group = $em
                ->getRepository("AppBundle:Groups")
                ->find($request->get('group_id'))
            ;

            $checkGroup = $instagramApiClient->checkCode($group->getLink(), $request->get('code'));

            if($checkGroup){
                $group->setIsVerify($checkGroup);
                $em->persist($group);
                $em->flush();
            }

            $jsonResponse->setData([
                'validate' => $checkGroup
            ]);
        }catch(\Exception $e){
            $jsonResponse->setStatusCode(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            $jsonResponse->setData([
                'msg' => $e->getMessage()
            ]);
        }

        return $jsonResponse;
    }

    protected function deleteImageFromCache(string $path, KernelInterface $kernel)
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'liip:imagine:cache:remove',
            'paths' => [$path],
        ));

        $output = new NullOutput();
        $application->run($input, $output);
    }
}
