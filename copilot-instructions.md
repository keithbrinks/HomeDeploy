# HomeDeploy - GitHub Copilot Instructions

## Project Overview
HomeDeploy is a lightweight, self-hosted deployment dashboard for single-server homelabs. It allows users to deploy PHP applications from GitHub repositories to the same server where HomeDeploy is running.

### Core Features
- **Server Management**: PHP version switching, service management (Nginx, MySQL, Redis).
- **Sites & Deployments**: GitHub integration, manual/webhook deployments, live logs.
- **Cloudflare Tunnel**: Management of tunnels and ingress rules.
- **Self-Update**: The application can update itself from GitHub releases.

---

## Tech Stack
- **Framework**: Laravel 12
- **Frontend**: Blade templates + Tailwind CSS (CDN) + Alpine.js
- **Database**: SQLite (Single file)
- **Queue**: Database driver
- **Process**: Symfony Process (via Laravel) for shell commands

---

## Coding Principles

### General
- Follow Laravel conventions.
- Keep it "dead-simple" — avoid complex abstractions.
- **Strict types everywhere**: All parameters, return types, and properties must be typed.
- **No PHPDoc blocks**: Use type hints instead.

### Architecture: Domain-Driven Design (Lite)
Group code by feature domain rather than technical layer where possible.
- `app/Domains/Sites/` (Models, Actions, DTOs for Sites)
- `app/Domains/Deployments/`
- `app/Domains/Server/`
- `app/Domains/Identity/` (GitHub OAuth)

### Controllers
- Keep them thin. Use Actions for logic.
- Use implicit route model binding.

### Actions
- Single-purpose classes in `app/Domains/{Domain}/Actions`.
- `execute()` method.

### Validation
- Use Form Requests in `app/Http/Requests`.

### Models
- Use `guarded = []` (unguarded) or strict `fillable`.
- `created_at` and `updated_at` are required.

---

## Frontend & Design System: "Mission Control"

### Aesthetic
- **Theme**: Dark Mode Only.
- **Colors**: Slate/Zinc backgrounds, Indigo/Violet accents.
- **Fonts**: Inter (UI), JetBrains Mono (Logs/Terminal).
- **Vibe**: Technical, precise, clean.

### Components
- **Status Badges**:
  - Green/Emerald: Running, Success
  - Amber/Yellow: Pending, Building
  - Red/Rose: Failed, Stopped
  - Slate/Gray: Inactive
- **Terminal/Logs**: Black background, monospace green/white text.

### Tailwind (CDN)
- Use utility classes.
- Since we use CDN, we cannot use `@apply` in CSS. Use Blade components for reusability.

---

## Testing (Pest)
- **TDD**: Write tests first.
- **Framework**: Pest PHP.
- **Types**: Feature tests for controllers/flows, Unit tests for Actions.

---

## Laravel 12 Specifics
- Use `bootstrap/app.php` for middleware/config.
- Use `routes/console.php` for commands.
