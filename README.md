<div align="center">

  <h1>🔄 Agent Rotator for WhatsApp</h1>
  <p><strong>WordPress Plugin</strong> — Distribuye contactos entrantes entre agentes según horario y disponibilidad</p>

  <p>
    <img src="https://img.shields.io/badge/version-1.0.0-blue?style=flat-square" alt="Version">
    <img src="https://img.shields.io/badge/WordPress-5.9%2B-21759b?style=flat-square&logo=wordpress" alt="WordPress">
    <img src="https://img.shields.io/badge/PHP-7.4%2B-777bb4?style=flat-square&logo=php" alt="PHP">
    <img src="https://img.shields.io/badge/License-GPLv2-green?style=flat-square" alt="License">
    <img src="https://img.shields.io/badge/Vanilla%20JS-No%20jQuery-f7df1e?style=flat-square&logo=javascript" alt="Vanilla JS">
  </p>

  <p>
    <a href="https://nrd.com.ar"><strong>🌐 Sitio Web</strong></a> •
    <a href="https://nrd.com.ar/contacto"><strong>💼 Contratar Premium</strong></a> •
    <a href="#-instalación"><strong>📥 Instalación</strong></a>
  </p>

  <br>
  <img src="./assets/screenshot-frontend.png" alt="Botón flotante de WhatsApp" width="400">
</div>

---

## 📋 Tabla de Contenidos

