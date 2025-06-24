<?php

declare(strict_types=1);
/**
 * Category Controller.
 */

namespace App\Controller;

use App\Entity\Category;
use App\Form\Type\CategoryType;
use App\Service\CategoryServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TransactionRepository;

/**
 * Controller responsible for managing categories.
 */
class CategoryController extends AbstractController
{
    /**
     * CategoryController constructor.
     *
     * @param CategoryServiceInterface $categoryService Category service
     */
    public function __construct(private readonly CategoryServiceInterface $categoryService)
    {
    }

    /**
     * Displays a paginated list of categories.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/category/index', name: 'category_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $pagination = $this->categoryService->getPaginatedList(
            $request->query->getInt('page', 1)
        );

        return $this->render('category/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Creates a new category.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/category/create', name: 'category_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $category = new Category();
        $category->setCreatedAt(new \DateTimeImmutable());
        $category->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryService->save($category);
            $this->addFlash('success', 'category.created_successfully');

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edits an existing category.
     *
     * @param Request  $request  HTTP request
     * @param Category $category Category entity
     *
     * @return Response HTTP response
     */
    #[Route('/category/{id}/edit', name: 'category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category): Response
    {
        $category->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryService->save($category);
            $this->addFlash('success', 'category.updated_successfully');

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/form.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * Deletes a category after confirmation.
     *
     * @param Request               $request               HTTP request
     * @param Category              $category              Category entity
     * @param TransactionRepository $transactionRepository Transaction repository
     *
     * @return Response HTTP response
     */
    #[Route('/category/{id}/delete', name: 'category_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Category $category, TransactionRepository $transactionRepository): Response
    {
        $form = $this->createForm(FormType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('category_delete', ['id' => $category->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transactions = $transactionRepository->findBy(['category' => $category]);

            if (!empty($transactions)) {
                $this->addFlash('danger', 'category.delete_failed_due_to_transactions');

                return $this->redirectToRoute('category_index');
            }

            $this->categoryService->delete($category);
            $this->addFlash('success', 'category.deleted_successfully');

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/delete.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays details of a category.
     *
     * @param Category $category Category entity
     *
     * @return Response HTTP response
     */
    #[Route('/category/{id}', name: 'category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }
}
