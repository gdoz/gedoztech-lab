# ⚗️ GedozTech Lab Website

> [**https://gedoz.tech**](https://gedoz.tech)  

A static website with Markdown rendering, multi-language support, a secure visit-tracking API, GA4 integration with GDPR/LGPD consent, a clean design system, and automated CI/CD for deployment.  
Designed to operate efficiently on a traditional shared hosting environment (this is a project requirement).

This repository demonstrates:
- a production-ready static website
- a minimal and secure backend API for anonymous visit tracking
- engineering practices focused on **simplicity**, **security**, **maintainability**, and **clarity**

---

# 🌍 1. Overview

This project integrates:

* A **static, SEO-optimized documentation website**
* A **secure PHP API** for anonymous page visit tracking
* A **MySQL database** for storing aggregated visit metrics
* A **CI/CD system** with GitHub Actions
* Full **GDPR/LGPD compliance**, including a consent banner
* A clean **design system** with responsive layout and theme switching

---

# 🏗 2. High-Level Architecture

```
+---------------------------+          +------------------------+         +--------------------+
|        Frontend           |  POST    |        Backend         | INSERT  |      Database      |
| - index.html              | ------>  | visit.php (PHP 8.3)    | ----->  | MySQL / MariaDB    |
| - content-{lang}.md       |          | - token validation     |         | - visits table     |
| - JS: renderer, TOC, GA4  |          | - sanitization         |         |                    |
| - Theme + language switch |          | - no IP collected      |         |                    |
+---------------------------+          +------------------------+         +--------------------+
         |   GET Markdown                          ^
         |                                         |
         └---------- loads MD files ---------------┘
```

**Flow Summary**

1. User loads the static website
2. JavaScript loads Markdown content dynamically
3. On visit, JS sends an authenticated POST to backend
4. PHP backend stores a minimal, anonymous record
5. Google Analytics 4 tracks additional aggregated metrics
6. CI/CD deploys front and back independently via FTP

---

# 🛡️ 3. High-Level Security Model

Security is a primary concern in this project. While the application is intentionally simple, the architecture follows several best practices to minimize risk and ensure reliable operation even in shared hosting environments.

### **Goals**

* Protect server resources from unauthorized requests
* Prevent credential exposure
* Avoid collecting or storing personal data
* Ensure database integrity
* Maintain a small, auditable attack surface
* Enable safe public hosting of the entire codebase

### 🔐 Authentication & API Access

* Visit-tracking endpoint protected by **token-based authentication**
* Token stored exclusively in **GitHub Secrets**
* `.env` generated dynamically during deployment
* Unauthorized requests rejected early

### 🗄 Data Minimization

No personal data is collected:

* No IP
* No User-Agent
* No geolocation
* No cookies (except optional GA4)

Stored fields only:

* Timestamp
* Language
* Theme
* Optional referrer

### 🧱 Input & Query Safety

* All inputs validated and sanitized
* Prepared statements only
* No dynamic SQL
* Restricted input sizes

### 🚫 Attack Surface Minimization

* A single, stateless backend endpoint
* No file uploads
* No session management
* No SSR, templating, or dynamic routing
* No admin interfaces

### 🔮 Future Hardening

* Cloudflare WAF & rate limiting
* DDoS protection
* IP throttling at CDN
* VPS/container deployment
* Proxy route (hide token from client side)

---

# 🎨 4. Design System & UI Guidelines

The frontend follows a minimalist, documentation-oriented design system:

### **Visual Style**

* Dark/light theme with CSS variables
* Clean typography using system fonts
* Subtle shadows and rounded corners
* Colors inspired by GitHub Dark / Primer design

### **Layout System**

* CSS Grid: left gutter, content column, right TOC
* Sticky TOC and header on desktop
* TOC becomes non-sticky or hidden on mobile
* Header is fixed and aligned to the content column
* Mobile-first responsiveness

### **Components**

* **Header:**
  Sticky top bar with brand logo + name
* **Content Panel:**
  Markdown rendered inside a styled panel
* **TOC Sidebar:**
  Auto-generated navigation
* **Consent Banner:**
  Required for GA4 activation
* **Theme Switcher:**
  Toggle persisted in `localStorage`
* **Language Switcher:**
  Switch between EN and PT, with `localStorage` persistence

### **Design Principles**

* **Simplicity:**
  Minimal layout with a focus on text readability
* **Legibility:**
  Ample spacing, consistent headers, visible anchors
* **Accessibility:**
  Sufficient contrast, keyboard-friendly, semantic HTML
* **Consistency:**
  Harmonized shadows, spacing, typography

---

# 💻 5. Frontend Features

* Markdown rendering (Marked.js + DOMPurify)
* Syntax highlighting (Highlight.js)
* Automatic Table of Contents
* Multi-language support (EN/US + PT/BR)
* Theme switcher (dark/light)
* Responsive grid design
* SEO meta tags + Open Graph + Twitter Cards
* PWA manifest
* Cookie consent banner for GDPR/LGPD
* Google Analytics 4 integration
* Anonymous visit counter integration

---

# 🛢 6. Backend API Features (PHP 8.3)

* Stateless POST endpoint
* Token-based authorization
* Sanitized inputs
* PDO prepared statements
* `.env` based configuration
* No cookies, no sessions, no PII

---

# 📋 7. Database Setup

Run the migration:

```sql
CREATE TABLE visit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_path VARCHAR(255) NOT NULL,
  lang CHAR(2) NOT NULL DEFAULT 'en',
  theme VARCHAR(16) NOT NULL DEFAULT 'auto',
  referrer VARCHAR(512) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_page_path (page_path),
  INDEX idx_created_at (created_at),
  INDEX idx_lang (lang),
  INDEX idx_theme (theme)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
```

No personal data is stored.

---

# 🔧 8. Local Development

Use Docker for local MySQL:

```bash
docker run -d --name docs-db \
  -e MYSQL_ROOT_PASSWORD=root \
  -e MYSQL_DATABASE=dbname \
  -p 3306:3306 mysql:8
```

Run PHP backend:

```bash
php -S localhost:8000 -t backend
```

Open frontend directly via browser.

---

# 🚀 9. CI/CD (GitHub Actions)

Two independent workflows:

### `deploy-frontend.yml`

* Deploys static frontend
* Trigger: push to main
* Manual approval required
* Uses FTP-Deploy-Action

### `deploy-backend.yml`

* Generates `.env`
* Deploys backend folder
* Manual approval required
* Uses FTP-Deploy-Action

---

# 🏗 10. Server Requirements

* Linux + Apache
* PHP 8.3
* MySQL/MariaDB
* FTPS enabled

---

# 📄 11. License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

---

# 🎉 Final Notes

This project aims to balance:

* simplicity
* clean design
* secure engineering
* portability
* educational value
