# Symfony — Persistencia desacoplada (puertos y adaptadores)

Demo mínimo que muestra cómo lograr que tu aplicación **no dependa de dónde guardás los datos**. Es un CRUD de notas (3 campos) donde el almacenamiento es **transparente**: el controlador habla con una interfaz, y cambiás dónde se guarda editando **una sola línea** de `config/services.yaml`.

Acompaña al artículo: [Arquitecturas Limpias: Cómo Desacoplar la Base de Datos de tu Aplicación](https://sincrodev.com/blog/arquitecturas-limpias-persistencia-desacoplada).

## La idea en un diagrama

```
        [ NotaController ]          ← no sabe dónde se guarda
               │
               ▼
      AlmacenamientoNotas           ← el PUERTO (interfaz)
         ▲     ▲     ▲
         │     │     │
   Doctrine  JSON  Memoria          ← los ADAPTADORES (intercambiables)
   (MySQL)  (archivo) (RAM)
```

- **Puerto**: `src/Almacenamiento/AlmacenamientoNotas.php` — el contrato (`guardar`, `buscarTodas`, `buscarPorId`, `eliminar`).
- **Adaptadores**: tres implementaciones del mismo contrato.
  - `AlmacenamientoDoctrine` → MySQL vía Doctrine ORM.
  - `AlmacenamientoJson` → un archivo `var/notas.json`, sin base de datos.
  - `AlmacenamientoMemoria` → un array en memoria (ideal para tests; se reinicia en cada request).
- **El controlador** (`src/Controller/NotaController.php`) depende solo de la interfaz. No importa nada de Doctrine.

## El interruptor

Toda la decisión de persistencia vive en `config/services.yaml`:

```yaml
# Cambiá esta única línea para cambiar dónde se guardan las notas:
App\Almacenamiento\AlmacenamientoNotas: '@App\Almacenamiento\AlmacenamientoDoctrine'
#                                          '@App\Almacenamiento\AlmacenamientoJson'
#                                          '@App\Almacenamiento\AlmacenamientoMemoria'
```

Reiniciás la cache y toda la app cambió de mecanismo de guardado, sin tocar el controlador, el formulario ni las plantillas.

## Stack

- PHP 8.3 + Symfony 7.2 (Doctrine ORM, Twig, Form, Validator)
- MySQL 8
- Docker + Docker Compose (entorno autocontenido)

## Cómo levantarlo

Requiere Docker y Docker Compose. No necesitás PHP ni Composer en tu máquina.

```bash
# 1. Clonar
git clone git@github.com:walteru/symfony-persistencia-desacoplada.git
cd symfony-persistencia-desacoplada

# 2. Construir y levantar
make build
make start

# 3. Instalar dependencias y crear la tabla (dentro del contenedor)
make composer c=install
make migrate

# 4. Abrir
#    http://localhost:8090
```

Puertos: la app queda en `8090` y MySQL en `3309` (elegidos para no chocar con otros proyectos).

## Comandos útiles

```bash
make help       # lista todos los targets
make logs       # logs en vivo
make sh         # shell dentro del contenedor
make console c="cache:clear"   # bin/console
make down       # baja contenedores y red
```

## Probar el desacople

1. Creá un par de notas con el adaptador por defecto (Doctrine/MySQL).
2. Cambiá la línea de `services.yaml` a `AlmacenamientoJson`.
3. `make console c="cache:clear"` y recargá: la lista aparece vacía (MySQL no se tocó) y las notas nuevas se escriben en `var/notas.json`.
4. Cambiá a `AlmacenamientoMemoria`: funciona igual, pero se reinicia en cada recarga.

El listado muestra arriba qué adaptador está activo, para que el cambio sea visible.

## Tests

El adaptador en memoria permite testear la lógica contra el contrato `AlmacenamientoNotas` **sin base de datos ni Docker**: es PHPUnit puro y corre en milisegundos.

```bash
make test
```

```
OK (5 tests, 12 assertions)   # en ~1 ms
```

Ver `src/tests/Almacenamiento/AlmacenamientoMemoriaTest.php`. El día que quieras testear el adaptador Doctrine, escribís otra clase que monte la base; el desacople hace que ese cambio no afecte al resto.

## Estructura relevante

```
.
├── docker/php-apache/      # imagen PHP 8.3 + Apache (docroot a public/)
├── docker-compose.yml      # app (8090) + mysql (3309), red propia
├── Makefile                # atajos de desarrollo
└── src/                    # la aplicación Symfony
    ├── config/services.yaml             # ← el interruptor
    └── src/
        ├── Almacenamiento/
        │   ├── AlmacenamientoNotas.php   # el puerto
        │   ├── AlmacenamientoDoctrine.php
        │   ├── AlmacenamientoJson.php
        │   └── AlmacenamientoMemoria.php
        ├── Controller/NotaController.php # depende solo del puerto
        ├── Entity/Nota.php
        └── Form/NotaType.php
    └── tests/Almacenamiento/
        └── AlmacenamientoMemoriaTest.php  # tests sin DB ni Docker
```

## Nota de diseño

Para mantener el demo simple, `Nota` lleva los atributos de Doctrine y se reutiliza como objeto de dominio. En una arquitectura hexagonal estricta separarías la entidad de dominio del modelo de persistencia; acá se priorizó la claridad para mostrar el concepto central: **el desacople vive en la interfaz y en el binding de `services.yaml`**.

## Licencia

[MIT](LICENSE) — Walter Rodriguez
