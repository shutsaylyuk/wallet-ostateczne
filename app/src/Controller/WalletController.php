<?php

/*
 * Wallet Controller
 */
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Wallet;
use App\Form\Type\WalletType;
use App\Security\Voter\WalletVoter;
use App\Service\WalletServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Repository\TransactionRepository;

/**
 * Controller responsible for wallet management.
 */
#[IsGranted('ROLE_USER')]
class WalletController extends AbstractController
{
    /**
     * WalletController constructor.
     *
     * @param WalletServiceInterface $walletService Wallet service
     * @param TranslatorInterface    $translator    Translator
     */
    public function __construct(private readonly WalletServiceInterface $walletService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Displays paginated list of wallets for the current user.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/wallet/index', name: 'wallet_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $sortField = $request->query->get('sort', null);
        $sortDirection = $request->query->get('direction', 'desc');

        $pagination = $this->walletService->getSortedPaginatedList($user, $page, $sortField, $sortDirection);
        $totalBalance = array_reduce($pagination->getItems(), fn ($sum, Wallet $w) => $sum + (float) $w->getBalance(), 0);

        return $this->render('wallet/index.html.twig', [
            'pagination' => $pagination,
            'totalBalance' => $totalBalance,
            'currentSort' => $sortField,
            'currentDirection' => $sortDirection,
        ]);
    }

    /**
     * Creates a new wallet.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/wallet/create', name: 'wallet_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $wallet = new Wallet();
        $wallet->setUser($this->getUser());
        $wallet->setCreatedAt(new \DateTimeImmutable());
        $wallet->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(WalletType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletService->save($wallet);
            $this->addFlash('success', 'wallet.created_successfully');

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render('wallet/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays wallet details.
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return Response HTTP response
     */
    #[Route('/wallet/{id}', name: 'wallet_show', methods: ['GET'])]
    public function show(Wallet $wallet): Response
    {
        $this->denyAccessUnlessGranted(WalletVoter::VIEW, $wallet);

        return $this->render('wallet/show.html.twig', [
            'wallet' => $wallet,
        ]);
    }

    /**
     * Edits an existing wallet.
     *
     * @param Request $request HTTP request
     * @param Wallet  $wallet  Wallet entity
     *
     * @return Response HTTP response
     */
    #[Route('/wallet/{id}/edit', name: 'wallet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Wallet $wallet): Response
    {
        $this->denyAccessUnlessGranted(WalletVoter::EDIT, $wallet);

        $wallet->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(WalletType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletService->save($wallet);
            $this->addFlash('success', 'wallet.updated_successfully');

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render('wallet/edit.html.twig', [
            'form' => $form->createView(),
            'wallet' => $wallet,
        ]);
    }

    /**
     * Deletes a wallet after CSRF check and transaction verification.
     *
     * @param Request               $request               HTTP request
     * @param Wallet                $wallet                Wallet entity
     * @param TransactionRepository $transactionRepository Transaction repository
     *
     * @return Response HTTP response
     */
    #[Route('/wallet/{id}/delete', name: 'wallet_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Wallet $wallet, TransactionRepository $transactionRepository): Response
    {
        $this->denyAccessUnlessGranted(WalletVoter::DELETE, $wallet);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('delete'.$wallet->getId(), $request->request->get('_token'))) {
                $this->addFlash('danger', 'wallet.invalid_token');

                return $this->redirectToRoute('wallet_index');
            }

            // 🔁 Sprawdź, czy są powiązane transakcje (bez relacji obustronnej)
            $transactions = $transactionRepository->findBy(['wallet' => $wallet]);

            if ([] !== $transactions) {
                $this->addFlash('danger', 'wallet.delete_failed_due_to_transactions');

                return $this->redirectToRoute('wallet_index');
            }

            $this->walletService->delete($wallet);
            $this->addFlash('success', 'wallet.deleted_successfully');

            return $this->redirectToRoute('wallet_index');
        }

        $form = $this->createForm(FormType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('wallet_delete', ['id' => $wallet->getId()]),
        ]);

        return $this->render('wallet/delete.html.twig', [
            'wallet' => $wallet,
            'form' => $form->createView(),
        ]);
    }
}
