# taxmetter — 1099 calculator hero + calculator page

Two pieces that work together:

```
taxmetter-hero.html   ← header + hero snippet (style + markup + script), paste into your home page
calculator.html       ← the full 1099 calculator page, a complete standalone page
theme-preview.html    ← open in a browser to try the hero in all eleven colour themes
README.md             ← this file
```

Plain HTML, CSS and vanilla JavaScript. No frameworks, no build step. The only external resource is Google Fonts.

---

## How they connect

Every call to action in the hero opens the calculator:

| In the hero | Links to |
|---|---|
| "Calculate my tax" button | `calculator.html` |
| "Open calculator" button (1099 forms card) | `calculator.html` |
| Arrow button (Tax answers card) | `calculator.html` |
| "1099 calculator" menu link | `calculator.html` |

Put `calculator.html` in the same folder as the page that holds the hero. If you rename it or use a clean URL such as `/1099-calculator`, search both files for `calculator.html` and replace it.

---

## Quick start

**Hero**
1. Your page `<head>` needs `<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">`.
2. Copy the two Google Fonts `<link>` lines from the top of `taxmetter-hero.html` into your `<head>`.
3. Paste the `<style>`, `<section>` and `<script>` blocks at the very top of `<body>`.
4. Remove your site's own header on that page, because the snippet has its own.

**Calculator**
Upload `calculator.html` as it is. It already contains the same header, colours and fonts as the hero.
On WordPress, use a blank / full-width page template and paste everything between `<body>` and `</body>` into a Custom HTML block (plus the `<style>` block).

---

## What changed from the original hero

The layout, animation, header and themes are unchanged. Only the content is new.

| Part | Now |
|---|---|
| Headline | "Add. Calculate. Save" |
| Taglines | One per step: add your 1099s, we calculate, see what to save |
| Call to action | "Calculate my tax" + "Free, no sign-up. Uses 2026 IRS rates." |
| Card 1 (step 3, Save) | **Your tax estimate**: $12,037 for the year, $3,009 per quarter, a graph of tax owed by quarter you can scrub |
| Card 2 (step 1, Add) | **Your 1099 forms**: NEC, K, MISC and INT circles feeding into the gauge, "4 added" badge, "Open calculator" button |
| Card 3 (step 2, Calculate) | **Tax answers**: three common freelancer questions and "Find your deductions and know what you owe" |
| Menu | 1099 calculator, How it works, Tax guide, Blog |

The numbers in the hero card are a real example: **$60,000 of 1099-NEC income, single, no expenses** gives $12,037 in the calculator. If you change them, change both the visible `.ew-sr` text and the `data-count` value, and the `TOTAL` value in the hero script (it scales the graph tooltip).

The four form circles keep their own colours in every theme. Hover one to see what that form is for.

---

## The calculator

### What it asks
1. **Filing status**: single, married filing jointly, married filing separately, head of household
2. **1099 income**: 1099-NEC, 1099-K, 1099-MISC (self-employment income) and 1099-INT (interest, income tax only)
3. **Business expenses**: a total, home office (simplified method), health insurance premiums, and business miles split into Jan–Jun and Jul–Dec
4. **Payments and savings (optional)**: W-2 wages, tax withheld, estimated payments already made, SEP-IRA / solo 401(k) contributions, last year's total tax, and whether last year's AGI was over $150,000

The result updates as the visitor types. Answers are remembered on the visitor's device, so they're still there after a reload. "Clear all" removes them. On phones and tablets a small bar at the bottom shows the total and jumps to the breakdown.

### What it shows
Total estimated federal tax, the split between self-employment tax, income tax and Additional Medicare tax, the amount to pay each quarter, the share of each 1099 payment to set aside, what's still owed after payments, the four 2026 due dates (the next one is highlighted automatically from today's date), and a line-by-line "How we calculated this" table.

