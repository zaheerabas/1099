# taxmetter website

A complete static website: home page, 1099 tax calculator, guides, legal pages, icons and SEO files.
No database, no build step, no plugins. It runs on any shared hosting.

## Folder structure

```
public_html/                 ← upload the CONTENTS of this folder to your hosting
├── index.html               Home page (animated hero + 2026 figures + steps)
├── calculator.html          1099 tax calculator + FAQ
├── how-it-works.html        The calculation method, with a worked example
├── tax-guide.html           2026 tax guide for 1099 workers
├── privacy.html             Privacy policy
├── terms.html               Terms and tax disclaimer
├── 404.html                 "Page not found" page
├── .htaccess                HTTPS, 404 page, clean URLs, caching, compression
├── robots.txt               Tells search engines what to crawl
├── sitemap.xml              List of pages for Google
├── site.webmanifest         Lets phones "add to home screen"
└── assets/
    ├── css/
    │   ├── site.css         Colours, themes, header, footer, page styles (every page)
    │   ├── home.css         Hero cards and animation (home page only)
    │   └── calculator.css   Calculator (calculator page only)
    ├── js/
    │   ├── site.js          Header menu and scroll effects (every page)
    │   ├── home.js          Hero animation (home page only)
    │   └── calculator.js    Tax maths and calculator page (2026 figures in the TAX object)
    └── img/
        ├── favicon.svg, favicon-32.png, apple-touch-icon.png, icon-192.png, icon-512.png
        └── og-image.png     Preview image shown when the site is shared (1200 × 630)
```

## Before you upload: 2 find-and-replace steps

Open the `public_html` folder in VS Code (or any editor with "Replace in files") and replace across all files:

1. `https://YOUR-DOMAIN.com` → your real address, for example `https://taxmetter.com` (no slash at the end). This updates canonical links, sitemap, robots.txt and share previews.
2. `hello@YOUR-DOMAIN.com` → your real contact email (privacy and terms pages).

## Upload to shared hosting (cPanel)

1. Log in to cPanel and open **File Manager**.
2. Go to `public_html` (or the folder for your domain if it's an add-on domain).
3. Click **Settings** (top right) and tick **Show Hidden Files**, so you can see `.htaccess`.
4. Zip the **contents** of the local `public_html` folder, upload the zip, then right-click it → **Extract**. Delete the zip afterwards.
5. Make sure `index.html` sits directly inside `public_html`, not inside another folder.
6. In cPanel, open **SSL/TLS Status** and run **AutoSSL** if your domain doesn't have a certificate yet.
7. Visit your domain. Check the home page, the calculator, and a made-up address like `/abc` to see the 404 page.

If your host already has a `.htaccess` file in `public_html` (WordPress, for example), merge the two instead of overwriting.

If the site shows "too many redirects" after upload, your host handles HTTPS another way: delete the three lines under "Always use HTTPS" in `.htaccess`.

## After it's live

- **Google Search Console**: add your domain, then submit `https://your-domain/sitemap.xml`.
- **Share preview**: paste your address into a WhatsApp chat or the LinkedIn post box to check the preview image.
- **Clean addresses**: `/calculator`, `/tax-guide` etc. also work thanks to `.htaccess`; the site's own links use `.html` so it also works when opened from your computer.

## Editing

- **Header and footer** are repeated in every HTML file (there is no server to include them). If you change a menu link, change it in all seven pages. Replace-in-files makes this quick.
- **Theme colour**: change `data-ew-theme="tax"` on `<div class="ew-hero">` in each page. Options: ocean, mint, violet, sunset, midnight, wealth, ledger, tax, capital, private, vault.
- **Tax figures**: all in the `TAX` object at the top of `assets/js/calculator.js`. The tax guide, how-it-works page and home page quote the same figures in text, so update those too each year (the IRS publishes new numbers every October or November).
- **Home page example** ($12,037 on $60,000): if you change it, update `data-count` and the `.ew-sr` text in `index.html` and the `TOTAL` value in `assets/js/home.js`.

## What changed from the single-page version

- Split into shared CSS and JavaScript files, so browsers download the header and colours once for the whole site.
- The header's "Log in" and "Sign up" were removed, because the site has no accounts. The header button is now "Calculate my tax" ("Calculate" on phones).
- The "Blog" menu link was replaced by "2026 tax guide" and "FAQ", which have real pages behind them.
- New: footer on every page, skip-to-content link for keyboard users, home page sections below the hero, guide pages, privacy, terms, 404, icons, share image, sitemap and `.htaccess`.

## Before you promote it

- Have a tax professional review the calculator, the guide and the disclaimer.
- Test on a phone, a tablet and a desktop after upload.
