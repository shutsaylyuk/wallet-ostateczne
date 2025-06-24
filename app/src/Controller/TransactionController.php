<?php

/*
 * Transaction Controller
 */
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\Type\TransactionType;
use App\Form\Type\TransactionFilterType;
use App\Security\Voter\TransactionVoter;
use App\Service\TransactionServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller responsible for managing transactions.
 */
class TransactionController extends AbstractController
{
    /**
     * TransactionController constructor.
     *
     * @param TransactionServiceInterface $transactionService Transaction service
     */
    public function __construct(private readonly TransactionServiceInterface $transactionService)
    {
    }

    /**
     * Displays the transaction list with optional filters and balance summary.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/transaction/', name: 'transaction_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $filtersForm = $this->createForm(TransactionFilterType::class, null, [
            'method' => 'GET',
            'user' => $this->getUser(),
        ]);
        $filtersForm->handleRequest($request);

        $filters = $filtersForm->isSubmitted() && $filtersForm->isValid()
            ? $filtersForm->getData()
            : [];

        $page = $request->query->getInt('page', 1);
        $qb = $this->transactionService->getFilteredQueryBuilder($filters);
        $pagination = $this->transactionService->paginate($qb, $page);

        $summaryFilters = array_filter([
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ]);

        $balanceData = $this->transactionService->calculateSummary($filters);
        $shouldShowSummary = !empty($filters['date_from']) || !empty($filters['date_to']);

        return $this->render('transaction/index.html.twig', [
            'pagination' => $pagination,
            'form' => $filtersForm->createView(),
            'balanceData' => $balanceData,
            'shouldShowSummary' => $shouldShowSummary,
        ]);
    }

    /**
     * Creates a new transaction.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/transaction/create', name: 'transaction_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $transaction = new Transaction();
        $transaction->setCreatedAt(new \DateTimeImmutable());
        $transaction->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(TransactionType::class, $transaction, [
            'user' => $this->getUser(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->transactionService->save($transaction);
                $this->addFlash('success', 'transaction.created_successfully');

                return $this->redirectToRoute('transaction_index');
            } catch (\LogicException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('transaction/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edits an existing transaction.
     *
     * @param Request     $request     HTTP request
     * @param Transaction $transaction Transaction entity
     *
     * @return Response HTTP response
     */
    #[Route('/transaction/{id}/edit', name: 'transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Transaction $transaction): Response
    {
        $this->denyAccessUnlessGranted(TransactionVoter::EDIT, $transaction);

        $transaction->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(TransactionType::class, $transaction, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->transactionService->save($transaction);
                $this->addFlash('success', 'transaction.updated_successfully');

                return $this->redirectToRoute('transaction_index');
            } catch (\LogicException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('transaction/edit.html.twig', [
            'form' => $form->createView(),
            'transaction' => $transaction,
        ]);
    }

    /**
     * Deletes a transaction after confirmation.
     *
     * @param Request     $request     HTTP request
     * @param Transaction $transaction Transaction entity
     *
     * @return Response HTTP response
     */
    #[Route('/transaction/{id}/delete', name: 'transaction_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Transaction $transaction): Response
    {
        $this->denyAccessUnlessGranted(TransactionVoter::DELETE, $transaction);

        $form = $this->createForm(FormType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('transaction_delete', ['id' => $transaction->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->transactionService->delete($transaction);
            $this->addFlash('success', 'transaction.deleted_successfully');

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render('transaction/delete.html.twig', [
            'form' => $form->createView(),
            'transaction' => $transaction,
        ]);
    }

    /**
     * Shows the details of a transaction.
     *
     * @param Transaction $transaction Transaction entity
     *
     * @return Response HTTP response
     */
    #[Route('/transaction/{id}', name: 'transaction_show', methods: ['GET'])]
    public function show(Transaction $transaction): Response
    {
        $this->denyAccessUnlessGranted(TransactionVoter::VIEW, $transaction);

        return $this->render('transaction/show.html.twig', [
            'transaction' => $transaction,
        ]);
    }
}
