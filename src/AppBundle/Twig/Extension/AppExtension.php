<?php

namespace AppBundle\Twig\Extension;

use AppBundle\Entity\Comments;
use AppBundle\Service\HandleMessage;
use Symfony\Component\Filesystem\Filesystem;

class AppExtension extends \Twig_Extension
{
    /**
     * @var HandleMessage
     */
    private $handleMessage;

    public function __construct(HandleMessage $handleMessage)
    {
        $this->handleMessage = $handleMessage;
    }

    public function getName()
    {
        return 'app';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'stripTagsMessage',
                [$this, 'stripTagsMessage'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'stripEmojiMessage',
                [$this, 'stripEmojiMessage']
            )
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'getParentsComment',
                [$this, 'getParentsComment']
            ),
            new \Twig_SimpleFunction(
                'newMessage',
                [$this, 'newMessage']
            ),
            new \Twig_SimpleFunction(
                'newCountMessage',
                [$this, 'newCountMessage']
            ),
            new \Twig_SimpleFunction(
                'file_exists',
                [$this, 'file_exists']
            ),
            new \Twig_SimpleFunction(
                'getCompanion',
                [$this, 'getCompanion']
            ),
            new \Twig_SimpleFunction(
                'newReports',
                [$this, 'newReports']
            ),
            new \Twig_SimpleFunction(
                'newCountReports',
                [$this, 'newCountReports']
            ),
        ];
    }

    /**
     * Получить цитируемые комментарии
     *
     * @param Comments $comment
     * @return string
     */
    public function getParentsComment(Comments $comment)
    {
        return $this->handleMessage->getParentsComment($comment);
    }

    /**
     * Удалить теги из сообщения
     *
     * @param $message
     * @return string
     */
    public function stripTagsMessage($message)
    {
        return $this->handleMessage->stripTagsMessage($message);
    }

    /**
     * Удалить emoji из сообщения
     *
     * @param $message
     * @return string
     */
    public function stripEmojiMessage($message)
    {
        return $this->handleMessage->stripEmojiMessage($message);
    }

    /**
     * Получить новые сообщения
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function newMessage()
    {
        return $this->handleMessage->getNewMessage();
    }

    /**
     * Получить количество новых сообщений
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function newCountMessage()
    {
        return $this->handleMessage->getCountNewMessage();
    }

    /**
     * Проверить наличие файла
     *
     * @param $path
     * @return bool
     */
    public function file_exists($path)
    {
        $fs = new Filesystem();
        return $fs->exists($path);
    }

    /**
     * Получить собеседника по переписке в чате
     *
     * @param $users
     * @param $current_user
     * @return array
     */
    public function getCompanion($users, $current_user)
    {
        if (is_object($users)) {
            $users = $users->toArray();
        }

        return array_filter($users, function ($user) use($current_user) {
            return $user !== $current_user;
        });
    }

    /**
     * Получить новые письма для администрации
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function newReports()
    {
        return $this->handleMessage->getNewReports();
    }

    /**
     * Получить количество новых писем для администрации
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function newCountReports()
    {
        return $this->handleMessage->getCountNewReports();
    }
}
