# taxmetter: free 1099 tax calculator

taxmetter is a website that helps freelancers, contractors and gig workers in the US work out how much federal tax they owe on their 1099 income, and how much to set aside each quarter.

This repository holds the whole website.

---

## Why this exists

If you're employed, your employer takes tax out of every paycheck. If you're paid on a 1099, nobody does. You have to:

- work out your own **self-employment tax** (Social Security and Medicare, 15.3%) on top of income tax,
- pay it yourself in **four quarterly estimated payments**, and
- know which expenses lower the bill.

Most people don't know what that adds up to until April, when it's too late to plan for it. Many online calculators ask for an email, push a paid product, or only give a rough percentage.

taxmetter answers the question directly: **"I earned this much on 1099s. What will I owe, and what should I put aside right now?"** It's free, needs no sign-up, and every number is explained.

---

## Who it's for

- Freelancers and independent contractors (1099-NEC)
- Gig and platform workers paid through apps and marketplaces (1099-K)
- Anyone with other 1099 income such as 1099-MISC or interest (1099-INT)
- People with a day job **and** side income, since W-2 wages are taken into account

---

## What the website does

### 1099 tax calculator
The main page. The visitor enters:

1. **Filing status**: single, married filing jointly, married filing separately or head of household
2. **1099 income**, by form type
3. **Business expenses**: total expenses, home office, health insurance and business miles
4. **Optional extras**: W-2 wages, tax already withheld or paid, retirement contributions and last year's tax

The result updates as they type and shows:

- total estimated federal tax for the year, split into self-employment tax, income tax and Additional Medicare tax
- how much to pay each quarter, and the next due date
- how much of each 1099 payment to set aside
- the **penalty-safe minimum** (the least they can pay to avoid an IRS underpayment penalty)
- how much an extra $1,000 of business expenses would save
- a line-by-line "How we calculated this" table

Answers are kept on the visitor's own device. They can share their answers as a link or print the result.

### Supporting pages
| Page | Purpose |
|---|---|
| Home | Explains the idea in three steps (add your 1099s, we calculate, see what to save) and links to the calculator |
| How it works | The calculation method, step by step, with a worked example |
| 2026 tax guide | Brackets, standard deduction, self-employment tax, deductions and quarterly due dates for 1099 workers |
| Privacy | Explains that the calculator runs in the browser and sends no numbers anywhere |
| Terms | Terms of use and the tax disclaimer |

---

## Privacy by design

There are no accounts, no database and no server-side code. All the maths runs in the visitor's browser. Their income and expense figures never leave their device.

---

## What it covers, and what it doesn't

**Covers (tax year 2026):** federal income tax brackets and standard deduction, self-employment tax, Additional Medicare tax, the self-employed health insurance deduction, retirement contributions (SEP-IRA / solo 401(k)), the 20% business income (QBI) deduction, home office (simplified method), business mileage and quarterly estimated payments.

**Doesn't cover:** state and local tax, itemized deductions, tax credits such as the child tax credit, capital gains, and the detailed QBI limits at high incomes.

The result is an **estimate** to help people plan. It isn't tax advice, and the site says so.

---

## How the repository is organised

```
public_html/          the website itself
  index.html          home page
  calculator.html     1099 tax calculator and FAQ
  how-it-works.html   calculation method
  tax-guide.html      2026 tax guide
  privacy.html, terms.html, 404.html
  assets/css/         styles (shared, home page, calculator)
  assets/js/          scripts (shared, home page animation, calculator)
  assets/img/         icons and share image
vercel.json           tells Vercel to serve the public_html folder
```

It's plain HTML, CSS and JavaScript, with no frameworks and no build step. You can open any page straight from the folder in a browser.

---

## Keeping it up to date

Tax figures change every year. Everything the calculator uses (brackets, standard deduction, Social Security wage base, mileage rates and due dates) is in one place: the `TAX` object at the top of `public_html/assets/js/calculator.js`.

The tax guide, How it works and home pages quote the same figures in their text, so update those at the same time. The IRS usually publishes next year's numbers in October or November.

Before promoting the site, have a tax professional review the calculator, the guide and the disclaimer.
