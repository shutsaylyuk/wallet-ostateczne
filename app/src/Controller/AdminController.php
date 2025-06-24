<?php

/*
 * Admin Controller
 */
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\AdminEmailType;
use App\Form\Type\AdminPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller for administrative actions.
 */
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator Translator service
     */
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Displays and processes account settings (email and password change) for admin.
     *
     * @param Request                     $request        HTTP request
     * @param EntityManagerInterface      $em             Doctrine entity manager
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     *
     * @return Response HTTP response
     */
    #[\Symfony\Component\Routing\Attribute\Route('/admin/account', name: 'admin_account', methods: ['GET', 'POST'])]
    public function accountSettings(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $emailForm = $this->createForm(AdminEmailType::class, $user);
        $emailForm->handleRequest($request);
        if ($emailForm->isSubmitted() && $emailForm->isValid() && $request->request->has('email_submit')) {
            $em->flush();
            $this->addFlash('success', $this->translator->trans('admin.email_updated_successfully'));

            return $this->redirectToRoute('admin_account');
        }

        $passwordForm = $this->createForm(AdminPasswordType::class);
        $passwordForm->handleRequest($request);
        if ($passwordForm->isSubmitted() && $passwordForm->isValid() && $request->request->has('password_submit')) {
            $plainPassword = $passwordForm->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $em->flush();

            $this->addFlash('success', $this->translator->trans('admin.password_updated_successfully'));

            return $this->redirectToRoute('admin_account');
        }

        return $this->render('admin/account.html.twig', [
            'emailForm' => $emailForm->createView(),
            'passwordForm' => $passwordForm->createView(),
        ]);
    }

    /**
     * Displays paginated list of all users for administrator.
     *
     * @param Request            $request        HTTP request
     * @param UserRepository     $userRepository User repository
     * @param PaginatorInterface $paginator      Paginator service
     *
     * @return Response HTTP response
     */
    #[\Symfony\Component\Routing\Attribute\Route('/admin/users', name: 'admin_user_index', methods: ['GET'])]
    public function listUsers(Request $request, UserRepository $userRepository, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $userRepository->createQueryBuilder('u');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10,
            [
                'defaultSortFieldName' => 'u.id',
                'defaultSortDirection' => 'asc',
            ]
        );

        return $this->render('admin/userlist.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
