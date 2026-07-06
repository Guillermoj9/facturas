# Facturador para Autonomos

Aplicacion web local y sencilla para gestionar facturas de uso personal como autonomo.

Esta pensada para ejecutarse en tu ordenador con Docker. No necesitas instalar PHP, Composer, Node ni configurar una base de datos.

## Que Incluye

- Configuracion inicial con tus datos fiscales.
- Gestion de clientes.
- Creacion, edicion y duplicado de facturas.
- Lineas de factura dinamicas.
- IVA e IRPF configurables.
- Numeracion correlativa anual.
- Estados de factura: borrador, enviada, pagada y vencida.
- PDF de factura con datos fiscales, logo e IBAN.
- Gestion de gastos deducibles.
- Dashboard con resumen mensual, trimestral y anual.
- Informes por fechas y resumen trimestral.
- Exportacion CSV.

## Que No Incluye

- No incluye VERI*FACTU/SIF.
- No incluye usuarios ni login.
- No es una app SaaS.
- No envia facturas por email.
- No tiene integracion bancaria.
- No esta pensada para publicarse directamente en internet.

Es una herramienta local para uso personal. Antes de usarla de forma profesional o comercial, revisa los requisitos fiscales y legales aplicables.

## Instalacion Sencilla

### 1. Instala Docker

Instala Docker Desktop:

```text
https://www.docker.com/products/docker-desktop/
```

### 2. Descarga el proyecto

```bash
git clone https://github.com/Guillermoj9/facturas
cd facturador-autonomos
```

Cambia la URL anterior por la URL real de tu repositorio.

### 3. Arranca la app

En Linux o macOS:

```bash
./abrir-facturador.sh
```

Si no tiene permisos:

```bash
chmod +x abrir-facturador.sh
./abrir-facturador.sh
```

En Windows:

```text
abrir-facturador.bat
```

La app se abrira en:

```text
http://localhost:8000
```

En el primer arranque Docker construye la aplicacion, crea la base de datos y ejecuta las migraciones.

## Uso Diario

Para abrir la app:

```bash
./abrir-facturador.sh
```

En Windows:

```text
abrir-facturador.bat
```

Para pararla:

```bash
docker compose down
```

Si tu Docker usa el comando clasico:

```bash
docker-compose down
```

## Donde Se Guardan Los Datos

La base de datos se guarda aqui:

```text
database/database.sqlite
```

Los logos y tickets subidos se guardan aqui:

```text
storage/app/public
```

Estos archivos no se suben a GitHub.

## Copia De Seguridad

Para guardar una copia de tus datos, copia:

```text
database/database.sqlite
storage/app/public
```

Ejemplo en Linux/macOS:

```bash
mkdir -p backups
cp database/database.sqlite backups/database-$(date +%Y-%m-%d).sqlite
tar -czf backups/storage-public-$(date +%Y-%m-%d).tar.gz storage/app/public
```

## Primer Uso

Al entrar por primera vez veras el formulario de configuracion inicial. Ahi puedes indicar:

- Nombre o razon social.
- NIF/DNI.
- Direccion fiscal.
- Email y telefono.
- IBAN.
- Logo.
- IVA por defecto.
- IRPF por defecto.
- Prefijo de numeracion.

Despues puedes cambiarlo desde `Configuracion`.

## Comandos Utiles

Ver logs:

```bash
docker compose logs -f facturador
```

Reconstruir la app:

```bash
docker compose up -d --build
```

Si el puerto 8000 esta ocupado, cambia esta linea en `docker-compose.yml`:

```yaml
ports:
  - "127.0.0.1:8001:8000"
```

Y abre:

```text
http://localhost:8001
```

## Stack

- Laravel.
- SQLite.
- Blade.
- Tailwind CSS.
- Chart.js.
- DomPDF.
- Docker.

## Licencia

MIT.
