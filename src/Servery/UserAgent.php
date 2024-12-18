<?php namespace Nabeghe\Servery;

/**
 * UserAgent Handler.
 */
class UserAgent
{
    protected static ?UserAgent $instance = null;

    protected ?string $value = null;

    /**
     * @var string
     */
    protected string $browserName;

    /**
     * @var bool
     */
    protected bool $isBot;

    /**
     * @var bool
     */
    protected bool $isMobile;

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getBrowserName(): string
    {
        if (!isset($this->browserName)) {
            $this->browserName = static::detectBrowserName($this->value);
        }
        return $this->browserName;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return static::generateHash($this->value);
    }

    /**
     * @return string
     */
    public function getSimpleHash(): string
    {
        return static::generateSimpleHash($this->value);
    }

    /**
     * @return bool
     */
    public function isBot(): bool
    {
        if (!isset($this->isBot)) {
            $this->isBot = static::detectBot($this->value);
        }
        return $this->isBot;
    }

    /**
     * @return bool
     */
    public function isMobile(): bool
    {
        if (!isset($this->isMobile)) {
            $this->isMobile = static::detectMobile($this->value);
        }
        return $this->isMobile;
    }

    /**
     * @param  string|null  $value
     */
    public function __construct(?string $value = null)
    {
        if (func_num_args() === 0) {
            $value = static::getCurrent();
        }

        $this->value = $value;
    }

    /**
     * @return static|null
     */
    public static function instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public static function getCurrent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public static function getCurrentReal(): string
    {
        if (isset($_SERVER['HTTP_X_ORIGINAL_USER_AGENT'])) {
            return $_SERVER['HTTP_X_ORIGINAL_USER_AGENT'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return static::getCurrent();
    }

    public static function detectBrowserName(?string $userAgentValue = null): string
    {
        if ($userAgentValue === null) {
            $userAgentValue = static::getCurrent();
        }
        if ($userAgentValue) {
            if (strpos($userAgentValue, 'Lynx') !== false) {
                return 'lynx';
            } elseif (strpos($userAgentValue, 'Edg') !== false) {
                return 'edge';
            } elseif (stripos($userAgentValue, 'chrome') !== false) {
                return 'chrome';
            } elseif (stripos($userAgentValue, 'safari') !== false) {
                return 'safari';
            } elseif ((strpos($userAgentValue, 'MSIE') !== false || strpos($userAgentValue,
                        'Trident') !== false) && strpos($userAgentValue, 'Win') !== false) {
                return 'ie'; // win
            } elseif (strpos($userAgentValue, 'MSIE') !== false && strpos($userAgentValue, 'Mac') !== false) {
                return 'ie'; // mac
            } elseif (strpos($userAgentValue, 'Gecko') !== false) {
                return 'gecko';
            } elseif (strpos($userAgentValue, 'Opera') !== false) {
                return 'opera';
            } elseif (strpos($userAgentValue, 'Nav') !== false && strpos($userAgentValue, 'Mozilla/4.') !== false) {
                return 'ns4';
            }
        }
        return '';
    }

    public static function generateHash(?string $userAgentValue = null)
    {
        if ($userAgentValue === null) {
            $userAgentValue = static::getCurrent();
        }
        return md5($userAgentValue);
    }

    public static function generateSimpleHash(?string $userAgentValue = null)
    {
        if ($userAgentValue === null) {
            $userAgentValue = static::getCurrent();
        }
        return crc32($userAgentValue);
    }

    public static function detectBot(?string $userAgentValue = null): bool
    {
        if ($userAgentValue === null) {
            $userAgentValue = static::getCurrent();
        }
        if ($userAgentValue) {
            if (preg_match('/abacho|accona|AddThis|AdsBot|ahoy|AhrefsBot|AISearchBot|alexa|altavista|anthill|appie|applebot|arale|araneo|AraybOt|ariadne|arks|aspseek|ATN_Worldwide|Atomz|baiduspider|baidu|bbot|bingbot|bing|Bjaaland|BlackWidow|BotLink|bot|boxseabot|bspider|calif|CCBot|ChinaClaw|christcrawler|CMC\/0\.01|combine|confuzzledbot|contaxe|CoolBot|cosmos|crawler|crawlpaper|crawl|curl|cusco|cyberspyder|cydralspider|dataprovider|digger|DIIbot|DotBot|downloadexpress|DragonBot|DuckDuckBot|dwcp|EasouSpider|ebiness|ecollector|elfinbot|esculapio|ESI|esther|eStyle|Ezooms|facebookexternalhit|facebook|facebot|fastcrawler|FatBot|FDSE|FELIX IDE|fetch|fido|find|Firefly|fouineur|Freecrawl|froogle|gammaSpider|gazz|gcreep|geona|Getterrobo-Plus|get|girafabot|golem|googlebot|-google|grabber|GrabNet|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|HTTrack|ia_archiver|iajabot|IDBot|Informant|InfoSeek|InfoSpiders|INGRID\/0\.1|inktomi|inspectorwww|Internet Cruiser Robot|irobot|Iron33|JBot|jcrawler|Jeeves|jobo|KDD-Explorer|KIT-Fireball|ko_yappo_robot|label-grabber|larbin|legs|libwww-perl|linkedin|Linkidator|linkwalker|Lockon|logo_gif_crawler|Lycos|m2e|majesticsEO|marvin|mattie|mediafox|mediapartners|MerzScope|MindCrawler|MJ12bot|mod_pagespeed|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|NationalDirectory|naverbot|NEC-MeshExplorer|NetcraftSurveyAgent|NetScoop|NetSeer|newscan-online|nil|none|Nutch|ObjectsSearch|Occam|openstat.ru\/Bot|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pingdom|pinterest|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|rambler|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Scrubby|Search-AU|searchprocess|search|SemrushBot|Senrigan|seznambot|Shagseeker|sharp-info-agent|sift|SimBot|Site Valet|SiteSucker|skymob|SLCrawler\/2\.0|slurp|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|spider|suke|tach_bw|TechBOT|TechnoratiSnoop|templeton|teoma|titin|topiclink|twitterbot|twitter|UdmSearch|Ukonline|UnwindFetchor|URL_Spider_SQL|urlck|urlresolver|Valkyrie libwww-perl|verticrawl|Victoria|void-bot|Voyager|VWbot_K|wapspider|WebBandit\/1\.0|webcatcher|WebCopier|WebFindBot|WebLeacher|WebMechanic|WebMoose|webquest|webreaper|webspider|webs|WebWalker|WebZip|wget|whowhere|winona|wlm|WOLP|woriobot|WWWC|XGET|xing|yahoo|YandexBot|YandexMobileBot|yandex|yeti|Zeus/i',
                $userAgentValue)) {
                return true;
            }
        }
        return false;
    }

    public static function detectMobile(?string $userAgentValue = null): bool
    {
        if ($userAgentValue === null) {
            $userAgentValue = static::getCurrent();
        }

        if (empty($userAgentValue)) {
            return false;
        }

        return (strpos($userAgentValue, 'Mobile') !== false // Many mobile devices (all iPhone, iPad, etc.)
            || strpos($userAgentValue, 'Android') !== false
            || strpos($userAgentValue, 'Silk/') !== false
            || strpos($userAgentValue, 'Kindle') !== false
            || strpos($userAgentValue, 'BlackBerry') !== false
            || strpos($userAgentValue, 'Opera Mini') !== false
            || strpos($userAgentValue, 'Opera Mobi') !== false);
    }
}