<?php

namespace App\Almacenamiento;

use App\Entity\Nota;

/**
 * ADAPTADOR 2: archivo JSON.
 *
 * Sin base de datos. Útil para prototipos, demos o bajo volumen.
 * Maneja su propio autoincremental de id. Doctrine no aparece por ningún lado.
 */
final class AlmacenamientoJson implements AlmacenamientoNotas
{
    private readonly string $archivo;

    public function __construct(string $directorioDatos)
    {
        $this->archivo = rtrim($directorioDatos, '/').'/notas.json';
    }

    public function guardar(Nota $nota): void
    {
        $notas = $this->leerCrudo();

        if ($nota->getId() === null) {
            $nuevoId = $this->siguienteId($notas);
            $nota->setId($nuevoId);
        }

        $notas[$nota->getId()] = $this->aArray($nota);
        $this->escribirCrudo($notas);
    }

    public function buscarTodas(): array
    {
        $notas = array_map($this->desdeArray(...), $this->leerCrudo());
        usort($notas, static fn (Nota $a, Nota $b) => $b->getId() <=> $a->getId());

        return $notas;
    }

    public function buscarPorId(int $id): ?Nota
    {
        $notas = $this->leerCrudo();

        return isset($notas[$id]) ? $this->desdeArray($notas[$id]) : null;
    }

    public function eliminar(int $id): void
    {
        $notas = $this->leerCrudo();
        unset($notas[$id]);
        $this->escribirCrudo($notas);
    }

    /** @return array<int, array{id:int, titulo:string, contenido:string, categoria:string}> */
    private function leerCrudo(): array
    {
        if (!is_file($this->archivo)) {
            return [];
        }

        $contenido = file_get_contents($this->archivo);

        return $contenido === '' || $contenido === false ? [] : (json_decode($contenido, true) ?? []);
    }

    private function escribirCrudo(array $notas): void
    {
        $dir = \dirname($this->archivo);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($this->archivo, json_encode($notas, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE));
    }

    private function siguienteId(array $notas): int
    {
        return $notas === [] ? 1 : (max(array_keys($notas)) + 1);
    }

    private function aArray(Nota $nota): array
    {
        return [
            'id' => $nota->getId(),
            'titulo' => $nota->getTitulo(),
            'contenido' => $nota->getContenido(),
            'categoria' => $nota->getCategoria(),
        ];
    }

    private function desdeArray(array $datos): Nota
    {
        return (new Nota())
            ->setId($datos['id'])
            ->setTitulo($datos['titulo'])
            ->setContenido($datos['contenido'])
            ->setCategoria($datos['categoria']);
    }
}
