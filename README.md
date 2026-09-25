# Automotive Inventory – WordPress Backend

Plugin de WordPress creado como backend headless para la aplicación **Automotive SPA**.

Este plugin administra el inventario de vehículos dentro de WordPress y expone los datos necesarios mediante la REST API para que el frontend desarrollado con React y TypeScript pueda consumirlos.

## Repositorios

Backend / plugin de WordPress:

https://github.com/aburtocampos/automtive-spa-backend

Frontend React:

https://github.com/aburtocampos/automotive-spa

## Arquitectura general

El proyecto está dividido en dos partes:

```text
WordPress + Automotive Inventory Plugin
              ↓
       WordPress REST API
              ↓
     React + TypeScript SPA
              ↓
            Usuario
```

WordPress funciona como CMS headless. El administrador gestiona los vehículos desde el dashboard de WordPress y el frontend obtiene esa información mediante peticiones HTTP a la REST API.

El frontend no necesita acceso al panel administrativo de WordPress ni credenciales administrativas.

## Responsabilidad del plugin

El plugin **Automotive Inventory** agrega a WordPress la estructura necesaria para administrar el catálogo automotriz.

Incluye:

- Custom Post Type para vehículos.
- Taxonomías específicas del inventario.
- Campos personalizados del vehículo.
- Galería de imágenes.
- Video utilizado en la presentación del vehículo.
- Exposición de información mediante WordPress REST API.
- Endpoint REST personalizado para solicitudes de cotización y prueba de manejo.
- Envío de solicitudes por correo mediante `wp_mail()`.

## Estructura principal

La estructura utilizada por el plugin sigue una separación por responsabilidades.

```text
automotive-inventory/
├── automotive-inventory.php
└── includes/
    ├── class-plugin.php
    ├── class-vehicle-post-type.php
    ├── class-vehicle-taxonomies.php
    ├── class-vehicle-meta.php
    ├── class-vehicle-gallery.php
    └── class-vehicle-inquiry.php
```

### Archivo principal

El archivo principal del plugin define las constantes utilizadas por el proyecto, carga la clase principal y arranca el plugin.

### class-plugin.php

Actúa como bootstrap interno del plugin.

Se encarga de cargar e inicializar las clases responsables de cada módulo:

- Vehicle Post Type
- Vehicle Taxonomies
- Vehicle Meta
- Vehicle Gallery
- Vehicle Inquiry

Esta separación evita concentrar toda la lógica del plugin en un solo archivo y facilita el mantenimiento.

## Custom Post Type de vehículos

El plugin registra un Custom Post Type para representar cada vehículo del catálogo.

Los vehículos pueden utilizar las capacidades nativas de WordPress, entre ellas:

- Título
- Descripción
- Extracto
- Imagen destacada
- Campos personalizados

El Custom Post Type se expone mediante la REST API para que pueda ser consumido por la SPA.

Endpoint principal:

```text
GET /wp-json/wp/v2/vehicles
```

El frontend también puede consultar un vehículo específico usando su slug.

Ejemplo:

```text
GET /wp-json/wp/v2/vehicles?slug=picanto
```

## Taxonomías

Las clasificaciones reutilizables se implementaron como taxonomías de WordPress en lugar de valores hardcoded en React.

El plugin maneja:

- Marca
- Tipo de vehículo
- Transmisión
- Tipo de combustible

Endpoints utilizados por el frontend:

```text
GET /wp-json/wp/v2/vehicle-brands
GET /wp-json/wp/v2/vehicle-types
GET /wp-json/wp/v2/vehicle-transmissions
GET /wp-json/wp/v2/vehicle-fuel-types
```

Esto permite que nuevas marcas, tipos o transmisiones agregadas desde WordPress puedan aparecer en el frontend sin modificar el código de React.

## Campos personalizados

Cada vehículo contiene información adicional utilizada por el frontend.

Entre los campos implementados están:

```text
_vehicle_price
_vehicle_year
_vehicle_hover_video
_vehicle_gallery
```

### Precio

```text
_vehicle_price
```

Almacena el precio del vehículo.

### Año

```text
_vehicle_year
```

Almacena el año o modelo del vehículo.

### Video

```text
_vehicle_hover_video
```

Guarda el ID del archivo de video administrado desde la Media Library de WordPress.

El frontend obtiene posteriormente la URL real del archivo mediante el endpoint de medios de WordPress.

### Galería

```text
_vehicle_gallery
```

Almacena una colección de IDs de archivos de la Media Library asociados al vehículo.

El frontend utiliza esos IDs para cargar las imágenes de la galería en la página de detalle.

## Media Library

Las imágenes y videos se administran utilizando la Media Library nativa de WordPress.

El frontend puede resolver los IDs de los archivos mediante:

```text
GET /wp-json/wp/v2/media/:id
```

De esta forma WordPress mantiene la responsabilidad de administrar los archivos mientras React controla cómo se muestran al usuario.

## Interacción con el frontend

El frontend se encuentra en:

https://github.com/aburtocampos/automotive-spa

La SPA utiliza una variable de entorno para definir la URL base de la API:

```env
VITE_WORDPRESS_API_URL=https://pellas.aburto.dev/wp-json/wp/v2
```

A partir de esta URL React consulta:

- vehículos;
- imágenes y videos;
- marcas;
- tipos de vehículo;
- transmisiones;
- combustibles.

El flujo principal es:

