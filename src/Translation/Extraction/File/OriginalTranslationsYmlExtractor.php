<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.04.14.
 * Time: 15:59
 */

namespace Webtown\KunstmaanExtensionBundle\Translation\Extraction\File;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use Symfony\Component\Yaml\Parser;

/**
 * Class OriginalTranslationsYmlExtractor
 *
 * Collect the original translations
 *
 * @package Webtown\KunstmaanExtensionBundle\Translation\Extraction\File
 *
 * @todo (Chris) Nem tud együtt működni a gyorsítással! Meg kell oldani, hogy a gyorsítást ki lehessen kapcsolni VAGY lehessen beállítani szabályokat, ahol a preformance tesztet kihagyva újra és újra fut az egész.
 */
class OriginalTranslationsYmlExtractor implements FileVisitorInterface
{
    const FILE_PIECE_DOMAIN = 'domain';
    const FILE_PIECE_LOCALE = 'locale';
    const FILE_PIECE_EXTENSION = 'extension';

    /**
     * @var Parser
     */
    protected $ymlParser;

    /**
     * @var array
     */
    protected $filePieces = [];

    /**
     * Called for non-specially handled files.
     *
     * This is not called if handled by a more specific method.
     *
     * @param \SplFileInfo $file
     * @param MessageCatalogue $catalogue
     */
    public function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue)
    {
        if ('.yml' !== substr($file, -4)) {
            return;
        }

        if ($this->skipThisLocale($file, $catalogue->getLocale())) {
            return;
        }

        $domain = substr($file->getFilename(), 0, -7);
        $parser = $this->getYmlParser();
        $translationsArray = $parser->parse(file_get_contents($file));
        $translationIds = $this->getChildrenKeys($translationsArray);
        $fileLocale = $this->getFilePiece($file, self::FILE_PIECE_LOCALE);
        $catalogueLocale = $catalogue->getLocale();
        foreach ($translationIds as $id => $translation) {
            $message = new Message($id, $domain);
            $message->addSource(new FileSource((string) $file));
            switch ($fileLocale) {
                case $catalogueLocale:
                    $message->setLocaleString($translation);
                    break;
                case 'en':
                    $message->setMeaning(sprintf('En: `%s`', $translation));
                    break;
            }
            $catalogue->add($message);
        }
    }

    protected function skipThisLocale(\SplFileInfo $file, $locale)
    {
        $fileLocale = $this->getFilePiece($file, self::FILE_PIECE_LOCALE);
        // Ha ez éppen az adott nyelven készített fájl, pl: messages.hu.yml...
        if ($fileLocale && $fileLocale == $locale) {
            return false;
        } elseif ($fileLocale != 'en') {
            return true;
        }

        $catalogueFileName = implode('.', [
            $this->getFilePiece($file, self::FILE_PIECE_DOMAIN),
            $locale,
            $this->getFilePiece($file, self::FILE_PIECE_EXTENSION),
        ]);
        $catalogueFilePath = $file->getPath() . DIRECTORY_SEPARATOR . $catalogueFileName;

        return file_exists($catalogueFilePath);
    }

    /**
     * @param \SplFileInfo $file
     * @param string $pieceName
     *
     * @return string
     */
    protected function getFilePiece(\SplFileInfo $file, $pieceName)
    {
        return $this->getFilePieces($file)[$pieceName];
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return array
     */
    protected function getFilePieces(\SplFileInfo $file)
    {
        $key = $file->getBasename();
        if (!array_key_exists($key, $this->filePieces)) {
            $pieces = explode('.', $file->getBasename());
            if (count($pieces) < 3) {
                return true;
            }
            $extension = array_pop($pieces);
            $locale = array_pop($pieces);
            $domain = implode('.', $pieces);

            $this->filePieces[$key] = [
                self::FILE_PIECE_DOMAIN => $domain,
                self::FILE_PIECE_LOCALE => $locale,
                self::FILE_PIECE_EXTENSION => $extension,
            ];
        }

        return $this->filePieces[$key];
    }

    protected function getChildrenKeys($array)
    {
        $keys = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach ($this->getChildrenKeys($value) as $subKey => $subValue) {
                    $keys[$key . '.' . $subKey] = $subValue;
                }
            } else {
                $keys[$key] = $value;
            }
        }

        return $keys;
    }

    /**
     * Called when a PHP file is encountered.
     *
     * The visitor already gets a parsed AST passed along.
     *
     * @param \SplFileInfo $file
     * @param MessageCatalogue $catalogue
     * @param array $ast
     *
     * @codeCoverageIgnore
     */
    public function visitPhpFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
    }

    /**
     * Called when a Twig file is encountered.
     *
     * The visitor already gets a parsed AST passed along.
     *
     * @param \SplFileInfo $file
     * @param MessageCatalogue $catalogue
     * @param \Twig_Node $ast
     *
     * @codeCoverageIgnore
     */
    public function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $ast)
    {
    }

    /**
     * @return Parser
     *
     * @codeCoverageIgnore
     */
    protected function getYmlParser()
    {
        if (!$this->ymlParser) {
            $this->ymlParser = new Parser();
        }

        return $this->ymlParser;
    }
}
