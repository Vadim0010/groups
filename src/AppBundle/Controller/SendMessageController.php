<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Chat;
use AppBundle\Entity\Groups;
use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\HelperClasses\EntityClass;
use AppBundle\Service\HandleMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Translation\TranslatorInterface;

class SendMessageController extends Controller
{
    use EntityClass;

    /**
     * Количество выводимых сообщений
     */
    const MESSAGE_LIMIT = 50;
    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->translator = $translator;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @Route("/profile/messages",
     *     name="messages-list"
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function listChatsAction(Request $request)
    {
        $select_chat = $this->getEntity($this->em, $request->query->get('chat'), Chat::class);

        if (
            ($request->query->has('chat') && ! $select_chat instanceof Chat) ||
            ($select_chat instanceof Chat && ! $select_chat->getUser()->contains($this->getUser()))
        ) {
            throw $this->createNotFoundException();
        }

        if ($select_chat instanceof Chat) {

            if ($select_chat->getMessage()->count()) {
                $this->isReadMessage($select_chat->getMessage());
            }
        }

        $chats = $this->em->getRepository(Chat::class)->getChatListForUser($this->getUser());

        return $this->render('AppBundle:messages:chat.html.twig', ['chats' => $chats, 'select_chat' => $select_chat]);
    }

    /**
     * @Route("/profile/message/read",
     *     name="ajax-chat-read",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function readMessageAction(Request $request)
    {
        $msg = '';

        try {
            if ($request->isXmlHttpRequest()) {
                $chat = $this->getEntity($this->em, $request->get('chat'), Chat::class);

                if (!$chat || ! $chat instanceof Chat) {
                    $msg = $this->translator->trans('message_no_chat');
                    throw new \Exception(
                        sprintf(
                            'Could not find chat (id = %s). Error in the function %s on line %s',
                            $request->get('chat') ?? 'chat_id not found',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                if (! $this->isGranted('ROLE_USER') || ! $chat->getUser()->contains($this->getUser())) {
                    $msg = $this->translator->trans('message_no_access_chat');
                    throw new \Exception(
                        sprintf(
                            'User (id = %s) does not have access to view chat (id = %s). Error in the function %s on line %s',
                            $this->getUser() ? $this->getUser()->getId() : 'user_id not found',
                            $chat->getId(),
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                $messages = $this->em->getRepository(Message::class)->getChatMessages($chat, self::MESSAGE_LIMIT);
                $dataMessage = $this->getResponseData(false, 200, '');
                $dataMessage['chat_name'] = $chat->getSubject();

                if(count($messages)){
                    $dataMessage['template'] = $this->renderView(
                        '@App/messages/parts/message-list-template.html.twig',
                        ['messages' => $messages]
                    );

                    $this->isReadMessage($messages);
                }

            } else {
                throw new \Exception(
                    sprintf(
                        'Failed to get Ajax request when receiving chat (id = %s) for user (id = %s). Error in the function %s on line %s',
                        $request->get('chat', 'chat_id not found'),
                        $this->getUser() ? $this->getUser()->getId() : 'user_id is not found',
                        __METHOD__,
                        __LINE__
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $msg_error = $msg != '' ? $msg : $this->translator->trans('message_sent_error');
            $dataMessage = $this->getResponseData(true, 400, $msg_error);
        }

        return new JsonResponse($dataMessage, $dataMessage['status']);
    }

    /**
     * @Route("/profile/message/send",
     *     name="ajax-chat-new",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @param HandleMessage $handleMessage
     * @return JsonResponse
     */
    public function newChatAction(Request $request, HandleMessage $handleMessage)
    {
        $_token = $request->get('_token');
        $msg = '';

        try {
            if (
                $request->isXmlHttpRequest() &&
                $this->isCsrfTokenValid('message', $_token) &&
                $this->isGranted('ROLE_USER')
            ) {
                $sender = $this->getUser();
                $recipient = $this->getEntity($this->em, $request->get('recipient'), User::class);
                $group = $this->getEntity($this->em, $request->get('group'), Groups::class);
                $subject = $handleMessage->stripTagsMessage(
                    $handleMessage->stripEmojiMessage(
                        $request->get('subject')
                    )
                );
                $body = $handleMessage->stripTagsMessage($request->get('body'));

                if ( ! $group && $request->get('group') !== '0' ) {
                    $msg = $this->translator->trans('message_no_group');
                    throw new \Exception(
                        sprintf(
                            'Could not find group (id = %s). Error in the function %s on line %s',
                            $request->get('group') ?? 'group_id not found',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                if ( ! $recipient || ! $recipient instanceof User ) {
                    $msg = $this->translator->trans('message_no_recipient');
                    throw new \Exception(
                        sprintf(
                            'Could not find user (id = %s). Error in the function %s on line %s',
                            $request->get('recipient') ?? 'user_id not found',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                if ($recipient == $sender) {
                    $msg = $this->translator->trans('message_no_himself');
                    $dataMessage = $this->getResponseData(true, 400, $msg);
                    return new JsonResponse($dataMessage, $dataMessage['status']);
                }

                if(!$subject) {
                    $msg = $this->translator->trans('message_no_subject');
                    $dataMessage = $this->getResponseData(true, 400, $msg);
                    return new JsonResponse($dataMessage, $dataMessage['status']);
                }

                if(!$body) {
                    $msg = $this->translator->trans('message_no_body');
                    $dataMessage = $this->getResponseData(true, 400, $msg);
                    return new JsonResponse($dataMessage, $dataMessage['status']);
                }

                $chat = $this->findChat($sender, $recipient, $group, $subject);
                $this->saveDataChat($sender, $body, $chat, $recipient, $subject, $group);
                $dataMessage = $this->getResponseData(false, 200, $this->translator->trans('message_sent_success'));

            } else {
                throw new \Exception(
                    sprintf(
                        'Failed to get ajax request when creating a new chat for the user (id = %s) and a token (%s). Error in the function %s on line %s',
                        $this->getUser() ? $this->getUser()->getId() : 'user_id not found',
                        $_token,
                        __METHOD__,
                        __LINE__
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $msg_error = $msg != '' ? $msg : $this->translator->trans('message_sent_error');
            $dataMessage = $this->getResponseData(true, 400, $msg_error);
        }

        return new JsonResponse($dataMessage, $dataMessage['status']);
    }

    /**
     * @Route("/profile/message/reply",
     *     name="ajax-chat-reply",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @param HandleMessage $handleMessage
     * @return JsonResponse
     */
    public function replyChatAction(Request $request, HandleMessage $handleMessage)
    {
        $msg = '';
        $_token = $request->get('_token');

        try {
            if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('message', $_token)) {
                $chat = $this->getEntity($this->em, $request->get('chat'), Chat::class);
                $reply_msg = $handleMessage->stripTagsMessage($request->get('reply_msg'));

                if (!$chat || ! $chat instanceof Chat) {
                    $msg = $this->translator->trans('message_no_chat');
                    throw new \Exception(
                        sprintf(
                            'Could not find chat (id = %s). Error in the function %s on line %s',
                            $request->get('chat') ?? 'chat_id not found',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                if (! $this->isGranted('ROLE_USER') || ! $chat->getUser()->contains($this->getUser())) {
                    $msg = $this->translator->trans('message_no_access_chat');
                    throw new \Exception(
                        sprintf(
                            'User (id = %s) does not have access to view chat (id = %s). Error in the function %s on line %s',
                            $this->getUser() ? $this->getUser()->getId() : 'user_id not found',
                            $chat->getId(),
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                if(!$reply_msg) {
                    $msg = $this->translator->trans('message_no_body');
                    $dataMessage = $this->getResponseData(true, 400, $msg);
                    return new JsonResponse($dataMessage, $dataMessage['status']);
                }

                $new_message = $this->saveDataChat($this->getUser(), $reply_msg, $chat);
                $dataMessage = $this->getResponseData(false, 200, '');
                $dataMessage['template'] = $this->renderView('@App/messages/parts/message-template.html.twig', ['message' => $new_message]);

            } else {
                throw new \Exception(
                    sprintf(
                        'Failed to get Ajax request when replying to a message (chat_id = %s) for user (id = %s). Error in the function %s on line %s',
                        $request->get('chat') ?? 'chat_id not found',
                        $this->getUser() ? $this->getUser()->getId() : 'user_id is not found',
                        __METHOD__,
                        __LINE__
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $msg_error = $msg != '' ? $msg : $this->translator->trans('message_sent_error');
            $dataMessage = $this->getResponseData(true, 400, $msg_error);
        }

        return new JsonResponse($dataMessage, $dataMessage['status']);
    }

    /**
     * @param $error
     * @param $status
     * @param $message
     * @return array
     */
    private function getResponseData($error, $status, $message): array
    {
        return [
            'errors' => $error,
            'status' => $status,
            'msg' => $message
        ];
    }

    /**
     * Пометить сообщения как "прочитанные"
     *
     * @param $messages
     */
    private function isReadMessage($messages)
    {
        if (is_object($messages)) {
            $messages = $messages->toArray();
        }

        $no_read_messages = array_filter($messages, function ($message) {
            return ! $message->getIsRead() && $message->getSender() != $this->getUser();
        });

        if (count($no_read_messages)) {
            foreach ($no_read_messages as $message) {
                $message->setIsRead(true);
                $this->em->persist($message);
            }
            $this->em->flush();
        }
    }

    /**
     * Найти уже существующий чат
     *
     * @param $sender
     * @param $recipient
     * @param $group
     * @param $subject
     * @return array|bool
     */
    private function findChat($sender, $recipient, $group, $subject)
    {
        return $this->em
                ->getRepository(Chat::class)
                ->findChat($sender, $recipient, $group, $subject)
            ?? false
        ;
    }

    /**
     * Сохранить данные чата
     *
     * @param User $sender
     * @param $body
     * @param $chat
     * @param User|null $recipient
     * @param null $subject
     * @param null $group
     * @return Message
     */
    private function saveDataChat(User $sender, $body, $chat, User $recipient = null, $subject = null, $group = null)
    {
        if (!$chat || ! $chat instanceof Chat) {
            $chat = new Chat();
            $chat->addUser($sender);
            $chat->addUser($recipient);
            $chat->setSubject($subject);
            $chat->setGroup($group);
        } else {
            $chat->setUpdatedAt(new \DateTime());
            $this->em->persist($chat);
        }

        $new_message = new Message();
        $new_message->setSender($sender);
        $new_message->setBody($body);
        $new_message->setChat($chat);

        $this->em->persist($new_message);
        $this->em->flush();

        return $new_message;
    }
}
