<?php

namespace App\Controller;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ProductType;


#[Route('/products', name: 'app_products_')]
final class ProductController extends AbstractController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    #[Route('/list', name: 'list', methods: ['GET'])]
    public function index(): Response
    {
        $products = $this->productRepository->findAllByAsc();

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/add', name:'add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();

        $form = $this->createForm(
            ProductType::class, 
            $product
        );

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_products_show', ['id' => $product->getId()]);
        }

        return $this->render('product/add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function supprimer(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $token)) {
            $em->remove($product);
            $em->flush();

            $this->addFlash('success', 'Produit supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide, suppression annulée.');
        }

        return $this->redirectToRoute('app_products_list');
    }
}