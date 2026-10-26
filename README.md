# Peluquería New Style — website

WordPress website for **Peluquería New Style**, a barbershop in Logroño (Plaza 1 de Mayo nº10), built under Spain's **Kit Digital** programme.

- Single-page site (Inicio, Servicios, Nosotros, Espacio, Galería, Contacto) plus an accessibility statement
- Spanish (default) and English at `/en/` via TranslatePress
- Built to WCAG 2.1 AA (Lighthouse 100 for accessibility, SEO and best practices)
- EU NextGenerationEU / Plan de Recuperación funding banner on every page

## Stack

| Part | What |
|---|---|
| Runtime | Docker Compose: `wordpress:latest` (Apache + PHP) and `mariadb:11` |
| Parent theme | [Astra](https://wordpress.org/themes/astra/) |
| Child theme | `wp-content/themes/newstyle-barber` — all custom design and code |
| Plugins | TranslatePress (languages), UpdraftPlus (backups / migration) |

Only `wp-content/` is mounted into the container. WordPress core comes from the Docker image.

## Requirements

- Docker with the Compose plugin (`docker compose version`)
- Free port `8081`, or another port set in `.env`

## Run it locally

```bash
git clone git@github.com:rene-mdd/barberia-logrono.git
cd barberia-logrono

# 1. Settings and passwords (never committed)
cp .env.example .env
#    edit .env — for local use, any values work

# 2. Start
docker compose up -d

# 3. Let WordPress write uploads, updates, translations and backups
#    (these folders are git-ignored, so create them first)
docker compose exec -w /var/www/html/wp-content wordpress sh -c \
  'mkdir -p uploads upgrade updraft languages && chown -R www-data:www-data uploads upgrade updraft languages plugins'
```

Open <http://localhost:8081>. If `.env` is missing, Compose stops with an error that names the missing variable. That is on purpose.

### First run: get the content

The **database and uploaded photos are not in git**: pages, menus, translations, settings and the Media Library live in the Docker volume `db_data` and in `wp-content/uploads/`. On a fresh clone you therefore start with an empty WordPress:

1. Complete the WordPress installer at <http://localhost:8081>.
2. Restore the content from an **UpdraftPlus backup**. Ask the maintainer for the five backup files: `-db.gz`, `-plugins.zip`, `-themes.zip`, `-uploads.zip` and `-others.zip`.
   - Activate UpdraftPlus (Plugins), then open **Settings → UpdraftPlus Backups → Upload backup files**.
   - Add the five files, then click **Restore**.
   - Log in again with the WordPress user from the backup.
3. Go to **Settings → Permalinks → Save**. This recreates the `.htaccess` rewrite rules inside the container, so URLs like `/en/` work.

If you have no backup, activate the **New Style Barber** theme (Appearance → Themes) and TranslatePress. Activating the theme creates the menu, site name and accessibility statement, but not the home page content.

## Everyday commands

```bash
docker compose ps                    # status
docker compose logs -f wordpress     # PHP / Apache logs
docker compose stop                  # stop (data kept)
docker compose up -d                 # start again
docker compose exec db mariadb -u"$DB_USER" -p wordpress   # database shell (password from .env)
docker compose down -v               # ⚠️ delete containers AND the database volume
```

Run PHP inside WordPress, for example to check a setting:

```bash
docker compose exec -w /var/www/html wordpress php -r 'require "wp-load.php"; echo get_option("blogname");'
```

## Project structure

```
compose.yaml                     Docker services (reads .env)
.env.example                     template for .env (passwords, port)
wp-content/
  themes/astra/                  parent theme (third-party, do not edit)
  themes/newstyle-barber/        child theme — the project's code
    functions.php                assets, SEO tags, menu phone + language switcher,
                                 funding banner, accessibility statement, setup on activation
    inc/business-info.php        business data: name, address, phone, Instagram
    front-page.php               renders the "Inicio" page blocks full width
    style.css                    all styles (nsb-* classes)
    assets/js/                   header offset, gallery lightbox, carousel
    assets/img/                  funding logos, favicon, social preview image
  plugins/                       TranslatePress, UpdraftPlus
  languages/                     Spanish language packs (WordPress, Astra, plugins)
  uploads/                       Media Library (not in git)
  updraft/                       UpdraftPlus backups (not in git)
```

## Editing content

- **Home page text and photos:** Pages → **Inicio** in the block editor. Sections are groups with `nsb-*` classes. Keep those classes, because the design depends on them.
  - Galleries use the block styles **Carrusel** and **Cuadrícula con ampliación**.
  - Buttons use **Dorado**, **Contorno claro** and **Contorno oscuro**.
- **English translations:** open the site and click *Translate Site* in the admin bar, or go to Settings → TranslatePress. Add English for any new or changed Spanish text.
- **Phone, address, Instagram:** these appear in the menu, footer and SEO data. Change them in `inc/business-info.php`.
- After editing theme files, simply reload. The CSS and JS versions change with each file's modification time, so browsers never use stale files.

## Kit Digital requirements — keep these passing

- The **funding band** (EU emblem «Financiado por la Unión Europea – NextGenerationEU» and the Plan de Recuperación logo) must stay on every page, with the EU emblem at least 30% larger than the other logo.
- **WCAG 2.1 AA:**
  - Gold text on cream backgrounds must use `--nsb-gold-text` (5.4:1 contrast), not `--nsb-gold`.
  - Every image needs alt text.
  - Interactive elements must work with the keyboard.
  - The carousel keeps its pause button.
- The **accessibility statement** is linked from the footer at `/declaracion-de-accesibilidad/`.
- **Multidioma evidence:** a screenshot of Settings → TranslatePress, plus the `/en/` site.

To check after changes, run Lighthouse in Chrome DevTools on `/` and `/en/`: accessibility, SEO and best practices should all be 100.

## Deploying (Hostinger)

1. Make a fresh backup locally (UpdraftPlus → Backup Now). The files appear in `wp-content/updraft/`.
2. On the hosting: install WordPress, enable SSL, install UpdraftPlus, upload the five files and **Restore**. UpdraftPlus replaces `http://localhost:8081` with the live domain.
3. On the live site:
   - Settings → Permalinks → **Save**.
   - Set a strong admin password.
   - Make sure "Discourage search engines" is off.
   - Purge any cache plugin.

## Security notes

- `.env`, `wp-config.php`, database dumps, backups (`wp-content/updraft/`) and Kit Digital paperwork are git-ignored. Keep it that way: a database backup contains the admin account and every setting.
- Older commits contain the original local-only database passwords (`admin`). Never reuse them anywhere real.