```text
Administrador edita vehículo en WordPress
                 ↓
Plugin registra y expone los datos
                 ↓
WordPress REST API
                 ↓
React realiza fetch de los datos
                 ↓
TanStack Query administra el server state
                 ↓
La SPA renderiza catálogo y detalle
```

## Formulario de cotización y prueba de manejo

Además de los endpoints nativos de WordPress, el plugin agrega un endpoint REST personalizado:

```text
POST /wp-json/automotive/v1/inquiries
```

Este endpoint recibe las solicitudes enviadas desde el frontend.

Ejemplo de payload:

```json
{
  "requestType": "quote",
  "vehicleName": "Toyota RAV4",
  "vehicleYear": 2026,
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "phone": "8888-8888",
  "message": "Estoy interesado en este vehículo."
}
```

Los tipos de solicitud aceptados son:

```text
quote
test-drive
```

## Validación del formulario

Aunque React realiza validación básica para mejorar la experiencia del usuario, el backend vuelve a validar y sanitizar los datos recibidos.

El endpoint valida:

- Nombre requerido.
- Email requerido.
- Formato válido del email.
- Vehículo requerido.
- Tipo de solicitud permitido.

Los valores se sanitizan antes de utilizarse en el correo.

## Envío de correo

Después de validar la solicitud, WordPress utiliza:

```php
wp_mail()
```

para enviar el correo a la dirección administrativa configurada en WordPress.

La dirección receptora corresponde a:

```text
Settings → General → Administration Email Address
```

El correo se genera en HTML con estilos inline para mejorar la compatibilidad con clientes de correo como Gmail y Outlook.

El correo incluye:

- Tipo de solicitud.
- Vehículo.
- Año.
- Nombre del cliente.
- Email.
- Teléfono.
- Mensaje.
- Enlace para responder al cliente.

El encabezado `Reply-To` utiliza el correo validado del usuario, por lo que se puede responder directamente desde el cliente de correo.

## Respuestas del endpoint

Respuesta exitosa:

```json
{
  "success": true,
  "message": "Inquiry sent successfully."
}
```

En caso de datos inválidos, el endpoint devuelve un código HTTP `400`.

Si WordPress no logra procesar el envío del correo, devuelve un error del servidor.

El frontend utiliza estas respuestas para mostrar el estado correspondiente al usuario.

## Instalación del plugin

Copiar el plugin dentro de:

```text
wp-content/plugins/
```

Después:

1. Ingresar al dashboard de WordPress.
2. Ir a **Plugins**.
3. Buscar **Automotive Inventory**.
4. Activar el plugin.
5. Confirmar que el Custom Post Type y sus opciones aparecen en el administrador.

## Verificar la REST API

Una vez activado el plugin se puede comprobar el catálogo desde:

```text
https://TU-DOMINIO.com/wp-json/wp/v2/vehicles
```

Para comprobar el namespace personalizado:

```text
https://TU-DOMINIO.com/wp-json/automotive/v1
```

La respuesta debe mostrar la ruta:

```text
/automotive/v1/inquiries
```

con método:

```text
POST
```

## Configuración del correo

El plugin utiliza el sistema de correo de WordPress.

Para producción se recomienda configurar SMTP correctamente en WordPress para mejorar la entrega de los mensajes y reducir la posibilidad de que sean clasificados como spam.

## Seguridad

El endpoint de solicitudes es público porque debe poder ser utilizado por visitantes del frontend.

Sin embargo, su función está limitada específicamente a recibir y procesar los datos del formulario.

El plugin:

- no expone credenciales de WordPress al frontend;
- valida los campos recibidos;
- sanitiza los valores en el servidor;
- restringe los tipos de solicitud aceptados;
- no permite operaciones administrativas desde la SPA.

Para una versión de producción con mayor tráfico se podrían agregar:

- rate limiting;
- honeypot;
- CAPTCHA;
- logging de solicitudes;
- reglas CORS más restrictivas.

## Decisiones técnicas

### WordPress como CMS headless

Se utilizó WordPress para aprovechar su interfaz administrativa y su REST API, manteniendo el frontend desacoplado.

Esto permite administrar el inventario sin modificar React.

### Taxonomías para clasificaciones

Marca, tipo, transmisión y combustible se modelaron como taxonomías porque son datos reutilizables entre múltiples vehículos.

### Post Meta para atributos

Precio, año, video y galería son atributos asociados directamente a cada vehículo, por lo que se manejan como metadata.

### Media Library para archivos

Las imágenes y videos utilizan la Media Library nativa de WordPress en lugar de implementar un sistema independiente de archivos.

### Endpoint REST personalizado

El formulario utiliza un endpoint REST dedicado en lugar de exponer credenciales o depender de autenticación administrativa.

React únicamente envía los datos necesarios y WordPress mantiene la responsabilidad de validarlos y procesarlos.

## Mejoras futuras

Para una implementación de producción se podrían incorporar:

- endpoint optimizado que entregue URLs de medios directamente;
- reducción de requests adicionales a `/media/:id`;
- paginación del lado del servidor;
- filtros REST por taxonomía;
- rate limiting del formulario;
- almacenamiento opcional de leads;
- integración con CRM;
- SMTP dedicado;
- logs de errores;
- tests automatizados;
- configuración de destinatarios desde el administrador.

## Frontend

El frontend que consume este plugin está disponible en:

https://github.com/aburtocampos/automotive-spa

Está desarrollado con React, TypeScript, Vite, React Router y TanStack Query.

## Autor

Ramon Aburto

https://aburto.dev
