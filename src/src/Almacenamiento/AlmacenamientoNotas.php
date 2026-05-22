<?php

namespace App\Almacenamiento;

use App\Entity\Nota;

/**
 * EL PUERTO.
 *
 * Define QUÉ se puede hacer con las notas, nunca CÓMO ni DÓNDE se guardan.
 * El controlador depende solo de esta interfaz. Cambiar el almacenamiento
 * es cambiar qué implementación se inyecta en config/services.yaml.
 */
interface AlmacenamientoNotas
{
    public function guardar(Nota $nota): void;

    /** @return Nota[] */
    public function buscarTodas(): array;

    public function buscarPorId(int $id): ?Nota;

    public function eliminar(int $id): void;
}
