{{-- Official Kliq wordmark. Letters follow currentColor; the dot keeps the brand orange. Each glyph carries --i for staggered reveals. --}}
<svg {{ $attributes->merge(['class' => 'logo inline-block h-[1em] w-auto shrink-0']) }} viewBox="497 610 543 317" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Kliq">
    <title>Kliq</title>
    <g class="logo__glyph" style="--i: 0" fill="currentColor">
        <path d="M497 621H544V745C544 745 553 746 556 746C590 746 611 721 615 688H663C659 720 642 751 616 768C645 784 667 818 663 863H615C618 815 594 784 556 787L544 788V863H497Z" />
    </g>
    <g class="logo__glyph" style="--i: 1" fill="currentColor">
        <rect x="696" y="621" width="46" height="242" />
    </g>
    <g class="logo__glyph" style="--i: 2" fill="currentColor">
        <rect x="775" y="688" width="45" height="175" />
        <circle class="logo__dot" fill="#FF5B00" cx="797" cy="638" r="28" />
    </g>
    <g class="logo__glyph" style="--i: 3" fill="currentColor">
        <path fill-rule="evenodd" d="M1040 688V927H992V842C985 850 975 868 937 868C890 868 851 830 851 775C851 728 889 683 935 683C960 683 978 694 994 709L997 688ZM946 725C975 725 991 750 991 776C991 803 974 825 946 825C917 825 901 804 901 776C901 748 918 725 946 725Z" />
    </g>
</svg>
