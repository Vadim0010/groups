<?php

namespace AppBundle\Service;

use AppBundle\Entity\Comments;
use AppBundle\Entity\Message;
use AppBundle\Entity\Reports;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class HandleMessage
{
    private $countNewMessage = 5;

    private $allowedTags = [
        '<blockquote>',
        '<br>',
        '<b>',
        '<i>'
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getAllowedTags(): string
    {
        return implode('', $this->allowedTags);
    }

    /**
     * Удалить теги из сообщения
     *
     * @param $message
     * @return string
     */
    public function stripTagsMessage($message): string
    {
        return strip_tags(nl2br($message), $this->getAllowedTags());
    }

    /**
     * Удалить emoji из сообщения
     *
     * @param $message
     * @return string
     */
    public function stripEmojiMessage($message): string
    {
        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $message = preg_replace($regexEmoticons, '', $message);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $message = preg_replace($regexSymbols, '', $message);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $message = preg_replace($regexTransport, '', $message);

        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $message = preg_replace($regexMisc, '', $message);

        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $message = preg_replace($regexDingbats, '', $message);

        return $message;
    }

    /**
     * Получить все родительские комментарии
     *
     * @param Comments $comment
     * @return string
     */
    public function getParentsComment(Comments $comment)
    {
        $parents = [];
        $template = '';
        $parents = $this->recursiveParents($parents, $comment);

        if (count($parents)) {
            $parents = array_reverse($parents);

            foreach ($parents as $parent) {
                $template = $this->getParentsCommentTemplate($parent, $template);
            }
        }


        return $template;
    }

    /**
     * Получить новые сообщения
     *
     * @return mixed
     */
    public function getNewMessage()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $storage = $this->container->get('security.token_storage');

        return $em->getRepository(Message::class)->getNewMessage($storage->getToken()->getUser(), $this->countNewMessage);
    }

    /**
     * Получить количество новых сообщений
     *
     * @return mixed
     */
    public function getCountNewMessage()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $storage = $this->container->get('security.token_storage');

        return $em->getRepository(Message::class)->getCountNewMessage($storage->getToken()->getUser());
    }

    /**
     * Получить новые письма для администрации
     *
     * @return mixed
     */
    public function getNewReports()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        return $em->getRepository(Reports::class)->getNewReports();
    }

    /**
     * Получить количество новых писем для администрации
     *
     * @return mixed
     */
    public function getCountNewReports()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        return $em->getRepository(Reports::class)->getCountNewReports();
    }

    /**
     * Получить массив всех родительсикх комментариев
     *
     * @param $parents
     * @param Comments $comment
     * @return mixed
     */
    private function recursiveParents(&$parents, Comments $comment)
    {
        if (!$comment->getParent()) {
            return $parents;
        }

        array_push($parents, $comment->getParent());
        return $this->recursiveParents($parents, $comment->getParent());
    }

    /**
     * Получить верстку цитируемых комментариев
     *
     * @param $comment
     * @param $parent_message
     * @return string
     */
    private function getParentsCommentTemplate($comment, $parent_message): string
    {
        return sprintf(
            '<blockquote>%s%s</blockquote>',
            $parent_message,
            $comment->getDeletedAt() ? $this->container->get('translator')->trans('comment_text_message_delete') : $comment->getMessage()
        );
    }
}