# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_KEY}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Puedes obtener tu token de autenticación haciendo login en <code>POST /api/auth/login</code>. Luego incluye el token en el header: <code>Authorization: Bearer {token}</code>
