# SocialConnect Style Guide

## Brand Overview
SocialConnect is a modern social media platform focused on meaningful connections, engaging content, and vibrant communities. Our design reflects accessibility, inclusivity, and user-friendly interaction.

## Color Palette

### Primary Colors
- **Brand Purple**: `#8B5CF6` (Primary brand color)
- **Dark Text**: `#1F2937` (Headings and primary text)
- **Body Text**: `#6B7280` (Secondary text and descriptions)
- **White**: `#FFFFFF` (Backgrounds and contrast)

### Secondary Colors
- **Light Gray**: `#F9FAFB` (Background sections)
- **Border Gray**: `#E5E7EB` (Borders and dividers)
- **Success Green**: `#10B981` (Success states)
- **Warning Orange**: `#F59E0B` (Warnings)
- **Error Red**: `#EF4444` (Error states)

### Usage Guidelines
- Use Brand Purple for primary actions, links, and brand elements
- Maintain high contrast ratios (4.5:1 minimum) for accessibility
- Use Dark Text for headlines and important information
- Body Text for secondary content and descriptions

## Typography

### Font Family
- **Primary**: Sans-serif system font stack
- **Fallback**: Arial, Helvetica, sans-serif

### Font Hierarchy
- **H1 (Page Titles)**: 48px, Bold, Dark Text
- **H2 (Section Headers)**: 36px, Semibold, Dark Text
- **H3 (Subsections)**: 24px, Semibold, Dark Text
- **H4 (Card Titles)**: 18px, Medium, Dark Text
- **Body Large**: 18px, Regular, Body Text
- **Body Regular**: 16px, Regular, Body Text
- **Body Small**: 14px, Regular, Body Text
- **Caption**: 12px, Regular, Body Text

### Usage Guidelines
- Use sentence case for headings (capitalize first letter only)
- Maximum line length of 65-75 characters for readability
- Line height of 1.5-1.6 for body text
- Use font weights sparingly (Regular, Medium, Semibold, Bold only)

## Layout & Spacing

### Grid System
- **Container Max Width**: 1200px
- **Column Count**: 12-column grid
- **Gutter Width**: 24px
- **Margins**: 16px (mobile), 24px (tablet), 32px (desktop)

### Spacing Scale
- **4px**: Fine details, borders
- **8px**: Small spacing between related elements
- **16px**: Default spacing between components
- **24px**: Section spacing
- **32px**: Large section spacing
- **48px**: Page section dividers
- **64px**: Major page sections

### Breakpoints
- **Mobile**: 320px - 767px
- **Tablet**: 768px - 1023px
- **Desktop**: 1024px and above

## Components

### Buttons

#### Primary Button
- **Background**: Brand Purple (`#8B5CF6`)
- **Text**: White
- **Padding**: 12px 24px
- **Border Radius**: 6px
- **Font**: 16px, Medium
- **Hover**: Darken background by 10%
- **Active**: Darken background by 15%

#### Secondary Button
- **Background**: White
- **Text**: Brand Purple
- **Border**: 1px solid Brand Purple
- **Padding**: 12px 24px
- **Border Radius**: 6px
- **Font**: 16px, Medium
- **Hover**: Light purple background (`#F3F4F6`)

#### Disabled Button
- **Background**: Border Gray (`#E5E7EB`)
- **Text**: Body Text (`#6B7280`)
- **No hover effects**

### Navigation

#### Header Navigation
- **Background**: White
- **Height**: 64px
- **Logo**: Brand Purple, 24px height
- **Links**: Body Text color, 16px
- **Active Link**: Brand Purple
- **Hover**: Darken text by 20%

#### Mobile Navigation
- **Hamburger Menu**: 3 lines, 2px height, 4px spacing
- **Overlay**: Semi-transparent dark background
- **Slide-in Menu**: White background, from right

### Cards
- **Background**: White
- **Border**: 1px solid Border Gray
- **Border Radius**: 8px
- **Padding**: 24px
- **Shadow**: Subtle drop shadow (`0 1px 3px rgba(0,0,0,0.1)`)
- **Hover**: Lift shadow slightly

### Forms

#### Input Fields
- **Border**: 1px solid Border Gray
- **Border Radius**: 6px
- **Padding**: 12px 16px
- **Font**: 16px, Regular
- **Focus**: Brand Purple border, purple glow
- **Error**: Red border, red text

#### Labels
- **Font**: 14px, Medium
- **Color**: Dark Text
- **Margin**: 8px bottom

## Images & Media

### Profile Pictures
- **Default Size**: 40px × 40px
- **Large Size**: 80px × 80px
- **Border Radius**: 50% (circular)
- **Border**: 2px white border with subtle shadow

### Hero Images
- **Aspect Ratio**: 16:9 or 4:3
- **Border Radius**: 8px
- **Quality**: High resolution, optimized for web
- **Alt Text**: Always provide descriptive alt text

### Icons
- **Style**: Outline style icons
- **Size**: 16px, 20px, 24px standard sizes
- **Color**: Inherit from parent text color
- **Hover**: Brand Purple transition

## Accessibility

### Requirements
- **Color Contrast**: Minimum 4.5:1 for normal text, 3:1 for large text
- **Focus Indicators**: Visible focus outlines on all interactive elements
- **Alt Text**: Descriptive alternative text for all images
- **Keyboard Navigation**: Full keyboard accessibility
- **Screen Readers**: Semantic HTML and ARIA labels

### Implementation
- Use semantic HTML elements
- Provide skip navigation links
- Ensure proper heading hierarchy
- Include loading states and error messages
- Test with screen readers

## Motion & Animation

### Principles
- **Duration**: 150-300ms for micro-interactions
- **Easing**: `ease-out` for entrances, `ease-in` for exits
- **Reduce Motion**: Respect user preference for reduced motion

### Common Animations
- **Hover Effects**: 150ms transition
- **Button Press**: 100ms scale down slightly
- **Page Transitions**: 250ms fade or slide
- **Loading Spinners**: Smooth rotation

## Best Practices

### Do's
- Maintain consistent spacing throughout the design
- Use the established color palette
- Provide clear visual hierarchy
- Optimize for mobile-first design
- Include loading and error states
- Test accessibility with real users

### Don'ts
- Don't use more than 3 font weights on a page
- Don't use pure black (`#000000`) for text
- Don't rely solely on color to convey information
- Don't create custom components without consulting this guide
- Don't ignore mobile breakpoints

## Implementation Notes

### CSS Custom Properties
```css
:root {
  --color-brand-purple: #8B5CF6;
  --color-text-dark: #1F2937;
  --color-text-body: #6B7280;
  --color-white: #FFFFFF;
  --color-background-light: #F9FAFB;
  --color-border: #E5E7EB;
  
  --spacing-xs: 4px;
  --spacing-sm: 8px;
  --spacing-md: 16px;
  --spacing-lg: 24px;
  --spacing-xl: 32px;
  
  --border-radius-sm: 4px;
  --border-radius-md: 6px;
  --border-radius-lg: 8px;
}
```

### Responsive Utilities
- Use relative units (rem, em, %) where appropriate
- Implement mobile-first media queries
- Test across all supported breakpoints
- Optimize images for different screen densities

---

**Last Updated**: May 2025  
**Version**: 1.0  
**Contact**: Design Team for questions or updates