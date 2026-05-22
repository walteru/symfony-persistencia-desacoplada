<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objeto de dominio "Nota".
 *
 * Lleva atributos de Doctrine para que el adaptador MySQL pueda mapearla,
 * pero esos atributos son metadatos inertes: los adaptadores JSON y en
 * memoria los ignoran por completo y trabajan con sus getters/setters.
 */
#[ORM\Entity]
#[ORM\Table(name: 'notas')]
class Nota
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $titulo = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $contenido = '';

    #[ORM\Column(length: 100)]
    private string $categoria = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getContenido(): string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): self
    {
        $this->contenido = $contenido;

        return $this;
    }

    public function getCategoria(): string
    {
        return $this->categoria;
    }

    public function setCategoria(string $categoria): self
    {
        $this->categoria = $categoria;

        return $this;
    }
}
