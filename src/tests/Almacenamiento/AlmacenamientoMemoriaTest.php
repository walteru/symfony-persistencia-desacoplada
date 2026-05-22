<?php

namespace App\Tests\Almacenamiento;

use App\Almacenamiento\AlmacenamientoMemoria;
use App\Almacenamiento\AlmacenamientoNotas;
use App\Entity\Nota;
use PHPUnit\Framework\TestCase;

/**
 * Tests del contrato AlmacenamientoNotas usando el adaptador en memoria.
 *
 * Acá se ve el beneficio del desacople: la suite corre sin base de datos,
 * sin Docker y sin tocar disco. Es un PHPUnit "puro" (extiende TestCase, no
 * KernelTestCase) y vuela en milisegundos.
 *
 * El día que quieras testear el adaptador Doctrine, escribís otra clase que
 * monte la base; la lógica de negocio que depende de la interfaz no cambia.
 */
final class AlmacenamientoMemoriaTest extends TestCase
{
    private AlmacenamientoNotas $almacenamiento;

    protected function setUp(): void
    {
        $this->almacenamiento = new AlmacenamientoMemoria();
    }

    public function testGuardarAsignaIdYPermiteRecuperar(): void
    {
        $nota = (new Nota())
            ->setTitulo('Puertos y adaptadores')
            ->setCategoria('Arquitectura')
            ->setContenido('El dominio no conoce la base de datos.');

        $this->almacenamiento->guardar($nota);

        self::assertNotNull($nota->getId(), 'El adaptador debe asignar un id al guardar.');

        $recuperada = $this->almacenamiento->buscarPorId($nota->getId());
        self::assertNotNull($recuperada);
        self::assertSame('Puertos y adaptadores', $recuperada->getTitulo());
        self::assertSame('Arquitectura', $recuperada->getCategoria());
    }

    public function testBuscarTodasDevuelveLasMasNuevasPrimero(): void
    {
        $this->almacenamiento->guardar((new Nota())->setTitulo('Primera'));
        $this->almacenamiento->guardar((new Nota())->setTitulo('Segunda'));

        $todas = $this->almacenamiento->buscarTodas();

        self::assertCount(2, $todas);
        self::assertSame('Segunda', $todas[0]->getTitulo());
        self::assertSame('Primera', $todas[1]->getTitulo());
    }

    public function testEditarUnaNotaNoCreaUnaNueva(): void
    {
        $nota = (new Nota())->setTitulo('Borrador');
        $this->almacenamiento->guardar($nota);

        $nota->setTitulo('Versión final');
        $this->almacenamiento->guardar($nota);

        self::assertCount(1, $this->almacenamiento->buscarTodas());
        self::assertSame('Versión final', $this->almacenamiento->buscarPorId($nota->getId())->getTitulo());
    }

    public function testEliminarQuitaLaNota(): void
    {
        $nota = (new Nota())->setTitulo('A eliminar');
        $this->almacenamiento->guardar($nota);
        $id = $nota->getId();

        $this->almacenamiento->eliminar($id);

        self::assertNull($this->almacenamiento->buscarPorId($id));
        self::assertCount(0, $this->almacenamiento->buscarTodas());
    }

    public function testBuscarPorIdInexistenteDevuelveNull(): void
    {
        self::assertNull($this->almacenamiento->buscarPorId(999));
    }
}
