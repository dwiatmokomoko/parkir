# Frontend Setup Documentation

## Overview

This document describes the frontend infrastructure setup for the Parking Payment Monitoring System, including Tailwind CSS 3.x and Alpine.js configuration.

## Technology Stack

- **Tailwind CSS 3.x**: Utility-first CSS framework for responsive design
- **Alpine.js 3.x**: Lightweight JavaScript framework for reactive components
- **Vite**: Modern build tool for fast development and optimized production builds
- **Blade Templates**: Laravel's templating engine

## Installation & Configuration

### 1. Tailwind CSS Setup

#### Configuration File: `tailwind.config.js`

The Tailwind configuration includes:

- **Content paths**: Configured to scan all Blade templates, JS, and Vue files
- **Theme extensions**: Custom colors, spacing, and responsive breakpoints
- **Responsive breakpoints**:
  - `xs`: 320px (mobile)
  - `sm`: 640px (small devices)
  - `md`: 768px (tablets)
  - `lg`: 1024px (desktops)
  - `xl`: 1280px (large desktops)
  - `2xl`: 1536px (extra large screens)

#### CSS File: `resources/css/app.css`

The main CSS file includes:

- **Tailwind directives**: `@tailwind base`, `@tailwind components`, `@tailwind utilities`
- **Base styles**: HTML, body, and heading styles
- **Component utilities**: Buttons, forms, cards, alerts, tables, badges, modals, pagination
- **Custom utilities**: Text truncation, flexbox helpers, transitions, shadows

### 2. Alpine.js Setup

#### Configuration File: `resources/js/app.js`

```javascript
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

Alpine.js is automatically initialized and available globally as `window.Alpine`.

#### Bootstrap File: `resources/js/bootstrap.js`

Configures Axios for HTTP requests with CSRF token support.

### 3. Vite Configuration

#### File: `vite.config.js`

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

### 4. PostCSS Configuration

#### File: `postcss.config.js`

```javascript
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
```

## Base Layouts

### 1. Admin Dashboard Layout: `resources/views/layouts/app.blade.php`

Main layout for authenticated admin users with:

- **Navigation bar**: Logo, menu links, user dropdown
- **Responsive design**: Mobile-friendly hamburger menu
- **Flash messages**: Success and error alerts
- **Footer**: Copyright information
- **Alpine.js integration**: Dropdown menus with `x-data` and `@click`

**Usage**:
```blade
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Your content here -->
@endsection
```

### 2. Authentication Layout: `resources/views/layouts/auth.blade.php`

Centered layout for login pages with:

- **Gradient background**: Blue to indigo gradient
- **Centered card**: Login form container
- **Logo and branding**: DISHUB logo and system name
- **Responsive design**: Works on all screen sizes

**Usage**:
```blade
@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <!-- Your login form here -->
@endsection
```

### 3. Attendant Interface Layout: `resources/views/layouts/attendant.blade.php`

Layout for parking attendant interface with:

- **Navigation bar**: Logo and user menu
- **Simplified design**: Focused on QR code generation
- **Flash messages**: Success and error alerts
- **Alpine.js integration**: Dropdown menus

**Usage**:
```blade
@extends('layouts.attendant')

@section('title', 'Generate QR Code')

@section('content')
    <!-- Your attendant interface here -->
@endsection
```

## Component Classes

### Buttons

```html
<!-- Primary Button -->
<button class="btn btn-primary">Click me</button>

<!-- Secondary Button -->
<button class="btn btn-secondary">Click me</button>

<!-- Success Button -->
<button class="btn btn-success">Click me</button>

<!-- Danger Button -->
<button class="btn btn-danger">Click me</button>

<!-- Warning Button -->
<button class="btn btn-warning">Click me</button>

<!-- Sizes -->
<button class="btn btn-primary btn-sm">Small</button>
<button class="btn btn-primary btn-lg">Large</button>

<!-- Block Button -->
<button class="btn btn-primary btn-block">Full Width</button>

<!-- Disabled Button -->
<button class="btn btn-primary btn-disabled" disabled>Disabled</button>
```

### Forms

```html
<!-- Form Group -->
<div class="form-group">
    <label class="form-label">Email Address</label>
    <input type="email" class="form-input" placeholder="Enter email">
    <p class="form-help">We'll never share your email.</p>
</div>

<!-- Form Input -->
<input type="text" class="form-input" placeholder="Enter text">

<!-- Form Select -->
<select class="form-select">
    <option>Select an option</option>
</select>

<!-- Form Textarea -->
<textarea class="form-textarea" rows="4"></textarea>

<!-- Form Error -->
<p class="form-error">This field is required</p>
```

### Cards

```html
<!-- Basic Card -->
<div class="card">
    <div class="card-header">
        <h3>Card Title</h3>
    </div>
    <p>Card content goes here</p>
    <div class="card-footer">
        <button class="btn btn-primary">Action</button>
    </div>