- [Descripción](#-descripción)
- [Demo](#-demo)
- [Características](#-características)
- [Lite vs Premium](#-lite-vs-premium)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Preguntas Frecuentes](#-preguntas-frecuentes)
- [Stack Técnico](#-stack-técnico)
- [Screenshots](#-screenshots)
- [Changelog](#-changelog)
- [Contribuir](#-contribuir)
- [Autor](#-autor)
- [Licencia](#-licencia)

---

## 📝 Descripción

**Agent Rotator for WhatsApp** es un plugin ligero para WordPress que agrega un botón flotante de contacto y enruta automáticamente a los visitantes hacia uno de tus agentes disponibles según:

- **Días laborables** — seleccioná qué días está activo cada agente (Lun–Dom).
- **Horario de atención** — configurá hora de inicio y fin por agente. Soporta turnos nocturnos (ej: 22:00–06:00).
- **Rotación round-robin** — cuando hay más de un agente activo, se selecciona uno al azar para distribuir la carga.

Si no hay ningún agente activo en el momento del clic, el botón permanece oculto. Los visitantes **nunca** llegan a un agente fuera de horario.

> 🇦🇷 Desarrollado en Argentina para la comunidad global de WordPress.

---

## 🎥 Demo

| Panel de Administración | Botón Flotante (Frontend) |
|:---:|:---:|
| <img src="./assets/screenshot-admin.png" alt="Panel admin" width="350"> | <img src="./assets/screenshot-frontend.png" alt="Botón flotante" width="350"> |

> ⚠️ **Nota:** Las imágenes de demo son referenciales. Reemplazar con screenshots reales del plugin.

---

## ✨ Características

- ✅ **Cero dependencias** — sin jQuery, sin librerías externas.
- ✅ **Mensaje predefinido** configurable desde el panel de administración.
- ✅ **Totalmente traducible** — listo para i18n (Text Domain: `agent-rotator-for-wa`).
- ✅ **Formulario protegido con Nonce** — seguro contra CSRF.
- ✅ **Sanitización y validación estricta** en cada guardado.
- ✅ **Turnos nocturnos** soportados nativamente.
- ✅ **Compatible con page builders** — Elementor, Divi, Beaver Builder, etc.

---

## 🆓 Lite vs Premium

| Característica | Lite (Gratis) | Premium |
|:---:|:---:|:---:|
| Agentes | **2 máximo** | Ilimitados |
| Rotación round-robin | ✅ | ✅ |
| Horarios nocturnos | ✅ | ✅ |
| Mensaje predefinido | ✅ | ✅ |
| Teléfono alternativo (fallback) por franja horaria | ❌ | ✅ |
| Soporte prioritario | ❌ | ✅ |

🔗 **[Solicitar versión Premium](https://nrd.com.ar/contacto)**

---

## 📥 Instalación

**Requisitos:** WordPress 5.9+ · PHP 7.4+

### Método 1: Descarga directa (recomendado)

1. Descargá el último release desde [GitHub Releases](https://github.com/igpaolillo-nrd/Rotator-Agent-for-WhatsApp/releases).
2. En tu WordPress, andá a **Plugins > Añadir nuevo > Subir plugin**.
3. Seleccioná el archivo `.zip` descargado e instalalo.
4. Activá el plugin.
5. Andá a **WA Rotator** en el sidebar del admin.

### Método 2: Instalación manual

```bash
# Clonar el repositorio
git clone https://github.com/igpaolillo-nrd/Rotator-Agent-for-WhatsApp.git

# Subir la carpeta a /wp-content/plugins/ via FTP/SFTP
cp -r Rotator-Agent-for-WhatsApp/agent-rotator-for-wa /ruta/a/tu/wordpress/wp-content/plugins/
```

Luego activalo desde **Plugins > Plugins instalados**.

---

## ⚙️ Configuración

1. Andá a **WA Rotator** en el menú lateral del admin.
2. Agregá tus agentes:
   - **Nombre** — nombre visible del agente.
   - **Teléfono** — solo dígitos, sin espacios ni guiones. Incluí el código de país sin el `+` (ej: `5491158887777`).
   - **Horario** — hora de inicio y fin.
   - **Días activos** — marcá los días que atiende.
3. Opcional: configurá un **mensaje predefinido global**.
4. Guardá los cambios — el botón flotante aparecerá automáticamente cuando haya al menos un agente activo.

---

## ❓ Preguntas Frecuentes

**¿Cuántos agentes puedo agregar en la versión gratis?**
> La versión Lite permite hasta 2 agentes. La versión Premium es ilimitada.

**¿El botón se muestra cuando no hay agentes activos?**
> No. El botón permanece oculto si ningún agente está dentro de su horario y días configurados.

**¿Puedo configurar turnos nocturnos?**
> Sí. Si la hora de inicio es posterior a la de fin (ej: 22:00–06:00), el plugin lo interpreta como un turno overnight.

**¿Es compatible con constructores de páginas?**
> Sí. El botón se inyecta vía `wp_footer`, por lo que funciona con Elementor, Divi, Beaver Builder y cualquier tema estándar de WordPress.

**¿Cómo debe ser el formato del número de teléfono?**
> Solo dígitos, de 6 a 15 caracteres. Incluí el código de país sin el signo `+` (ej: `5491158887777`).

**¿Ralentiza mi sitio web?**
> No. El plugin es completamente ligero. No carga frameworks pesados y utiliza JavaScript vanilla.

---

## 🛠️ Stack Técnico

| Tecnología | Uso |
|---|---|
| **PHP 7.4+** | Backend, hooks de WordPress, sanitización |
| **Vanilla JavaScript** | Lógica de rotación en frontend, sin jQuery |
| **WordPress Hooks API** | Integración con `wp_footer`, `admin_menu`, `admin_init` |
| **Nonce Verification** | Protección CSRF en formularios de admin |
| **i18n (gettext)** | Soporte multilingüe completo |

---

## 📸 Screenshots

### 1. Panel de administración — Gestión de agentes
<img src="./assets/screenshot-admin.png" alt="Panel admin" width="700">

### 2. Botón flotante en el frontend
<img src="./assets/screenshot-frontend.png" alt="Frontend" width="400">

> 💡 **Tip:** Las screenshots deben colocarse en la carpeta `/assets/` del repositorio.

---

## 🔄 Changelog

Ver el [CHANGELOG.md](./CHANGELOG.md) completo.

### [1.0.0] — 2026-07-26
- 🚀 Release inicial.
- Panel de administración con gestión de agentes (nombre, teléfono, horarios, días).
- Botón flotante con lógica de rotación de agentes.
- Límite de 2 agentes en versión Lite.
- Soporte completo i18n (text domain: `agent-rotator-for-wa`).
- Verificación Nonce y sanitización/validación estricta.

---

## 🤝 Contribuir

¿Encontraste un bug o tenés una idea de mejora?

- 🐛 [Abrí un Issue](https://github.com/igpaolillo-nrd/Rotator-Agent-for-WhatsApp/issues)
- 💬 [Participá en Discussions](https://github.com/igpaolillo-nrd/Rotator-Agent-for-WhatsApp/discussions)

Toda contribución es bienvenida.

---

## 👤 Autor

<table>
  <tr>
    <td align="center">
      <a href="https://github.com/igpaolillo-nrd">
        <img src="https://github.com/igpaolillo-nrd.png?size=100" width="100" style="border-radius:50%">
        <br>
        <strong>Ivan Paolillo</strong>
      </a>
      <br>
      Diseñador Gráfico & Multimedial · UX/UI · Docente · Fullstack Dev
      <br>
      🇦🇷 Argentina
    </td>
  </tr>
</table>

- 🌐 **Web:** [nrd.com.ar](https://nrd.com.ar)
- 💼 **LinkedIn:** [linkedin.com/in/ivan-paolillo](https://linkedin.com/in/ivan-paolillo)
- 📧 **Contacto:** [nrd.com.ar/contacto](https://nrd.com.ar/contacto)

---

## 📄 Licencia

Este proyecto está licenciado bajo la **GPLv2 o posterior**.

```
Agent Rotator for WhatsApp
Copyright (C) 2026  Ivan Paolillo / NRD

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.
```

Consultá el archivo [LICENSE](./LICENSE) para el texto completo.

---

<div align="center">
  <br>
  <p>⭐ Si te sirvió este plugin, dejá una estrella en el repo — ayuda un montón.</p>
  <p>💬 ¿Encontraste un bug o tenés una idea? Abrí un <a href="https://github.com/igpaolillo-nrd/Rotator-Agent-for-WhatsApp/issues">Issue</a>.</p>
  <br>
  <sub>Hecho con ❤️ en Argentina por <a href="https://nrd.com.ar">NRD</a></sub>
</div>
