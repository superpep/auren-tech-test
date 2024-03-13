<?php

namespace App\Controller;

use App\Entity\Country;
use App\Form\CountryType;
use App\Repository\CountryRepository;
use App\Service\CountryImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/country')]
class CountryController extends AbstractController
{
    #[Route('/', name: 'country_index', methods: ['GET'])]
    public function index(CountryRepository $countryRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('perPage', 10);

        $countries = $countryRepository->findAll();

        // Lógica de paginación
        $offset = ($page - 1) * $perPage;
        $totalPages = ceil(count($countries) / $perPage);
        $pagination = [
            'prevUrl' => $page > 1 ? $this->generateUrl('country_index', ['page' => $page - 1]) : null,
            'nextUrl' => $page < $totalPages ? $this->generateUrl('country_index', ['page' => $page + 1]) : null,
            'pages' => [],
        ];

        for ($i = 1; $i <= $totalPages; $i++) {
            $pagination['pages'][] = [
                'number' => $i,
                'url' => $this->generateUrl('country_index', ['page' => $i]),
                'isCurrent' => $i === $page,
            ];
        }

        // Pasar los datos a la plantilla Twig
        return $this->render('country/index.html.twig', [
            'countries' => array_slice($countries, $offset, $perPage),
            'pagination' => $pagination,
            'perPage' => $perPage,
        ]);
    }

    #[Route('/update-api', name: 'country_update_api', methods: ['GET'])]
    public function update_api(CountryRepository $countryRepository, CountryImportService $countryImportService): Response
    {
        $countryImportService->importCountries();
        return $this->redirectToRoute('app_homepage', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/update-api/{id}', name: 'country_update_one_api', methods: ['GET'])]
    public function update_one_api(CountryRepository $countryRepository, CountryImportService $countryImportService, Country $country): Response
    {
        $countryImportService->importCountry($country);
        return $this->render('country/show.html.twig', [
            'country' => $country,
        ]);
    }

    #[Route('/new', name: 'country_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $country = new Country();
        $form = $this->createForm(CountryType::class, $country);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($country);
            $entityManager->flush();

            return $this->redirectToRoute('country_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('country/new.html.twig', [
            'country' => $country,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'country_show', methods: ['GET'])]
    public function show(Country $country): Response
    {
        return $this->render('country/show.html.twig', [
            'country' => $country,
        ]);
    }

    #[Route('/{id}/edit', name: 'country_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Country $country, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CountryType::class, $country);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('country_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('country/edit.html.twig', [
            'country' => $country,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'country_delete', methods: ['POST'])]
    public function delete(Request $request, Country $country, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$country->getId(), $request->request->get('_token'))) {
            $entityManager->remove($country);
            $entityManager->flush();
        }

        return $this->redirectToRoute('country_index', [], Response::HTTP_SEE_OTHER);
    }
}
