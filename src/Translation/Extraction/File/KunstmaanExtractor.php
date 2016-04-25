<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.04.14.
 * Time: 13:48
 */

namespace Webtown\KunstmaanExtensionBundle\Translation\Extraction\File;

use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Annotation\Ignore;
use JMS\TranslationBundle\Annotation\Meaning;
use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\File\DefaultPhpFileExtractor;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Symfony\Component\Yaml\Parser;

/**
 * Class KunstmaanExtractor
 *
 * Kunstmaan specific translation extractor:
 *  - custom class and methods (self::enterNode())
 *  - page part names from config ymls (self::visitFile())
 *
 * @package Webtown\KunstmaanExtensionBundle\Translation\Extraction\File
 *
 * @see \JMS\TranslationBundle\JMSTranslationBundle
 */
class KunstmaanExtractor extends DefaultPhpFileExtractor
{
    const DOMAIN = 'messages';

    /**
     * @var Parser
     */
    protected $ymlParser;

    public function enterNode(Node $node)
    {
        /**
         * Configuration:
         *  Key: name of class or method
         *  Value: the translation ID number from arguments (starting from 0!)
         */
        $classConfigs = [
            'SimpleListAction' => 1,
            'Tab' => 0,
        ];
        $methodConfigs = [
            'addField' => 1,
            'addFilter' => 2,
        ];

        if (!$node instanceof Node\Expr\New_ && !$node instanceof Node\Expr\MethodCall) {
            return;
        }

        // @todo (Chris) Ezt még meg kellene csinálni, hogy működjön!
        $ignore = false;
        $desc = $meaning = null;
//        if (null !== $docComment = $this->getDocCommentForNode($node)) {
//            if ($docComment instanceof Doc) {
//                $docComment = $docComment->getText();
//            }
//            foreach ($this->docParser->parse($docComment, 'file ' . $this->file . ' near line ' . $node->getLine()) as $annot) {
//                if ($annot instanceof Ignore) {
//                    $ignore = true;
//                } elseif ($annot instanceof Desc) {
//                    $desc = $annot->text;
//                } elseif ($annot instanceof Meaning) {
//                    $meaning = $annot->text;
//                }
//            }
//        }

        if ($ignore) {
            return;
        }

        if ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Name) {
            // @todo (Chris) A parts-nál meg kellene nézni, hogy mi van, ha van namespace! Pl: new Namespace\User()...
            // @todo (Chris) Az sem ártana, ha FQNS-re tesztelné az osztály egyezőséget
            $className = $node->class->parts[0];
            if (array_key_exists($className, $classConfigs)) {
                $argumentNumber = $classConfigs[$className];
                $this->registerArgument($node, $argumentNumber, $desc, $meaning);
            }

            return;
        }
        // @todo (Chris) Ez jó lenne, ha csak megadott osztályok esetén futna le, így szűrni kellene fájlnévre, vagy osztályra.
        if ($node instanceof Node\Expr\MethodCall && is_string($node->name)) {
            $methodName = $node->name;
            if (array_key_exists($methodName, $methodConfigs)) {
                $argumentNumber = $methodConfigs[$methodName];
                $this->registerArgument($node, $argumentNumber, $desc, $meaning);
            }

            return;
        }
        return;
    }

    /**
     * @param Node|Node\Expr\New_|Node\Expr\MethodCall $node
     * @param $argumentNumber
     * @param $desc
     * @param $meaning
     */
    protected function registerArgument(Node $node, $argumentNumber,$desc, $meaning)
    {
        if (array_key_exists($argumentNumber, $node->args)) {
            $argument =  $node->args[$argumentNumber];
            if ($argument->value instanceof Node\Scalar\String_) {
                $id = $argument->value->value;
                $domain = self::DOMAIN;

                $message = new Message($id, $domain);
                $message->setDesc($desc);
                $message->setMeaning($meaning);
                $message->addSource(new FileSource((string) $this->file, $node->getLine()));

                $this->catalogue->add($message);
            }
        }
    }

    /**
     * Collect the pagepart names!
     *
     * @param \SplFileInfo $file
     * @param MessageCatalogue $catalogue
     */
    public function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue)
    {
        if ($file->getExtension() != 'yml') {
            return;
        }
        $path = strtr($file->getRealPath(), DIRECTORY_SEPARATOR, '/');
        if (strpos($path, 'Resources/config/pageparts') === false) {
            return;
        }
        $parser = $this->getYmlParser();
        $pagePartConfigs = $parser->parse(file_get_contents($file));
        if (array_key_exists('types', $pagePartConfigs) && is_array($pagePartConfigs['types'])) {
            foreach ($pagePartConfigs['types'] as $type) {
                if (is_array($type) && array_key_exists('name', $type)) {
                    $message = new Message($type['name']);
                    $message->addSource(new FileSource((string)$file));
                    $catalogue->add($message);
                }
            }
        }
    }

    protected function getYmlParser()
    {
        if (!$this->ymlParser) {
            $this->ymlParser = new Parser();
        }

        return $this->ymlParser;
    }
}
