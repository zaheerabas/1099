<?php
declare(strict_types=1);

/**
 * Illustrated cover for articles without a cover image, in the style of the home page hero cards.
 * The picture depends on the category: quarterly taxes, self-employment tax, deductions, or a gauge for anything else.
 */
function cover_art(?string $categorySlug, string $size = 'card'): string
{
    $kind = in_array($categorySlug, ['quarterly-taxes', 'self-employment-tax', 'deductions'], true) ? $categorySlug : 'default';
    $svg = match ($kind) {
        'quarterly-taxes' => '<svg viewBox="0 0 320 200" aria-hidden="true" focusable="false">'
            . '<path class="cv-grid" d="M30 150H290M30 110H290M30 70H290"/>'
            . '<path class="cv-area" d="M30 150 C90 140 110 112 160 104 S240 70 290 52 V150Z"/>'
            . '<path class="cv-line" d="M30 150 C90 140 110 112 160 104 S240 70 290 52"/>'
            . '<g class="cv-q"><rect x="34" y="20" width="54" height="30" rx="15"/><text x="61" y="40">Q1</text></g>'
            . '<g class="cv-q"><rect x="100" y="20" width="54" height="30" rx="15"/><text x="127" y="40">Q2</text></g>'
            . '<g class="cv-q"><rect x="166" y="20" width="54" height="30" rx="15"/><text x="193" y="40">Q3</text></g>'
            . '<g class="cv-q cv-q--on"><rect x="232" y="20" width="54" height="30" rx="15"/><text x="259" y="40">Q4</text></g>'
            . '<circle class="cv-dot" cx="160" cy="104" r="7"/><circle class="cv-dot" cx="290" cy="52" r="7"/>'
            . '</svg>',
        'self-employment-tax' => '<svg viewBox="0 0 320 200" aria-hidden="true" focusable="false">'
            . '<circle class="cv-track" cx="110" cy="100" r="62"/>'
            . '<circle class="cv-ring" cx="110" cy="100" r="62" pathLength="153" stroke-dasharray="124 153" transform="rotate(-90 110 100)"/>'
            . '<circle class="cv-ring cv-ring--2" cx="110" cy="100" r="62" pathLength="153" stroke-dasharray="29 153" stroke-dashoffset="-124" transform="rotate(-90 110 100)"/>'
            . '<text class="cv-big" x="110" y="108">15.3%</text>'
            . '<g class="cv-key"><rect x="200" y="62" width="14" height="14" rx="4" class="cv-sw"/><text x="222" y="74">12.4%</text><text class="cv-sub" x="222" y="92">Social Security</text></g>'
            . '<g class="cv-key"><rect x="200" y="112" width="14" height="14" rx="4" class="cv-sw cv-sw--2"/><text x="222" y="124">2.9%</text><text class="cv-sub" x="222" y="142">Medicare</text></g>'
            . '</svg>',
        'deductions' => '<svg viewBox="0 0 320 200" aria-hidden="true" focusable="false">'
            . '<path class="cv-paper" d="M96 22h128v152l-16-10-16 10-16-10-16 10-16-10-16 10-16-10-16 10z"/>'
            . '<path class="cv-rule" d="M114 53h38M114 83h30M114 113h42"/>'
            . '<text class="cv-amt" x="210" y="58">−$540</text><text class="cv-amt" x="210" y="88">−$1,500</text><text class="cv-amt" x="210" y="118">−$320</text>'
            . '<circle class="cv-badge" cx="236" cy="148" r="24"/><path class="cv-tick" d="m225 148 8 8 15-16"/>'
            . '</svg>',
        default => '<svg viewBox="0 0 320 200" aria-hidden="true" focusable="false">'
            . '<path class="cv-track" d="M90 140a70 70 0 0 1 140 0"/>'
            . '<path class="cv-ring" d="M90 140a70 70 0 0 1 112-56"/>'
            . '<path class="cv-needle" d="M160 140l34-46"/><circle class="cv-dot" cx="160" cy="140" r="9"/>'
            . '</svg>',
    };
    return '<span class="cv cv--' . $kind . ' cv--' . $size . '">' . $svg . '</span>';
}
