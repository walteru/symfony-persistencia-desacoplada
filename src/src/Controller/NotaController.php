<?php

namespace App\Controller;

use App\Almacenamiento\AlmacenamientoNotas;
use App\Entity\Nota;
use App\Form\NotaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * El controlador NO sabe dónde se guardan las notas.
 *
 * Solo depende de la interfaz AlmacenamientoNotas. Symfony inyecta el
 * adaptador concreto según lo configurado en config/services.yaml.
 * Cambiar de MySQL a JSON o memoria no toca ni una línea de este archivo.
 */
final class NotaController extends AbstractController
{
    public function __construct(private readonly AlmacenamientoNotas $almacenamiento)
    {
    }

    #[Route('/', name: 'nota_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('nota/index.html.twig', [
            'notas' => $this->almacenamiento->buscarTodas(),
            'backend' => $this->almacenamiento::class,
        ]);
    }

    #[Route('/nueva', name: 'nota_nueva', methods: ['GET', 'POST'])]
    public function nueva(Request $request): Response
    {
        $nota = new Nota();
        $form = $this->createForm(NotaType::class, $nota);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->almacenamiento->guardar($nota);

            return $this->redirectToRoute('nota_index');
        }

        return $this->render('nota/form.html.twig', [
            'form' => $form,
            'titulo_pagina' => 'Nueva nota',
        ]);
    }

    #[Route('/{id}/editar', name: 'nota_editar', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function editar(int $id, Request $request): Response
    {
        $nota = $this->almacenamiento->buscarPorId($id);
        if ($nota === null) {
            throw $this->createNotFoundException('Nota inexistente');
        }

        $form = $this->createForm(NotaType::class, $nota);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->almacenamiento->guardar($nota);

            return $this->redirectToRoute('nota_index');
        }

        return $this->render('nota/form.html.twig', [
            'form' => $form,
            'titulo_pagina' => 'Editar nota',
        ]);
    }

    #[Route('/{id}/eliminar', name: 'nota_eliminar', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function eliminar(int $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('eliminar'.$id, (string) $request->request->get('_token'))) {
            $this->almacenamiento->eliminar($id);
        }

        return $this->redirectToRoute('nota_index');
    }
}
