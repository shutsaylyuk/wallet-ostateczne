<?php

declare(strict_types=1);

/*
 * Dashboard Controller
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller responsible for user dashboard.
 */
class DashboardController extends AbstractController
{
    /**
     * Renders the main dashboard page for authenticated users.
     *
     * @return Response HTTP response
     */
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('dashboard/index.html.twig');
    }

    /**
     * Redirects root URL to the dashboard.
     *
     * @return Response HTTP response
     */
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->redirectToRoute('app_dashboard');
    }
}