</div>
```

### Alerts

```html
<!-- Success Alert -->
<div class="alert alert-success">
    Success message here
</div>

<!-- Danger Alert -->
<div class="alert alert-danger">
    Error message here
</div>

<!-- Warning Alert -->
<div class="alert alert-warning">
    Warning message here
</div>

<!-- Info Alert -->
<div class="alert alert-info">
    Information message here
</div>
```

### Tables

```html
<table class="table">
    <thead>
        <tr>
            <th>Column 1</th>
            <th>Column 2</th>
            <th>Column 3</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Data 1</td>
            <td>Data 2</td>
            <td>Data 3</td>
        </tr>
    </tbody>
</table>
```

### Badges

```html
<span class="badge badge-primary">Primary</span>
<span class="badge badge-success">Success</span>
<span class="badge badge-danger">Danger</span>
<span class="badge badge-warning">Warning</span>
<span class="badge badge-gray">Gray</span>
```

## Alpine.js Usage Examples

### Dropdown Menu

```html
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle Menu</button>
    <div x-show="open" @click.away="open = false">
        <a href="#">Menu Item 1</a>
        <a href="#">Menu Item 2</a>
    </div>
</div>
```

### Modal

```html
<div x-data="{ open: false }">
    <button @click="open = true">Open Modal</button>
    
    <div x-show="open" class="modal-overlay" @click="open = false"></div>
    <div x-show="open" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modal Title</h3>
            </div>
            <div class="modal-body">
                Modal content here
            </div>
            <div class="modal-footer">
                <button @click="open = false" class="btn btn-secondary">Close</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
```

### Tab Component

```html
<div x-data="{ activeTab: 'tab1' }">
    <div class="flex space-x-4 border-b">
        <button @click="activeTab = 'tab1'" :class="{ 'border-b-2 border-blue-600': activeTab === 'tab1' }">
            Tab 1
        </button>
        <button @click="activeTab = 'tab2'" :class="{ 'border-b-2 border-blue-600': activeTab === 'tab2' }">
            Tab 2
        </button>
    </div>
    
    <div x-show="activeTab === 'tab1'">Tab 1 content</div>
    <div x-show="activeTab === 'tab2'">Tab 2 content</div>
</div>
```

### Form Validation

```html
<div x-data="{ email: '', errors: {} }">
    <input 
        type="email" 
        x-model="email" 
        class="form-input"
        @blur="errors.email = !email ? 'Email is required' : ''"
    >
    <p x-show="errors.email" class="form-error" x-text="errors.email"></p>
</div>
```

## Responsive Design

### Breakpoint Usage

```html
<!-- Hidden on mobile, visible on tablet and up -->
<div class="hidden md:block">
    Desktop content
</div>

<!-- Responsive grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div>Column 1</div>
    <div>Column 2</div>
    <div>Column 3</div>
</div>

<!-- Responsive text size -->
<h1 class="text-2xl md:text-3xl lg:text-4xl">Responsive Heading</h1>

<!-- Responsive padding -->
<div class="p-4 md:p-6 lg:p-8">
    Responsive padding
</div>
```

### Mobile-First Approach

All responsive classes follow a mobile-first approach:
- Base styles apply to mobile (320px+)
- `sm:` prefix for 640px+
- `md:` prefix for 768px+
- `lg:` prefix for 1024px+
- `xl:` prefix for 1280px+
- `2xl:` prefix for 1536px+

## Development Workflow

### Running Development Server

```bash
npm run dev
```

This starts Vite in development mode with hot module replacement (HMR).

### Building for Production

```bash
npm run build
```

This creates optimized production builds in the `public/build` directory.

## Best Practices

1. **Use Tailwind utilities**: Prefer Tailwind classes over custom CSS
2. **Responsive design**: Always design mobile-first, then enhance for larger screens
3. **Component reusability**: Create reusable Blade components for common UI patterns
4. **Alpine.js for interactivity**: Use Alpine.js for simple interactive components
5. **Accessibility**: Include proper ARIA labels and semantic HTML
6. **Performance**: Minimize custom CSS, leverage Tailwind's purging
7. **Consistency**: Use the defined color palette and spacing scale

## Troubleshooting

### Tailwind classes not applying

1. Ensure the file is included in the `content` array in `tailwind.config.js`
2. Run `npm run build` to rebuild CSS
3. Clear browser cache (Ctrl+Shift+Delete)

### Alpine.js not working

1. Ensure Alpine.js is imported in `resources/js/app.js`
2. Check that `@vite` directive is in the layout
3. Verify Alpine.js is initialized with `Alpine.start()`

### Vite not hot-reloading

1. Ensure `npm run dev` is running
2. Check that the file is in the `content` paths
3. Restart the dev server if needed

## Resources

- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev)
- [Vite Documentation](https://vitejs.dev)
- [Laravel Vite Plugin](https://laravel.com/docs/vite)
