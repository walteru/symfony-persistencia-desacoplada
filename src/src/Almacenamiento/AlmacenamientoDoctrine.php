<?php

namespace App\Almacenamiento;

use App\Entity\Nota;
use Doctrine\ORM\EntityManagerInterface;

/**
 * ADAPTADOR 1: MySQL vía Doctrine ORM.
 *
 * Es el único adaptador que conoce Doctrine. El resto de la aplicación
 * no importa nada de aquí: solo conoce la interfaz AlmacenamientoNotas.
 */
final class AlmacenamientoDoctrine implements AlmacenamientoNotas
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function guardar(Nota $nota): void
    {
        $this->em->persist($nota);
        $this->em->flush();
    }

    public function buscarTodas(): array
    {
        return $this->em->getRepository(Nota::class)->findBy([], ['id' => 'DESC']);
    }

    public function buscarPorId(int $id): ?Nota
    {
        return $this->em->getRepository(Nota::class)->find($id);
    }

    public function eliminar(int $id): void
    {
        $nota = $this->buscarPorId($id);
        if ($nota !== null) {
            $this->em->remove($nota);
            $this->em->flush();
        }
    }
}
