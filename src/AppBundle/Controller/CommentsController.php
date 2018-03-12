<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Groups;
use AppBundle\Entity\Comments;
use AppBundle\HelperClasses\EntityClass;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Translation\TranslatorInterface;

class CommentsController extends Controller
{
    use EntityClass;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @Route("/add_comment",
     *     name="ajax_add_comment",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function addComment(Request $request)
    {
        $msg = '';
        $_token = $request->get('_token');

        try {
            if (
                $request->isXmlHttpRequest() &&
                $this->isCsrfTokenValid('authenticate', $_token) &&
                $this->isGranted('ROLE_USER')
            ) {
                $pattern_quote = '/\[blockquote\](.*)\[\/blockquote\](.*)/s';
                list($message, $group, $parent_comment) = $this->getRequestData($request);
                $this->em->getFilters()->disable('softdeleteable');

                if (!$message) {
                    $msg = $this->translator->trans('comment_not_empty');
                    $dataComment = $this->getResponseData(true, 400, $msg);
                    return new JsonResponse($dataComment, $dataComment['status']);
                }

                if (!$group) {
                    $msg = $this->translator->trans('comment_not_group');
                    throw new \Exception(
                        sprintf(
                            'Could not find group (id = %s). Error in the function %s on line %s',
                            $request->get('group', 'group_id not found'),
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                $new_comment = new Comments();

                if ($parent_comment && preg_match($pattern_quote, $message, $answer_message)) {
                    $data = $this->accessor->getValue($answer_message, '[2]');

                    if ( ! is_null($data) ) {
                        $message = trim($data);
                    }

                    $new_comment->setParent($parent_comment);
                }

                $new_comment->setGroup($group);
                $new_comment->setUser($this->getUser());
                $new_comment->setMessage($message);
                $this->em->persist($new_comment);
                $this->em->flush();

                $msg = $this->translator->trans('comment_add_msg_success');
                $dataComment = $this->getResponseData(false, 200, $msg);
                $dataComment['comment_id'] = $new_comment->getId();
                $dataComment['template'] = $this->renderView(
                    '@App/Groups/parts/comment-template.html.twig',
                    ['comment' => $new_comment]
                );
            }else{
                throw new \Exception(
                    sprintf(
                        'Failed to get ajax request when adding a comment for the user (id = %s) and a token (%s). Error in the function %s on line %s',
                        $this->getUser() ? $this->getUser()->getId() : 'user_id not found',
                        $_token,
                        __METHOD__,
                        __LINE__
                    )
                );
            }


        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $msg_error = $msg != '' ? $msg : $this->translator->trans('comment_add_msg_error');
            $dataComment = $this->getResponseData(true, 400, $msg_error);
        }

        return new JsonResponse($dataComment, $dataComment['status']);
    }
    /**
     * @Route("/delete_comment",
     *     name="ajax_delete_comment",
     *     methods={"DELETE"}
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $msg = '';

        try {
            if (
                $request->isXmlHttpRequest() &&
                $this->isCsrfTokenValid('authenticate', $request->get('_token')) &&
                $this->isGranted('ROLE_ADMIN')
            ) {
                $comment = $this->getEntity($this->em, $request->get('comment'), Comments::class);

                if (! $comment) {
                    $msg = $this->translator->trans('comment_not_comment');
                    throw new \Exception(
                        sprintf(
                            'Could not find comment (id = %s). Error in the function %s on line %s',
                            $request->get('comment', 'comment_id not found'),
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                $this->em->remove($comment);
                $this->em->flush();
                $msg = $this->translator->trans('comment_delete_msg_success');
                $dataComment = $this->getResponseData(false, 200, $msg);

            }else{
                throw new \Exception(
                    sprintf(
                        'Failed to get ajax request when remove a comment. Error in the function %s on line %s',
                        __METHOD__,
                        __LINE__
                    )
                );
            }


        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $msg_error = $msg != '' ?? $this->translator->trans('comment_delete_msg_error');
            $dataComment = $this->getResponseData(true,400, $msg_error);
        }

        return new JsonResponse($dataComment, $dataComment['status']);
    }

    /**
     * Получить данные ajax-запроса при добавлении нового комментария
     *
     * @param $request
     * @return array
     */
    private function getRequestData($request)
    {
        return [
            trim($request->get('comment')),
            $this->getEntity($this->em, $request->get('group'), Groups::class),
            $this->getEntity($this->em, $request->get('answer_comment'), Comments::class)
        ];
    }

    /**
     * @param $error
     * @param $status
     * @param $message
     * @return array
     */
    private function getResponseData($error, $status, $message)
    {
        return [
            'errors' => $error,
            'status' => $status,
            'msg' => $message
        ];
    }
}
