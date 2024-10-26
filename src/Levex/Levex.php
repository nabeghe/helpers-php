<?php namespace Nabeghe\Levex;

class Levex
{
    public const BASE_LEVEL_XP = 100;

    public const LEVEL_LOG_BASE = 3;

    public const PRICE_XP_RATE = 1;

    public const GOD_LEVEL_SYMBOLS = ['∞'];

    protected static self $instance;

    protected int $baseLevelXp = self::BASE_LEVEL_XP;

    protected int $levelLogBase = self::LEVEL_LOG_BASE;

    protected float $priceXpRate = self::PRICE_XP_RATE;

    protected array $godLevelSymbols = self::GOD_LEVEL_SYMBOLS;

    protected bool $negativeLevelIsGod = true;

    protected ?\Closure $levelNamesHandler = null;

    public function __construct(?array $options = [])
    {
        if (!$options) {
            $options = [];
        }

        if (defined('LEVEX_BASE_LEVEL_XP')) {
            $this->baseLevelXp = constant('LEVEX_BASE_LEVEL_XP');
        }
        if (defined('LEVEX_LEVEL_LOG_BASE')) {
            $this->levelLogBase = constant('LEVEX_LEVEL_LOG_BASE');
        }
        if (defined('LEVEX_PRICE_XP_RATE')) {
            $this->priceXpRate = constant('LEVEX_PRICE_XP_RATE');
        }
        if (defined('LEVEX_GOD_LEVEL_SYMBOLS')) {
            $this->godLevelSymbols = constant('LEVEX_GOD_LEVEL_SYMBOLS');
        }
        if (defined('LEVEX_NEGATIVE_LEVEL_IS_GOD')) {
            $this->negativeLevelIsGod = constant('LEVEX_NEGATIVE_LEVEL_IS_GOD');
        }

        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    public static function instance(): static
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Calculates which level corresponds to a given XP.
     * @param  int  $xp  XP.
     * @return int Level number.
     */
    public function calcLevel(int $xp): int
    {
        if ($xp < $this->baseLevelXp) {
            return 0;
        }
        $level = floor(log($xp / $this->baseLevelXp, $this->levelLogBase)) + 1;
        return $level;
    }

    /**
     * Calculates the minimum XP required to reach a specified level.
     * @param  int  $level  Level Number.
     * @return int Required XP to reach the specified level.
     */
    public function calcRequiredXpToLevel(int $level): int
    {
        if ($level < 1) {
            return 0;
        }
        return $this->baseLevelXp * pow($this->levelLogBase, $level - 1);
    }

    /**
     * Calculates how much XP is remaining to reach a specific level.
     * @param  int  $level  Level number.
     * @param  int  $currentXP  Current XP.
     * @param  int  $levelXP  Outout of the {@see calcRequiredXpToLevel()}.
     * @return int Remaining XP to reach the specified level.
     */
    public function calcRemainingXpToLevel($level, $currentXP, &$levelXP = null): int
    {
        if ($this->checkGodLevel($level)) {
            return 0;
        }

        $levelXP = $this->calcRequiredXpToLevel($level);
        if ($currentXP >= $levelXP) {
            return 0;
        }
        return $levelXP - $currentXP;
    }

    /**
     * Calculates how many percent of progress reamining to reaching a specific level.
     * @param  int  $level  Level Number.
     * @param  int  $currentXP  Current XP.
     * @param  int  $leftXP  Output of the remaining XP to reach the desired level.
     * @return float|int
     */
    public function calcRemainingPercentToLevel(int $level, int $currentXP, int &$leftXP = 0)
    {
        $remaining_xp = $this->calcRemainingXpToLevel($level, $currentXP, $leftXP);
        if ($remaining_xp <= 0) {
            return 0;
        }
        return $remaining_xp * 100 / $leftXP;
    }

    /**
     * Calculates how many percent of progress has been made towards reaching a specific level.
     * @param  int  $level  The level number (greather than zero).
     * @param  int  $currentXP  Gained XP (xperience points).
     * @param  int &$leftXP  Output the remaining XP to reach the desired level.
     * @return float|int
     */
    public function calcPassedPercentToLevel(int $level, int $currentXP, int &$leftXP = 0)
    {
        $remaining_percent = $this->calcRemainingPercentToLevel($level, $currentXP, $leftXP);
        return $remaining_percent <= 0 ? 0 : 100 - $remaining_percent;
    }

    /**
     * Calculates how much XP can be earned from a given price.
     * @param  float|int  $price  Price amount.
     * @param  float|null  $rate  Optional. XP rate per unit of price.
     *      For example, if the rate is 10, every 10 units of a specified price will equal 1 XP. Default `{@see self::priceXpRate}`.
     * @return int
     */
    public function calcXpByPrice($price, ?float $rate = null): int
    {
        if (!is_numeric($price) || (float) $price < 0) {
            return 0;
        }
        $xp = floor($price / ($rate ?? $this->priceXpRate));
        return max(0, $xp);
    }

    /**
     * Checks whether the level is specific to the god or not.
     * @param  string|int  $level  Level name or number.
     * @return bool
     */
    public function checkGodLevel($level): bool
    {
        return in_array($level, $this->godLevelSymbols) || ($this->negativeLevelIsGod && is_int($level) && $level < 0);
    }

    /**
     * Retrives the name/title of a level.
     * @param  string|int  $level  Level Number.
     * @return string
     */
    public function determineLevelName($level): string
    {
        if ($this->levelNamesHandler) {
            $handler = $this->levelNamesHandler;
            $name = $handler($level, $this);
            if ($name !== null) {
                return (string) $name;
            }
        }

        if ($this->checkGodLevel($level)) {
            return 'Creator';
        }

        if (!is_numeric($level)) {
            return 'Undefined';
        }

        $level = (int) $level;
        if ((int) $level <= 0) {
            return 'Noob';
        }

        $levelNames = [
            1 => 'Noob',//100
            2 => 'Beginner',//300
            3 => 'Novice',//900
            4 => 'Apprentice',//2700
            5 => 'Adept',//8100
            6 => 'Expert',//24300
            7 => 'Veteran',//72900
            8 => 'Master',//218700
            9 => 'Grandmaster',//656100
            10 => 'Champion',//1963300
            11 => 'Hero',//5,904,900
            12 => 'Legend',//17,714,700
            13 => 'Mythic',//53,144,100
            14 => 'Immortal',//159,432,300
        ];
        if (isset($levelNames[$level])) {
            return $levelNames[$level];
        }

        if ($level >= 15 && $level <= 28) {
            $level = (int) $level;
            $degree = ($level - 14);
            return "Supreme $degree";
        }

        // >= 29
        $level = (int) $level;
        $degree = ($level - 28);
        return "Angle $degree";
    }
}