<?php
declare(strict_types=1);

/**
 * Example articles that install.php can publish so the blog isn't empty on day one.
 * Edit or delete them from the admin like any other article.
 * 'category' is a category slug created by the installer; 'days_ago' sets the publish date.
 * Figures match tax year 2026 and the TAX object in assets/js/tax-engine.js.
 */
return [
    [
        'title' => 'How much should I set aside for taxes on 1099 income?',
        'slug' => 'how-much-to-set-aside-for-1099-taxes',
        'category' => 'quarterly-taxes',
        'days_ago' => 3,
        'featured' => 1,
        'excerpt' => 'Setting aside 20–30% is a good start, but your real number depends on your income, expenses and filing status. Here’s how to work it out.',
        'seo_description' => 'How much of each 1099 payment to save for taxes in 2026, with a worked example for $60,000 of freelance income.',
        'content' => <<<'HTML'
<p>When you’re paid on a 1099, nobody takes tax out of your pay. The money lands in your account in full, and it’s up to you to keep enough back for the IRS. Save too little and April hurts. Save too much and you’re short of cash all year.</p>
<h2>The quick answer</h2>
<p>Most freelancers with a moderate income should set aside <strong>between 20% and 30%</strong> of every 1099 payment for federal tax. Add your state’s income tax on top if you have one.</p>
<p>That range is wide because two different taxes are involved, and they work in different ways.</p>
<h2>The two taxes you owe</h2>
<ul>
<li><strong>Self-employment tax</strong> is 15.3% on 92.35% of your net profit. It pays for Social Security and Medicare, and it applies from the first dollar of profit (once your net earnings reach $400).</li>
<li><strong>Federal income tax</strong> uses the same brackets as everyone else. You only pay it on income above your standard deduction ($16,100 for a single filer in 2026), so the first part of your profit is income-tax free.</li>
</ul>
<p>Because self-employment tax is a flat rate and income tax is progressive, your set-aside percentage rises as your income rises.</p>
<h2>A worked example</h2>
<p>Say you’re single, earned $60,000 on 1099-NEC forms in 2026, and had no expenses.</p>
<ul>
<li>Self-employment tax: $60,000 × 92.35% × 15.3% = <strong>$8,478</strong></li>
<li>Income tax, after deducting half of the self-employment tax, the $16,100 standard deduction and the 20% business income deduction: <strong>$3,559</strong></li>
<li>Total: <strong>$12,037</strong>, or about <strong>20%</strong> of what you earned</li>
</ul>
<p>Paid in four equal installments, that’s about $3,009 a quarter.</p>
<h2>What changes your number</h2>
<ul>
<li><strong>Expenses.</strong> Every dollar of business expenses lowers both taxes. At this income, $1,000 of expenses saves about $230.</li>
<li><strong>A day job.</strong> W-2 wages fill up the lower income tax brackets first, so your 1099 income is taxed at a higher rate.</li>
<li><strong>Filing status.</strong> Married couples filing jointly get a bigger standard deduction and wider brackets.</li>
<li><strong>Retirement savings.</strong> SEP-IRA or solo 401(k) contributions lower income tax (but not self-employment tax).</li>
</ul>
<h2>A simple habit that works</h2>
<p>Open a separate savings account just for tax. Every time a client pays you, move your percentage across straight away. When a quarterly due date comes round, the money is already waiting.</p>
<p>To get your own percentage, put your numbers into the <a href="/calculator.html">1099 tax calculator</a>. It shows exactly how much of each payment to keep.</p>
HTML,
    ],
    [
        'title' => 'Self-employment tax explained: what the 15.3% really pays for',
        'slug' => 'self-employment-tax-explained',
        'category' => 'self-employment-tax',
        'days_ago' => 9,
        'featured' => 0,
        'excerpt' => 'Self-employment tax is often the biggest tax a new freelancer pays. Here’s what it is, how it’s worked out, and the legal ways to lower it.',
        'seo_description' => 'What self-employment tax is, how the 15.3% is calculated on 92.35% of profit, the 2026 Social Security wage base and how to reduce it.',
        'content' => <<<'HTML'
<p>New freelancers are often surprised that their tax bill is bigger than it was as an employee, even on the same income. The reason is self-employment tax.</p>
<h2>What it is</h2>
<p>Every worker pays into Social Security and Medicare. As an employee you pay 7.65% and your employer quietly pays another 7.65%. When you work for yourself, you’re both the employee and the employer, so you pay both halves: <strong>15.3%</strong>.</p>
<ul>
<li><strong>12.4%</strong> goes to Social Security</li>
<li><strong>2.9%</strong> goes to Medicare</li>
</ul>
<h2>How it’s calculated</h2>
<p>The tax isn’t charged on your whole profit. First, multiply your net profit by <strong>92.35%</strong>. This mirrors the fact that employees don’t pay tax on their employer’s share. Then apply 15.3% to the result.</p>
<p>For $50,000 of profit: $50,000 × 92.35% = $46,175, and $46,175 × 15.3% = <strong>$7,065</strong>.</p>
<p>If your net earnings from self-employment are under $400 for the year, you don’t owe self-employment tax at all.</p>
<h2>The Social Security cap</h2>
<p>The 12.4% Social Security part only applies up to the <strong>wage base</strong>, which is <strong>$184,500</strong> in 2026. Above that, only the 2.9% Medicare part continues.</p>
<p>If you also have a W-2 job, your wages count towards that cap first. A high earner with a day job may pay little or no Social Security tax on side income.</p>
<p>At higher incomes there’s also an <strong>Additional Medicare tax</strong> of 0.9% on earnings above $200,000 for single filers ($250,000 married filing jointly).</p>
<h2>The deduction that helps</h2>
<p>You can deduct <strong>half of your self-employment tax</strong> from your income before income tax is worked out. It doesn’t lower the self-employment tax itself, but it does lower your income tax.</p>
<h2>Ways to lower it</h2>
<ul>
<li><strong>Claim every business expense.</strong> Self-employment tax is charged on profit, so expenses lower it directly. See our list of <a href="/blog/deductions-freelancers-often-miss">deductions freelancers often miss</a>.</li>
<li><strong>Keep good records.</strong> Mileage logs and receipts turn into real savings.</li>
<li><strong>Know what doesn’t help.</strong> Retirement contributions and the standard deduction lower income tax, but not self-employment tax.</li>
</ul>
<p>The <a href="/calculator.html">calculator</a> shows your self-employment tax and income tax separately, so you can see where your bill comes from.</p>
HTML,
    ],
    [
        'title' => 'Quarterly estimated taxes for 2026: due dates and how much to pay',
        'slug' => 'quarterly-estimated-taxes-2026',
        'category' => 'quarterly-taxes',
        'days_ago' => 16,
        'featured' => 0,
        'excerpt' => 'If you expect to owe $1,000 or more, the IRS wants tax four times a year, not once in April. Here are the 2026 dates and the rules that keep you penalty-free.',
        'seo_description' => '2026 quarterly estimated tax due dates for freelancers, how much to pay each quarter, and the safe harbor rules that avoid an underpayment penalty.',
        'content' => <<<'HTML'
<p>The US tax system is pay-as-you-go. Employees pay through withholding every paycheck. If you’re self-employed, you do the same thing yourself with <strong>estimated tax payments</strong>.</p>
<h2>Who needs to pay</h2>
<p>You generally need to make estimated payments if you expect to owe <strong>$1,000 or more</strong> in federal tax for the year after subtracting any withholding. That’s most freelancers with a few thousand dollars of profit or more.</p>
<h2>The 2026 due dates</h2>
<ul>
<li><strong>Q1:</strong> April 15, 2026 (income from January to March)</li>
<li><strong>Q2:</strong> June 15, 2026 (April and May)</li>
<li><strong>Q3:</strong> September 15, 2026 (June to August)</li>
<li><strong>Q4:</strong> January 15, 2027 (September to December)</li>
</ul>
<p>The periods aren’t equal quarters, which catches people out. Q2 covers only two months but is due just two months after Q1.</p>
<h2>How much to pay</h2>
<p>The simplest approach is to estimate your total tax for the year and divide it by four. If your income is uneven, you can pay more in the quarters you earn more.</p>
<p>If you start late in the year, spread what you still owe over the due dates that are left. The <a href="/calculator.html">calculator</a> does this automatically and highlights your next payment.</p>
<h2>Avoiding the underpayment penalty</h2>
<p>You won’t owe a penalty if, through withholding and estimated payments, you pay at least the <strong>smaller</strong> of:</p>
<ul>
<li><strong>90%</strong> of your 2026 tax, or</li>
<li><strong>100%</strong> of your 2025 tax (<strong>110%</strong> if your 2025 adjusted gross income was over $150,000)</li>
</ul>
<p>The second option is useful when your income is growing: you can base your payments on last year’s known bill and settle the rest in April without a penalty.</p>
<h2>How to pay</h2>
<p>You can pay online through IRS Direct Pay or your IRS online account, through the Electronic Federal Tax Payment System (EFTPS), or by mailing a check with a Form 1040-ES voucher. Online payments give you an instant confirmation, which is worth keeping.</p>
<h2>If you miss one</h2>
<p>Pay as soon as you can. The penalty works like interest, growing with the amount and the number of days you’re late, so a payment a few weeks late costs far less than skipping it.</p>
HTML,
    ],
    [
        'title' => '9 tax deductions freelancers often miss',
        'slug' => 'deductions-freelancers-often-miss',
        'category' => 'deductions',
        'days_ago' => 23,
        'featured' => 0,
        'excerpt' => 'Every legitimate business expense lowers both your income tax and your self-employment tax. These are the ones that most often get left off.',
        'seo_description' => 'Nine common deductions 1099 contractors and freelancers miss, from mileage and home office to health insurance and retirement contributions.',
        'content' => <<<'HTML'
<p>As a 1099 worker, a business expense saves you more than it would an employee, because it lowers your self-employment tax as well as your income tax. On $60,000 of freelance income, each $1,000 of expenses saves about $230, and more at higher incomes.</p>
<p>The rule is simple: an expense must be <strong>ordinary and necessary</strong> for your work. Here are nine that are easy to forget.</p>
<h2>1. Business mileage</h2>
<p>Driving to clients, job sites, the bank or the post office for work all count. For 2026 the standard rate is <strong>72.5¢ a mile</strong> from January to June and <strong>76¢</strong> from July. Keep a log with the date, purpose and miles. Commuting to a regular workplace doesn’t count.</p>
<h2>2. Home office</h2>
<p>If you use part of your home regularly and only for work, the simplified method gives <strong>$5 per square foot, up to 300 square feet</strong>. Read more in our <a href="/blog/home-office-deduction-simplified-vs-regular">home office guide</a>.</p>
<h2>3. Phone and internet</h2>
<p>The business share of your phone and home internet bills is deductible. If you use them half for work, deduct half.</p>
<h2>4. Software and subscriptions</h2>
<p>Accounting software, design tools, cloud storage, website hosting, your domain name and paid professional apps all count.</p>
<h2>5. Equipment</h2>
<p>A laptop, monitor, camera or tools you buy for work can usually be deducted in the year you buy them.</p>
<h2>6. Health insurance premiums</h2>
<p>If you pay for your own health insurance and can’t join an employer plan (yours or your spouse’s), the premiums are deductible from your income.</p>
<h2>7. Retirement contributions</h2>
<p>Money you put into a SEP-IRA or solo 401(k) lowers your income tax and builds your savings at the same time.</p>
<h2>8. Fees and commissions</h2>
<p>Platform fees from marketplaces and payment processors are a business expense. Check your 1099-K: it often shows the gross amount before fees were taken.</p>
<h2>9. Learning and professional costs</h2>
<p>Courses that improve skills for your current work, industry books, professional memberships and the cost of a tax professional to prepare your business return.</p>
<h2>Keep the paperwork</h2>
<p>Save receipts and bank statements for at least three years. A separate bank account for your business makes this much easier.</p>
<p>Want to see what your expenses are worth? Add them to the <a href="/calculator.html">calculator</a>, which also shows how much an extra $1,000 of expenses would save you.</p>
HTML,
    ],
    [
        'title' => 'Home office deduction: simplified or regular method?',
        'slug' => 'home-office-deduction-simplified-vs-regular',
        'category' => 'deductions',
        'days_ago' => 31,
        'featured' => 0,
        'excerpt' => 'There are two ways to claim a home office. One takes five minutes, the other can be worth more. Here’s how to choose.',
        'seo_description' => 'Who qualifies for the home office deduction, how the simplified $5 per square foot method works, and when the regular method is worth the extra work.',
        'content' => <<<'HTML'
<p>If you work from home, part of the cost of your home may be a business expense. The IRS gives you two ways to claim it.</p>
<h2>Do you qualify?</h2>
<p>The space must be used <strong>regularly and exclusively</strong> for your business. A spare room that is only your office qualifies. A kitchen table you also eat at doesn’t. It must also be your main place of business, or a place you regularly meet clients.</p>
<h2>The simplified method</h2>
<ul>
<li>Deduct <strong>$5 for every square foot</strong> of office space</li>
<li>Up to <strong>300 square feet</strong>, so a maximum of <strong>$1,500</strong> a year</li>
<li>No receipts for rent, utilities or repairs needed</li>
</ul>
<p>A 120 sq ft office gives a $600 deduction. The deduction can’t be more than your business profit, so it can’t create a loss.</p>
<h2>The regular method</h2>
<p>Work out the share of your home used for business, for example 150 sq ft of a 1,500 sq ft home is 10%. Then deduct that share of your actual costs:</p>
<ul>
<li>Rent, or mortgage interest and depreciation if you own</li>
<li>Utilities, home insurance and repairs</li>
<li>Property tax</li>
</ul>
<p>Expenses only for the office itself, like painting that room, can be deducted in full.</p>
<h2>Which is better?</h2>
<p>The regular method is often worth more if you rent in an expensive area, or your office is larger than 300 sq ft. The simplified method wins on time, record-keeping and simplicity, and it avoids depreciation questions if you later sell a home you own.</p>
<p>A quick test: multiply your yearly rent and utilities by your office percentage. If the result is well above your simplified figure, the regular method may be worth the extra paperwork.</p>
<h2>Try it with your numbers</h2>
<p>The <a href="/calculator.html">calculator</a> uses the simplified method. Enter your office size to see how much it lowers your tax.</p>
HTML,
    ],
    [
        'title' => 'New 1099 thresholds for 2026: what changed and what didn’t',
        'slug' => '1099-thresholds-2026',
        'category' => 'self-employment-tax',
        'days_ago' => 38,
        'featured' => 0,
        'excerpt' => 'Clients now send a 1099-NEC at $2,000 instead of $600, and the 1099-K is back to $20,000. But one thing hasn’t changed: all your income is still taxable.',
        'seo_description' => 'The 2026 reporting thresholds for Form 1099-NEC, 1099-MISC and 1099-K, and why income below the threshold is still taxable.',
        'content' => <<<'HTML'
<p>The amounts that make a business send you a 1099 form have changed for 2026. It’s good news for paperwork, but it’s easy to misread.</p>
<h2>1099-NEC: now $2,000</h2>
<p>Clients send a <strong>Form 1099-NEC</strong> when they pay you for your work as a contractor. From 2026 they only have to send one if they pay you <strong>$2,000 or more</strong> in the year. It used to be $600.</p>
<h2>1099-MISC: also $2,000 for most payments</h2>
<p>The <strong>1099-MISC</strong>, used for rents, prizes and some other payments, moves to $2,000 for most types of payment too.</p>
<h2>1099-K: back to $20,000</h2>
<p>Payment apps, card processors and online marketplaces send a <strong>Form 1099-K</strong>. The threshold is <strong>over $20,000 and over 200 transactions</strong> in the year, after plans to lower it were reversed.</p>
<h2>The part that didn’t change</h2>
<p>These thresholds only decide <strong>whether a form is sent</strong>. They don’t change what’s taxable. If a client pays you $1,500, you won’t get a 1099-NEC, but the $1,500 is still income you must report.</p>
<p>In practice this means more freelancers will have income with no form behind it. Your own records matter more than ever.</p>
<h2>Tips for tracking income without forms</h2>
<ul>
<li>Use one bank account for all business income, so every payment is in one place.</li>
<li>Keep a simple income log: date, client and amount.</li>
<li>At year end, compare the forms you received with your own total. The forms will usually add up to less.</li>
</ul>
<h2>When you get a 1099-K for personal payments</h2>
<p>If a 1099-K includes money that isn’t business income, like friends paying you back for dinner, keep records showing what those payments were. You only pay tax on actual income.</p>
<p>Add up all your income, with or without forms, and enter it in the <a href="/calculator.html">calculator</a> to see the full picture.</p>
HTML,
    ],
];
