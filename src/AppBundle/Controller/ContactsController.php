<?php

namespace AppBundle\Controller;

use Psr\Log\LoggerInterface;
use AppBundle\Service\Notice;
use AppBundle\Entity\SiteContacts;
use AppBundle\Form\ReportFromType;
use AppBundle\Service\CaptchaVerify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ContactsController extends Controller
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Notice
     */
    private $noty;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, Notice $noty, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->noty = $noty;
        $this->translator = $translator;
    }

    /**
     * @Route("/contacts", name="contacts")
     *
     * @param Request $request\
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, CaptchaVerify $reCaptcha)
    {
        $contacts = $this->em->getRepository(SiteContacts::class)->findBy(['isEnabled' => true], ['updatedAt' => 'DESC']);
        $reportForm = $this->createForm(ReportFromType::class, null, ['user' => $this->getUser()]);
        $reportForm->handleRequest($request);

        if ($reportForm->isSubmitted() && $reportForm->isValid()) {
            $msg_error = $this->translator->trans('report_message_error');
            $reportData = $reportForm->getData();

            try {
                $accessor = PropertyAccess::createPropertyAccessor();
                $reCaptchaResponse = $reCaptcha->reCaptchaVerify();

                if ( ! $accessor->getValue($reCaptchaResponse, '[success]') ) {
                    $error = $accessor->getValue($reCaptchaResponse, '[error]');
                    $clientIp = $accessor->getValue($reCaptchaResponse, '[clientIp]');

                    if ( $error && $error == 'defined as a robot' ) {
                        $msg_error = $this->translator->trans('Failed to send message. We had a suspicion that you are a robot!');
                        throw new \Exception(
                            sprintf('Suspicion that the form sender is a robot. Name: %s, email: %s, message_body: %s, ip: %s. Error message - %s',
                                $reportForm->get('name')->getData(),
                                $reportForm->get('email')->getData(),
                                $reportForm->get('message')->getData(),
                                $clientIp,
                                implode(', ', $accessor->getValue($reCaptchaResponse, '[response][error-codes]'))
                            )
                        );
                    }

                    $msg_error = $this->translator->trans('Confirm that you are not a robot!');
                    throw new \Exception(
                        sprintf('Suspicion that the form sender is a robot. Name: %s, email: %s, message_body: %s, ip: %s',
                            $reportForm->get('name')->getData(),
                            $reportForm->get('email')->getData(),
                            $reportForm->get('message')->getData(),
                            $clientIp
                        )
                    );
                }

                if ($this->getUser()) {
                    $reportData->setSender($this->getUser());
                }

                $this->em->persist($reportData);
                $this->em->flush();
                $this->noty->success($this->translator->trans('report_message_sent'));

                return $this->redirectToRoute('contacts');

            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->noty->error($msg_error);
            }
        }

        return $this->render('AppBundle:pages:contacts.html.twig', [
            'reportForm' => $reportForm->createView(),
            'contacts' => $contacts
        ]);
    }
}
