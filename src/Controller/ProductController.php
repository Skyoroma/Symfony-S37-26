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

    #[Route('/add', name:'add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $poduct = new Product();

        $form = $this->createForm(
            ProductType::class, 
            $poduct
        );

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($poduct);
            $entityManager->flush();

            return $this->redirectToRoute('app_products_show', ['id' => $poduct->getId()]);
        }

        return $this->render('product/add.html.twig', [
            'form' => $form,
        ]);
    }
}