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
 */
class OriginalTranslationsYmlExtractor implements FileVisitorInterface
{
    /**
     * @var Parser
     */
    protected $ymlParser;

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
        if ('.en.yml' !== substr($file, -7)) {
            return;
        }
        $domain = substr($file->getFilename(), 0, -7);
        $parser = $this->getYmlParser();
        $translationsArray = $parser->parse(file_get_contents($file));
        $translationIds = $this->getChildrenKeys($translationsArray);
        foreach ($translationIds as $id => $enTranslation) {
            $message = new Message($id, $domain);
            // @todo (Chris) A $file ilyet ad vissza: Resources/.../...twig --> nem tudni, melyik bundle. Inkább a $file->getRealPath()-ból kellene legyártani.
            $message->addSource(new FileSource((string) $file));
            $message->setMeaning(sprintf('En: `%s`', $enTranslation));
            $catalogue->add($message);
        }
    }

    // @todo (Chris) Ezt protecteddé kell tenni és a tesztben máshogy hívni.
    public function getChildrenKeys($array)
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
     */
    public function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $ast)
    {
    }

    protected function getYmlParser()
    {
        if (!$this->ymlParser) {
            $this->ymlParser = new Parser();
        }

        return $this->ymlParser;
    }
}
