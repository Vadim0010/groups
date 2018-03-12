<?php

namespace AppBundle\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class InvitationLetterCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    private $templateLayouts = '@App/email/email_invite.txt.twig';

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * InvitationLetterCommand constructor.
     *
     * @param null $name
     * @param \Twig_Environment $twig
     * @param LoggerInterface $logger
     * @param \Swift_Mailer $mailer
     * @param ValidatorInterface $validator
     */
    public function __construct($name = null, \Twig_Environment $twig, LoggerInterface $logger, \Swift_Mailer $mailer, ValidatorInterface $validator)
    {
        $this->twig = $twig;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->validator = $validator;

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:invitation-letter')
            ->setDescription('Invitations to the site')
            ->addOption('isSent', null, InputOption::VALUE_REQUIRED, 'Confirm sending')
            ->setHelp('Send invitations "Visit website" to email address')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Invitations to the StoreInstagram website');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $value = mb_strtoupper($input->getOption('isSent'));
        $isSent =  $value === 'Y' || $value === 'YES' ? true : false;

        if ($isSent) {
            $emailConstraint = new Email();
            $fromEmail = $this->getContainer()->getParameter('mail_address');
            $emails = $this->getEmails();
            $dataOutput = [
                'status' => 'error',
                'msg' => ''
            ];

            if ($emails) {
                foreach ($emails as $item) {

                    $email = trim($item);
                    $errors = $this->validator->validate($email, $emailConstraint);

                    if (count($errors) > 0) {
                        $dataOutput['status'] = 'error';
                        $dataOutput['msg'] = (string) $errors->get(0)->getMessage();
                    } else {
                        if ($this->sendMessage($fromEmail, $email)) {
                            $dataOutput['status'] = 'success';
                            $dataOutput['msg'] = 'email successfully sent';
                        } else {
                            $dataOutput['status'] = 'warning';
                            $dataOutput['msg'] = 'email not sent';
                        }
                    }

                    $message = sprintf('%s | %s',
                        str_pad($email, 20),
                        $dataOutput['msg']
                    );

                    switch ($dataOutput['status']) {
                        case 'success':
                            $this->io->success($message);
                            break;
                        case 'warning':
                            $this->io->warning($message);
                            break;
                        default:
                            $this->io->error($message);
                    }
                }
            } else {
                $this->io->caution('Could not get email');
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $input->setOption(
            'isSent',
            $this->io->ask('You really want to send invitations to the entire list of users?', 'Y', function($value) {

                $value = mb_strtoupper($value);

                if ( $value != 'Y' && $value != 'N' && $value != 'YES' && $value != 'NO' && $value != 'NOT') {
                    throw new \RuntimeException('You can only answer "Y" or "N"');
                }

                return $value;
            })
        );
    }

    /**
     * To send a email
     *
     * @param $fromEmail
     * @param $toEmail
     * @return bool|int
     */
    private function sendMessage($fromEmail, $toEmail)
    {
        $template = $this->twig->load($this->templateLayouts);
        $subject = $template->renderBlock('subject');
        $textBody = $template->renderBlock('body_text');
        $htmlBody = $template->renderBlock('body_html');

        try {
            $message = (new \Swift_Message())
                ->setSubject($subject)
                ->setFrom($fromEmail)
                ->setTo($toEmail);

            if (!empty($htmlBody)) {
                $message->setBody($htmlBody, 'text/html')
                    ->addPart($textBody, 'text/plain');
            } else {
                $message->setBody($textBody);
            }

            return $this->mailer->send($message);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    /**
     * Get list of mails from file
     *
     * @return array|bool
     */
    private function getEmails()
    {
        $filePath = sprintf(
            '%s/var/files/emails_invite.log',
            $this->getContainer()->getParameter('kernel.project_dir')
        );
        $file = new File($filePath);

        $emails = $file->isFile()
            ? explode(PHP_EOL, file_get_contents($file->getPathname(), true) )
            : false
        ;

        return $emails;
    }
}