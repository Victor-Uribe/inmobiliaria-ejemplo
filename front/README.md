## Comandos

```bash
npm install
cp .env.example .env.local
npm run serve
```

Para compilar:

```bash
npm run build
```

## Stack

- Vue 2
- Vue CLI
- Bootstrap 5
- GSAP
- Vue Router
- Axios
- Imagenes locales llamativas en `src/assets/images`
- Banners por subida local `multipart/form-data`
- Comprador, pagos mensuales y semaforo de atraso en propiedades vendidas
- Modales separados para comprador y pagos, comunicados con eventos `save` y `close`
- Formularios CRUD del admin en modales: propiedades, galeria, banners, usuarios, testimonios, comprador y pagos.

## Rutas

- `/` Landing publica
- `/propiedades` Catalogo publico
- `/propiedades/:slug` Detalle publico
- `/admin/login` Login
- `/admin` Dashboard
- `/admin/propiedades` Propiedades
- `/admin/leads` Leads, solo Admin/Super Admin
- `/admin/usuarios` Usuarios, solo Admin/Super Admin
- `/admin/testimonios` Testimonios, solo Admin/Super Admin
- `/admin/banners` Banners, solo Admin/Super Admin

## API

Configura `VUE_APP_API_URL` en `.env.local`. Por defecto usa:

```txt
http://localhost:8000/api
```

El asesor solo ve el menu de Dashboard y Propiedades. La seguridad real tambien debe aplicarse en Laravel con middleware/policies.

## Endpoints admin nuevos

Para completar comprador y pagos mensuales en Laravel:

```txt
POST /properties/{id}/buyer
GET  /properties/{id}/payments
POST /properties/{id}/payments
```

Campos esperados en propiedades o comprador:

```txt
buyer_number
buyer_name
buyer_phone
buyer_email
monthly_payment_amount
payment_due_day
payments[]
months_late opcional
```

El semaforo se calcula asi:

- Verde: 0 meses de atraso.
- Amarillo: 1 o 2 meses de atraso.
- Rojo: 3 meses o mas de atraso.

Cuando el semaforo esta amarillo o rojo y existe `buyer_phone`, aparece el boton WhatsApp con mensaje de recordatorio.

Los banners deben enviarse como archivo en el campo `image`; no se usa URL para registrar imagen.

Comprador y pagos no se capturan en el formulario principal de propiedad. En el admin de propiedades:

- Cambia la propiedad a `Vendida`.
- Guarda la propiedad.
- Usa el boton `Comprador` para registrar comprador y datos del pago mensual.
- Usa el boton `Pagos` para registrar mensualidades.

Todos los formularios administrativos se abren desde botones y viven en componentes modal dentro de `src/components/admin`. Cada modal emite `save` para guardar y `close` para cerrar.
