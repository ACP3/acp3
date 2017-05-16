<?php
namespace ACP3\Core\I18n;

use ACP3\Core\I18n\DictionaryCache as LanguageCache;

class Translator implements TranslatorInterface
{
    /**
     * @var \ACP3\Core\I18n\DictionaryCache
     */
    protected $dictionaryCache;
    /**
     * @var Locale
     */
    private $locale;
    /**
     * @var array
     */
    protected $buffer = [];

    /**
     * Translator constructor.
     * @param DictionaryCache $dictionaryCache
     * @param Locale $locale
     */
    public function __construct(
        LanguageCache $dictionaryCache,
        Locale $locale
    ) {
        $this->dictionaryCache = $dictionaryCache;
        $this->locale = $locale;
    }

    /**
     * @param string $module
     * @param string $phrase
     * @param array  $arguments
     *
     * @return string
     */
    public function t(string $module, string $phrase, array $arguments = []): string
    {
        if (isset($this->buffer[$this->locale->getLocale()]) === false) {
            $this->buffer[$this->locale->getLocale()] = $this->dictionaryCache->getLanguageCache($this->locale->getLocale());
        }

        if (isset($this->buffer[$this->locale->getLocale()]['keys'][$module . $phrase])) {
            return strtr($this->buffer[$this->locale->getLocale()]['keys'][$module . $phrase], $arguments);
        }

        return strtoupper('{' . $module . '_' . $phrase . '}');
    }
}