It also shows:
- **Payments that fit the date.** What's still owed is spread over the due dates that haven't passed, so late in the year it shows the next payment instead of a quarter of the annual total. Each due date shows its amount.
- **The penalty-safe minimum.** The least the visitor can pay to avoid an underpayment penalty: the smaller of 90% of this year's tax or 100% of last year's (110% if last year's AGI was over $150,000).
- **What an extra $1,000 of expenses saves.** Worked out from the visitor's own numbers.
- **Copy link** puts all answers into a link (for example `calculator.html?status=single&nec=60000`) that opens the calculator filled in. **Print or save PDF** prints only the result card.

### Questions section and SEO
Below the calculator are six common 1099 questions that open and close. The page has a title, description, Open Graph tags and structured data (WebApplication + FAQPage) so search engines can show it as a free calculator with its questions.

Replace `https://YOUR-DOMAIN.com/calculator.html` in the `<link rel="canonical">` tag with the real address.

The three question bubbles in the hero's Tax answers card link straight to their answers (for example `calculator.html#faq-set-aside` opens that question).

### Tax rules used (tax year 2026)
| Item | Value |
|---|---|
| Income tax brackets and standard deduction | Rev. Proc. 2025-32 ($16,100 single / $32,200 joint / $24,150 head of household) |
| Self-employment tax | 15.3% on 92.35% of net profit; none if net earnings are under $400 |
| Social Security wage base | $184,500 (reduced by any W-2 wages) |
| Additional Medicare tax | 0.9% above $200,000 / $250,000 joint / $125,000 separate |
| Half of SE tax | Deducted from income |
| Retirement contributions | Deducted from income (not from SE tax), up to net profit minus half SE tax and health insurance |
| Self-employed health insurance | Deducted, up to net profit minus half SE tax |
| Business income (QBI) deduction | 20%, capped at 20% of taxable income; $400 minimum with $1,000+ of business income. Shows a note above $201,750 ($403,500 joint) where limits can apply |
| Home office | $5 per sq ft, up to 300 sq ft, can't create a loss |
| Mileage | 72.5¢ (Jan–Jun) and 76¢ (Jul–Dec) per mile |
| Quarterly payment | (total tax − withholding − payments made) ÷ due dates still ahead. Shows a note if under $1,000 is owed |
| Penalty-safe minimum | Smaller of 90% of 2026 tax or 100% / 110% of 2025 tax |

All figures are in the `TAX` object at the top of the calculator script, so next year's update is one place.

### Not included
State and local tax, itemized deductions, tax credits (child tax credit and others), capital gains, and the detailed QBI limits for high incomes. The page says this in its disclaimer. Have a tax professional review the page before you promote it.

---

## Themes

Both files use `data-ew-theme="tax"`. Change it on `<section class="ew-hero">` in the hero and on `<div class="ew-hero">` in the calculator, keeping both the same. Options: `ocean`, `mint`, `violet`, `sunset`, `midnight`, `wealth`, `ledger`, `tax`, `capital`, `private`, `vault`. Open `theme-preview.html` to compare them.

---

## Accessibility and performance

Unchanged from the original hero: pause control, reduced-motion support, keyboard focus rings, screen-reader labels, only `transform`/`opacity` animated, looping motion paused off-screen.

On the calculator: every field has a visible label and hint, the total is announced to screen readers when it changes, the filing status is a real radio group, and the page prints as a clean one-page estimate.

---

## Before going live

- [ ] Put your real address in the canonical link at the top of `calculator.html`
- [ ] Point "How it works", "Tax guide", "Blog", "Log in" and "Sign up" to real pages, or remove them
- [ ] Check the `calculator.html` links if you use a different file name or URL
- [ ] Have a tax professional review the calculator and the disclaimer
- [ ] Choose a theme and use the same one on both pages
- [ ] Test on a phone, a tablet and a desktop
- [ ] Update the `TAX` figures each year (IRS releases them every October or November)
