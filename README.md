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
| Home | Explains the idea in three steps, with a **quick estimate** (sliders that open the full calculator pre-filled), the 2026 key figures, a **payment calendar** counting down to the next due date, and the latest blog articles |
| FAQ | 25 common 1099 tax questions in five topics, with instant search and a shareable link to each answer |
| How it works | The calculation method, step by step, with a worked example |
| 2026 tax guide | Brackets, standard deduction, self-employment tax, deductions and quarterly due dates for 1099 workers |
| Privacy | Explains that the calculator runs in the browser and sends no numbers anywhere |
| Terms | Terms of use and the tax disclaimer |

### Blog
Articles about 1099 taxes, written and published by the taxmetter team from a private admin area. Readers can browse by category, search, and follow along by RSS. The team can schedule articles, add images, and give people editor or administrator access.
---

## Privacy by design

Visitors never need an account. All the tax maths runs in the visitor's browser, so their income and expense figures never leave their device. The only data the site stores is the blog: its articles, images and the logins of the people who write them.

---

## What it covers, and what it doesn't

**Covers (tax year 2026):** federal income tax brackets and standard deduction, self-employment tax, Additional Medicare tax, the self-employed health insurance deduction, retirement contributions (SEP-IRA / solo 401(k)), the 20% business income (QBI) deduction, home office (simplified method), business mileage and quarterly estimated payments.

**Doesn't cover:** state and local tax, itemized deductions, tax credits such as the child tax credit, capital gains, and the detailed QBI limits at high incomes.

The result is an **estimate** to help people plan. It isn't tax advice, and the site says so.
