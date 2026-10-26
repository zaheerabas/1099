/* taxmetter: 2026 federal tax maths for 1099 workers. Used by the calculator and the home page estimate.
   All tax figures are in the TAX object below; update them here each year. */
(function () {
    /* ================= 1099 tax calculation (tax year 2026) ================= */
    var TAX = {
      year: 2026,
      // Rev. Proc. 2025-32: [upper limit of bracket, rate]
      brackets: {
        single: [[12400,.10],[50400,.12],[105700,.22],[201775,.24],[256225,.32],[640600,.35],[Infinity,.37]],
        mfj:    [[24800,.10],[100800,.12],[211400,.22],[403550,.24],[512450,.32],[768700,.35],[Infinity,.37]],
        mfs:    [[12400,.10],[50400,.12],[105700,.22],[201775,.24],[256225,.32],[384350,.35],[Infinity,.37]],
        hoh:    [[17700,.10],[67450,.12],[105700,.22],[201750,.24],[256200,.32],[640600,.35],[Infinity,.37]]
      },
      stdDeduction: { single: 16100, mfj: 32200, mfs: 16100, hoh: 24150 },
      ssWageBase: 184500,                 // Social Security wage base for 2026
      addlMedicare: { single: 200000, mfj: 250000, mfs: 125000, hoh: 200000 },
      mileH1: 0.725, mileH2: 0.76,        // business mileage: Jan–Jun, Jul–Dec 2026
      homeOfficeRate: 5, homeOfficeMax: 300,
      qbiRate: 0.20, qbiMin: 400, qbiMinBase: 1000,   // OBBBA minimum: $400 if business income is $1,000+
      qbiThreshold: { single: 201750, mfj: 403500, mfs: 201775, hoh: 201750 },
      dueDates: [['Q1','2026-04-15'],['Q2','2026-06-15'],['Q3','2026-09-15'],['Q4','2027-01-15']]
    };

    function incomeTax(taxable, status) {
      var tax = 0, lower = 0, rate = 0;
      TAX.brackets[status].some(function (b) {
        if (taxable > lower) { tax += (Math.min(taxable, b[0]) - lower) * b[1]; rate = b[1]; }
        lower = b[0];
        return taxable <= b[0];
      });
      return { tax: tax, rate: rate };
    }

    function calculate(v) {
      var status = v.status;
      var gross1099 = v.nec + v.k + v.misc;
      var homeOffice = Math.min(v.sqft, TAX.homeOfficeMax) * TAX.homeOfficeRate;
      var mileage = v.mi1 * TAX.mileH1 + v.mi2 * TAX.mileH2;
      var beforeOffice = gross1099 - v.exp - mileage;
      homeOffice = Math.max(0, Math.min(homeOffice, beforeOffice)); // simplified method can't create a loss
      var expenses = v.exp + mileage + homeOffice;
      var profit = gross1099 - expenses;                             // Schedule C net profit (can be negative)

      // Self-employment tax (Schedule SE)
      var seBase = Math.max(0, profit) * 0.9235;
      if (seBase < 400) seBase = 0;
      var ssRoom = Math.max(0, TAX.ssWageBase - v.w2);
      var ss = Math.min(seBase, ssRoom) * 0.124;
      var medicare = seBase * 0.029;
      var seTax = ss + medicare;
      var halfSE = seTax / 2;

      // Additional Medicare tax (0.9%) on wages + SE earnings above the threshold
      var addl = Math.max(0, v.w2 + seBase - TAX.addlMedicare[status]) * 0.009;

      // Above-the-line deductions
      var health = Math.max(0, Math.min(v.health, Math.max(0, profit) - halfSE));
      // SEP-IRA / solo 401(k): can't be more than net profit after half SE tax and health insurance
      var ret = Math.max(0, Math.min(v.ret, Math.max(0, profit) - halfSE - health));
      var agi = v.w2 + v.int + profit - halfSE - health - ret;
      var std = TAX.stdDeduction[status];
      var beforeQBI = Math.max(0, agi - std);
      var qbiBase = Math.max(0, profit - halfSE - health - ret);
      var qbi = Math.min(qbiBase * TAX.qbiRate, beforeQBI * TAX.qbiRate);
      if (qbiBase >= TAX.qbiMinBase) qbi = Math.max(qbi, TAX.qbiMin);
      var taxable = Math.max(0, beforeQBI - qbi);
      var it = incomeTax(taxable, status);

      var total = seTax + it.tax + addl;
      var paid = v.wh + v.paid;
      var afterWithholding = Math.max(0, total - v.wh);
      var quarterly = afterWithholding / 4;
      // Least you can pay (withholding + estimates) to avoid an underpayment penalty:
      // the smaller of 90% of this year's tax or 100% of last year's (110% if last year's AGI was high)
      var safeHarbor = v.prior > 0 ? Math.min(total * 0.9, v.prior * (v.hi ? 1.1 : 1)) : total * 0.9;
      var totalIncome = gross1099 + v.w2 + v.int;
      return {
        gross1099: gross1099, expenses: expenses, mileage: mileage, homeOffice: homeOffice, profit: profit,
        seBase: seBase, seTax: seTax, halfSE: halfSE, addl: addl, health: health, ret: ret, safeHarbor: safeHarbor, agi: agi, std: std, qbi: qbi,
        taxable: taxable, incomeTax: it.tax, marginal: it.rate, total: total, paid: paid,
        owe: total - paid, quarterly: quarterly, afterWithholding: afterWithholding,
        setAside: gross1099 > 0 ? Math.max(0, total - v.wh) / gross1099 : 0,
        effective: totalIncome > 0 ? total / totalIncome : 0,
        qbiLimited: beforeQBI > TAX.qbiThreshold[status]
      };
    }


    window.TMTax = { TAX: TAX, calculate: calculate, incomeTax: incomeTax };
})();
