# Shebamiles EMS Badge

A professional, animated branding badge for the Shebamiles Employee Management System, inspired by modern web applications.

## 🎨 Features

- **Smooth Animations**: Slide-in entrance animation and interactive hover effects
- **Responsive Design**: Adapts to all screen sizes (desktop, tablet, mobile)
- **Accessibility**: Full keyboard navigation and screen reader support
- **User Preference**: Remembers if user dismissed the badge (localStorage)
- **Theme Matching**: Uses Shebamiles orange gradient theme
- **Performance**: Hardware-accelerated animations with will-change optimization
- **High Contrast Support**: Adapts for users with accessibility needs
- **Reduced Motion**: Respects user's motion preferences

## 📍 Location

The badge appears in the bottom-right corner of all pages:
- Desktop: 16px from bottom and right
- Mobile: 12px from bottom and right

## 🎯 Customization

### Change Colors

Edit `includes/badge.php` CSS variables:

```css
#shebamiles-badge {
    --badge-bg: #FF6B35;           /* Main background color */
    --badge-bg-dark: #E55A2B;      /* Gradient end color */
    --badge-text: #FFFFFF;          /* Text color */
}
```

### Change Version Number

Update the version in `includes/badge.php`:

```html
<span id="shebamiles-badge-subtitle">v1.0.0</span>
```

### Change Badge Text

Modify the text content:

```html
<span id="shebamiles-badge-title">Shebamiles EMS</span>
<span id="shebamiles-badge-subtitle">v1.0.0</span>
```

### Change Logo

Replace the "S" with an image or different text:

```html
<div id="shebamiles-badge-logo">
    <img src="path/to/logo.png" alt="Logo" style="width: 20px; height: 20px;">
</div>
```

### Change Position

Modify the CSS positioning:

```css
#shebamiles-badge {
    bottom: 16px;    /* Change this */
    right: 16px;     /* Change this */
    /* Or use: */
    top: 16px;       /* For top positioning */
    left: 16px;      /* For left positioning */
}
```

### Disable on Specific Pages

To hide the badge on a specific page, add this before the closing `</body>` tag:

```html
<style>
    #shebamiles-badge {
        display: none !important;
    }
</style>
```

Or remove the include line:
```php
<?php include 'includes/badge.php'; ?>
```

### Add Click Action

To make the badge clickable (e.g., link to about page), wrap the content in an anchor tag:

```html
<a href="about.php" id="shebamiles-badge-content" style="text-decoration: none; color: inherit;">
    <!-- existing content -->
</a>
```

## 🔧 Technical Details

### Browser Support
- Chrome/Edge: 90+
- Firefox: 88+
- Safari: 14+
- Mobile browsers: Full support

### Performance
- Uses CSS transforms for smooth 60fps animations
- Hardware acceleration with `translateZ(0)`
- Minimal JavaScript (< 1KB)
- No external dependencies

### Accessibility
- ARIA labels for screen readers
- Keyboard navigation support
- High contrast mode support
- Reduced motion support
- Focus indicators

## 🚀 Advanced Customization

### Add More Information

You can add more rows to the badge:

```html
<div id="shebamiles-badge-text">
    <span id="shebamiles-badge-title">Shebamiles EMS</span>
    <span id="shebamiles-badge-subtitle">v1.0.0</span>
    <span style="font-size: 9px; opacity: 0.7;">© 2026</span>
</div>
```

### Make It Permanent

To disable the close button, add to CSS:

```css
#shebamiles-badge-close,
#shebamiles-badge-divider {
    display: none !important;
}
```

### Add Animation Effects

Add pulse animation:

```css
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

#shebamiles-badge:hover {
    animation: pulse 2s infinite;
}
```

### Change Animation Duration

Modify the animation timing:

```css
#shebamiles-badge {
    animation: slideInUp 0.5s ease forwards 0.5s;
    /* Format: name duration easing fill-mode delay */
}
```

## 📱 Mobile Optimization

The badge automatically:
- Reduces size on tablets (36px height)
- Further reduces on mobile (32px height)
- Hides subtitle on small screens
- Adjusts logo size
- Optimizes touch targets (minimum 40x40px)

## 🎨 Design Philosophy

Based on:
- Lovable.dev badge design patterns
- Material Design animation principles
- Apple Human Interface Guidelines
- WCAG 2.1 accessibility standards

## 🐛 Troubleshooting

**Badge not showing:**
- Check if localStorage has `shebamiles-badge-dismissed` set to `true`
- Clear browser cache
- Ensure `includes/badge.php` exists

**Badge overlapping content:**
- Adjust z-index in CSS (currently 999999)
- Move position (change bottom/right values)

**Animation not smooth:**
- Check browser support
- Disable other animations on page
- Check GPU acceleration in browser settings

## 📄 License

Part of the Shebamiles Employee Management System.
Free to modify for your project needs.
