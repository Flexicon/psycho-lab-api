<?php

namespace App\Controller;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    public function __construct()
    {
    }

    /**
     * @Route(
     *     name="auth_user",
     *     path="/auth",
     * )
     * @Method("POST")
     */
    public function authAction(Request $request)
    {
        $repository = $this->getRepository();
        $this->prepareJsonBody($request);

        /** @var User $user */
        $user = $repository->findOneBy([
            'login' => $request->request->get('login'),
            'pass'  => $request->request->get('pass'),
        ]);

        return $this->json([
            'status' => true,
            'id'     => $user->getId(),
        ]);
    }

    /**
     * @Route(
     *     name="get_users",
     *     path="/users",
     * )
     * @Method("GET")
     */
    public function listAction()
    {
        $repository = $this->getRepository();
        /** @var User[] $users */
        $users     = $repository->findBy([], ['points' => 'desc', 'played' => 'asc']);
        $usersList = [];

        foreach ($users as $user) {
            $userJson['login']  = $user->getLogin();
            $userJson['played'] = $user->getPlayed();
            $userJson['points'] = $user->getPoints();

            $usersList[] = $userJson;
        }

        return $this->json([
            'status' => true,
            'users'  => $usersList,
        ]);
    }

    /**
     * @Route(
     *     name="create_user",
     *     path="/users",
     * )
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $dm = $this->getDocumentManager();
        $this->prepareJsonBody($request);

        $user = new User();
        $user->setLogin($request->request->get('login'));
        $user->setPass($request->request->get('pass'));

        try {
            $dm->persist($user);
            $dm->flush();
        } catch (\MongoDuplicateKeyException $exception) {
            return $this->json([
                'status' => false,
                'err'    => 'User with this login already exists',
            ]);
        }

        return $this->json([
            'status' => true,
        ]);
    }

    /**
     * @Route(
     *     name="get_user",
     *     path="/users/{id}",
     *     requirements={"id"="[a-zA-Z0-9_]*"}
     * )
     * @Method("GET")
     */
    public function getAction($id)
    {
        $repository = $this->getRepository();

        $user = $repository->find($id);

        if (!$user) {
            return $this->json([
                'status' => false,
                'err'    => 'User not found',
            ]);
        }

        return $this->json([
            'status' => true,
            'user'   => [
                'login'  => $user->getLogin(),
                'played' => $user->getPlayed(),
                'points' => $user->getPoints(),
            ],
        ]);
    }

    /**
     * @Route(
     *     name="delete_user",
     *     path="/users/{id}",
     *     requirements={"id"="[a-zA-Z0-9_]*"}
     * )
     * @Method("DELETE")
     */
    public function deleteAction($id)
    {
        $dm         = $this->getDocumentManager();
        $repository = $this->getRepository();

        $user = $repository->find($id);

        if (!$user) {
            return $this->json([
                'status' => false,
                'err'    => 'User not found',
            ]);
        }

        try {
            $dm->remove($user);
            $dm->flush();
        } catch (\Exception $exception) {
            return $this->json([
                'status' => false,
                'err'    => 'Failed to remove user',
            ]);
        }

        return $this->json([
            'status' => true,
        ]);
    }

    /**
     * @Route(
     *     name="patch_user",
     *     path="/users/{id}",
     *     requirements={"id"="[a-zA-Z0-9_]*"}
     * )
     * @Method("PATCH")
     *
     * @param Request $request
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function patchAction(Request $request, $id)
    {
        $dm         = $this->getDocumentManager();
        $repository = $this->getRepository();
        $this->prepareJsonBody($request);

        /** @var User $user */
        $user = $repository->find($id);

        if (!$user) {
            return $this->json([
                'status' => false,
                'err'    => 'User not found',
            ]);
        }

        try {
            $user->incrementPlayed();
            $user->addPoints((int)$request->request->get('points'));

            $dm->persist($user);
            $dm->flush();
        } catch (\Exception $exception) {
            return $this->json([
                'status' => false,
                'err'    => 'Failed to update user data',
            ]);
        }

        return $this->json([
            'status' => true,
            'played' => $user->getPlayed(),
            'points' => $user->getPoints(),
        ]);
    }

    /**
     * Returns Doctrine MongoDB Document Manager
     *
     * @return DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->get('doctrine_mongodb')->getManager();
    }

    /**
     * Returns User MongoDB Document Repository
     *
     * @return DocumentRepository
     */
    protected function getRepository()
    {
        return $this->get('doctrine_mongodb')->getManager()->getRepository('App:User');
    }

    /**
     * Prepares JSON body as request parameters
     *
     * @param Request $request
     */
    protected function prepareJsonBody(Request $request)
    {
        $json = json_decode($request->getContent(), true);
        $request->request->replace(is_array($json) ? $json : []);
    }
}
