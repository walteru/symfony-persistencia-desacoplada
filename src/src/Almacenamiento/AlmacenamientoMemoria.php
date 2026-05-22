<?php

namespace App\Almacenamiento;

use App\Entity\Nota;

/**
 * ADAPTADOR 3: en memoria.
 *
 * Guarda en un array dentro del propio servicio. No persiste entre
 * peticiones HTTP (cada request crea una instancia nueva), así que en la
 * web "se reinicia" en cada recarga. Es ideal para tests rápidos: la suite
 * corre sin base de datos ni archivos.
 */
final class AlmacenamientoMemoria implements AlmacenamientoNotas
{
    /** @var array<int, Nota> */
    private array $notas = [];

    private int $proximoId = 1;

    public function guardar(Nota $nota): void
    {
        if ($nota->getId() === null) {
            $nota->setId($this->proximoId++);
        }

        $this->notas[$nota->getId()] = $nota;
    }

    public function buscarTodas(): array
    {
        $notas = array_values($this->notas);
        usort($notas, static fn (Nota $a, Nota $b) => $b->getId() <=> $a->getId());

        return $notas;
    }

    public function buscarPorId(int $id): ?Nota
    {
        return $this->notas[$id] ?? null;
    }

    public function eliminar(int $id): void
    {
        unset($this->notas[$id]);
    }
}
