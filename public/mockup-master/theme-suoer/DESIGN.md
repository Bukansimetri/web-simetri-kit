---
name: Luminous Azure
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#3f4851'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#6f7882'
  outline-variant: '#bec7d3'
  surface-tint: '#006397'
  primary: '#006397'
  on-primary: '#ffffff'
  primary-container: '#0099e5'
  on-primary-container: '#002d48'
  inverse-primary: '#92ccff'
  secondary: '#3a5f94'
  on-secondary: '#ffffff'
  secondary-container: '#9fc2fe'
  on-secondary-container: '#294f83'
  tertiary: '#00677f'
  on-tertiary: '#ffffff'
  tertiary-container: '#009ec1'
  on-tertiary-container: '#002f3b'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#cce5ff'
  primary-fixed-dim: '#92ccff'
  on-primary-fixed: '#001d31'
  on-primary-fixed-variant: '#004b73'
  secondary-fixed: '#d5e3ff'
  secondary-fixed-dim: '#a7c8ff'
  on-secondary-fixed: '#001b3c'
  on-secondary-fixed-variant: '#1f477b'
  tertiary-fixed: '#b6ebff'
  tertiary-fixed-dim: '#47d6ff'
  on-tertiary-fixed: '#001f28'
  on-tertiary-fixed-variant: '#004e60'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  headline-xl:
    fontFamily: Manrope
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Manrope
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Manrope
    fontSize: 28px
    fontWeight: '600'
    lineHeight: 36px
  body-md:
    fontFamily: Be Vietnam Pro
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Be Vietnam Pro
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-bold:
    fontFamily: Be Vietnam Pro
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 12px
  md: 24px
  lg: 48px
  xl: 80px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 64px
---

## Brand & Style

The brand personality is centered on the concept of "Luminous Ecological Service"—a philosophy that blends technological precision with environmental harmony. It evokes a sense of clarity, reliability, and "ga kaku" (fluidity), moving away from rigid industrial structures toward a more organic, breathing digital interface.

The design style utilizes **Corporate Modernism with Glassmorphic accents**. This approach ensures a professional and trustworthy foundation while using translucent layers and soft background blurs to suggest the "luminous" quality of the brand. It is clean and spacious, prioritizing legibility and effortless navigation for a diverse user base.

## Colors

The palette is anchored by the **SUOER Blue (#0099E5)**, a vibrant and energetic primary color that symbolizes innovation and clear skies. This replaces previous green tones to align with a more expansive, atmospheric brand identity.

- **Primary:** SUOER Blue. Used for core brand elements, primary actions, and critical status indicators.
- **Secondary:** Deep Navy. Provides grounding and professional contrast, used for headers and primary text.
- **Tertiary:** Sky Glint. A lighter, more luminous cyan used for accents, highlights, and glassmorphic gradients to maintain the "ecological" lightness.
- **Neutral:** Slate Gray. A balanced, cool-toned neutral used for secondary text and structural borders to keep the UI feeling "not rigid."

## Typography

The typography system balances the technical precision of **Manrope** for headings with the warmth and approachability of **Be Vietnam Pro** for body text.

Headings should be set with tighter letter spacing to create a sense of modern authority. Body text utilizes a generous line height to ensure maximum readability and a "breathing" layout. On mobile devices, large display headings scale down to maintain visual balance without overwhelming the smaller viewport.

## Layout & Spacing

The layout utilizes a **12-column fluid grid** for desktop and a **4-column grid** for mobile. The spacing rhythm is based on an 8px baseline, ensuring mathematical harmony across all components.

To achieve the "ga kaku" aesthetic, the layout avoids dense clusters. Instead, it uses "safe areas" and dynamic padding to let content flow naturally. Desktop margins are generous (64px) to emphasize the premium, spacious feel of the service, while mobile margins compress to 16px to maximize the utility of the screen real estate.

## Elevation & Depth

Depth is communicated through **Tonal Layers** and **Glassmorphism**, avoiding heavy, traditional shadows that can make a UI feel dated or "rigid."

1.  **Base Layer:** The cleanest white (#FFFFFF) or extremely light cool gray (#F8FAFC).
2.  **Surface Layer:** Used for cards and containers, utilizing a subtle 1px border in a low-opacity version of the neutral color.
3.  **Luminous Depth:** For primary overlays or modals, a backdrop blur (12px to 20px) is applied to semi-transparent surfaces to create the "glassmorphic" effect, allowing background colors to bleed through softly.
4.  **Interactive Lift:** Only active elements (like buttons or hovered cards) receive a soft, ambient shadow tinted with a hint of the primary blue to suggest they are "floating" or "active."

## Shapes

The shape language is consistently **Rounded**, reflecting the "ecological" and "not rigid" narrative. Sharp corners are avoided entirely as they contradict the brand's friendly and fluid personality.

- Standard UI components (buttons, inputs) use a **0.5rem (8px)** radius.
- Large containers (cards, feature blocks) use a **1rem (16px)** radius.
- System-wide icons and decorative elements should mirror these soft curves to maintain a unified visual language.

## Components

### Buttons
Primary buttons are solid SUOER Blue with white text. Secondary buttons use a "ghost" style with a 1px SUOER Blue border and blue text. All buttons feature the `rounded-md` (8px) radius.

### Input Fields
Inputs are defined by a light gray border that transitions to the primary blue on focus. The focus state includes a soft blue outer glow (2px) to enhance the "luminous" feel.

### Cards
Cards are the primary container for information. They should have a white background, a 1px light gray border, and a `rounded-lg` (16px) corner radius. On hover, the border color may shift toward the primary blue or gain a very soft, diffused shadow.

### Chips & Tags
Used for categorization, chips should have a low-saturation blue background with deep navy text, maintaining readability while feeling lighter than a standard button.

### Progress & Status
Ecological service status should be represented by "Luminous" gradients—using a blend of SUOER Blue and Sky Glint to show active or healthy states.