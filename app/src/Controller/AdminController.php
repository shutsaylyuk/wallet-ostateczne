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
use App\Service\AdminService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @param TranslatorInterface $translator   Translator service
     * @param AdminService        $adminService Service handling admin operations
     */
    public function __construct(private readonly TranslatorInterface $translator, private readonly AdminService $adminService)
    {
    }

    /**
     * Displays and processes account settings (email and password change) for admin.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[\Symfony\Component\Routing\Attribute\Route('/admin/account', name: 'admin_account', methods: ['GET', 'POST'])]
    public function accountSettings(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $emailForm = $this->createForm(AdminEmailType::class, $user);
        $emailForm->handleRequest($request);
        if ($emailForm->isSubmitted() && $emailForm->isValid() && $request->request->has('email_submit')) {
            $this->adminService->updateEmail($user);
            $this->addFlash('success', $this->translator->trans('admin.email_updated_successfully'));

            return $this->redirectToRoute('admin_account');
        }

        $passwordForm = $this->createForm(AdminPasswordType::class);
        $passwordForm->handleRequest($request);
        if ($passwordForm->isSubmitted() && $passwordForm->isValid() && $request->request->has('password_submit')) {
            $plainPassword = $passwordForm->get('plainPassword')->getData();
            $this->adminService->updatePassword($user, $plainPassword);

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
