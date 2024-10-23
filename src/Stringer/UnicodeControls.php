<?php namespace Nabeghe\Stringer;

/**
 * A class that includes some Unicode control characters;
 * for example, the invisible character, or the right-to-left and left-to-right markers.
 */
class UnicodeControls
{
    /**
     * Invisible.
     */
    public const ISS = '⁪';

    /**
     * Left to Right.
     */
    public const LRM = '‎';

    /**
     * Right to Left.
     */
    public const RLM = '‏';

    /**
     * Zero width joiner.
     */
    public const ZWJ = '‍';

    /**
     * Zero width none-joiner.
     */
    public const ZWNJ = '‌';

    /**
     * Zero width space.
     */
    public const ZWSP = '​';

    /**
     * Start of left-to-right embedding.
     */
    public const LRE = '‪';

    /**
     * Start of right-to-left embedding.
     */
    public const RLE = '‫';

    /**
     * Start of left-to-right override.
     */
    public const LRO = '‭';

    /**
     * Start of right-to-left override.
     */
    public const RLO = '‮';

    /**
     * Pop directional formatting.
     */
    public const PDF = '‬';

    /**
     * Left-to-right isolate.
     */
    public const LRI = '⁦';

    /**
     * Right-to-left isolate.
     */
    public const RTI = '⁧';

    /**
     * First strong isolate.
     */
    public const FSI = '⁨';

    /**
     * Pop directional isolate.
     */
    public const PDI = '⁩';
}