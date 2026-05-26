# ACF Field Block v2

Plugin WordPress que añade un bloque Gutenberg **completamente editable** para mostrar cualquier campo ACF. Diseñado para ser usado dentro de **Query Loop** y con soporte total de estilos.

## Características

- ✅ **Tipografía** — familia, tamaño, peso, estilo, interlineado, espaciado entre letras, decoración, transformación
- ✅ **Colores** — texto, fondo, degradados (paleta del tema + colores personalizados)
- ✅ **Bordes** — color, radio, estilo (sólido/punteado/etc.), grosor
- ✅ **Espaciado** — margin y padding visual (con los controles nativos de Gutenberg)
- ✅ **Sombra** — sombra de caja
- ✅ **Alineación de texto** — izquierda / centro / derecha / justificado (toolbar superior)
- ✅ **Query Loop** — detecta `postId` del contexto automáticamente
- ✅ **Etiqueta HTML configurable** — `<p>`, `<h2>`, `<span>`, etc.
- ✅ **4 modos de display** — Texto, HTML, Imagen, Enlace
- ✅ **Prefijo / Sufijo / Fallback**
- ✅ **Sin compilación npm** — listo para instalar

## Requisitos

- WordPress 6.3+
- PHP 7.4+
- ACF o ACF Pro activo

## Instalación

1. Sube la carpeta `acf-field-block` a `/wp-content/plugins/`
2. Activa el plugin desde **Plugins → Plugins instalados**

## Cómo funciona la edición de estilos

Los estilos se editan exactamente igual que cualquier bloque nativo de WordPress:

1. Selecciona el bloque **Campo ACF** en el editor
2. En la **barra lateral derecha**, aparecen los paneles:
   - **Tipografía** → Familia de fuente, Tamaño, Peso, Interlineado, Espaciado, etc.
   - **Color** → Texto, Fondo, Degradado
   - **Bordes** → Radio, Estilo, Color, Grosor
   - **Dimensiones** → Padding y Margin
3. En la **toolbar superior** del bloque: controles de alineación de texto

Todos los estilos se guardan como clases CSS del tema o como inline styles y se aplican directamente al elemento renderizado en el servidor.

## Estructura

```
acf-field-block/
├── acf-field-block.php    # Plugin principal + REST endpoint
└── build/
    ├── block.json         # Metadata + soporte de estilos declarado
    ├── index.js           # Editor (sin compilar, usa wp.* globals)
    ├── index.css          # Estilos del canvas en el editor
    ├── style.css          # Estilos en el frontend
    └── render.php         # Renderizado en servidor (aplica todos los estilos)
```

## Clave técnica: ¿Cómo funciona?

**En el editor (`index.js`)**:
- `useBlockProps()` → Gutenberg inyecta automáticamente todas las clases y estilos inline (color, tipografía, spacing, border) en el wrapper del bloque
- `InspectorControls` → Los paneles de Tipografía/Color/Bordes/Dimensiones los añade Gutenberg automáticamente cuando se declaran en `block.json → supports`
- `AlignmentControl` + `BlockControls` → Barra de herramientas con alineación

**En el servidor (`render.php`)**:
- `get_block_wrapper_attributes()` → Aplica todas las clases y estilos al elemento HTML final. Esto hace que los estilos del editor se vean idénticos en el frontend.
