# 🅿️ Parking Fácil 05 — Sitio Web Oficial

Sitio web corporativo y sistema de reservas online para **Parking Fácil 05**
(Luxury Parking Wash, S.L.), parking vigilado ubicado en **El Prat de Llobregat,
Barcelona**, junto al Aeropuerto Josep Tarradellas Barcelona-El Prat.

🌐 **[parkingfacil05.es](https://parkingfacil05.es)**

---

## ✨ Funcionalidades

- **Hero animado** con flip-card interactiva (hover + click)
- **Sección de servicios** con cards e imágenes propias
- **Tarifas dinámicas** del 1 al 30 días con cálculo automático de precio
- **Formulario de reserva en 4 pasos** (stepper):
  - Paso 1 — Selección de fechas y horas con cálculo de duración y precio en tiempo real
  - Paso 2 — Datos personales, vehículo y vuelos
  - Paso 3 — Método de pago (Transferencia bancaria / Bizum / Efectivo) con subida de comprobante
  - Paso 4 — Confirmación y envío
- **Envío automático de correos** al confirmar reserva:
  - Email al equipo con todos los datos + comprobante adjunto (JPG, PNG o PDF)
  - Email de confirmación al cliente con referencia y resumen
- **Formulario de contacto** con confirmación automática por correo
- **Galería** con modal de imágenes
- **Mapa embebido** de Google Maps
- **Diseño 100% responsive** (móvil, tablet, escritorio)

---

## 🛠️ Stack técnico

| Capa | Tecnología |
|---|---|
| Frontend | HTML5 + CSS3 + JavaScript vanilla |
| Backend | PHP nativo (`mail()`) |
| Hosting | Hostinger Premium |
| Dominio | `parkingfacil05.es` |
| Fuentes | Bebas Neue + DM Sans (Google Fonts) |
| Correo | Cuentas propias del dominio en Hostinger |

**Sin frameworks. Sin dependencias externas. Un solo `index.html` + `send_email.php`.**

---

## 📁 Estructura
