<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.04.12.
 * Time: 17:35
 */

namespace Webtown\KunstmaanExtensionBundle\Translation\Extraction\File;

use Doctrine\Common\Annotations\DocParser;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Annotation\Ignore;
use JMS\TranslationBundle\Annotation\Meaning;
use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\Extractor\File\DefaultPhpFileExtractor;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;

class MenuExtractor extends DefaultPhpFileExtractor
{
    const DOMAIN = 'messages';

    public function enterNode(Node $node)
    {
        $functions = [
            'setlabel',
            'addchild',
            'createitem',
        ];
        if (!$node instanceof Node\Expr\MethodCall
            || !is_string($node->name)
            || !in_array(strtolower($node->name), $functions)
            || count($node->args) == 0
            || !$node->args[0]->value instanceof String_
        ) {
            $this->previousNode = $node;

            return;
        }

        $ignore = false;
        $desc = $meaning = null;
        if (null !== $docComment = $this->getDocCommentForNode($node)) {
            if ($docComment instanceof Doc) {
                $docComment = $docComment->getText();
            }
            foreach ($this->docParser->parse($docComment, 'file ' . $this->file . ' near line ' . $node->getLine()) as $annot) {
                if ($annot instanceof Ignore) {
                    $ignore = true;
                } elseif ($annot instanceof Desc) {
                    $desc = $annot->text;
                } elseif ($annot instanceof Meaning) {
                    $meaning = $annot->text;
                }
            }
        }

        if ($ignore) {
            return;
        }

        $id = $node->args[0]->value->value;
        $domain = self::DOMAIN;

        $message = new Message($id, $domain);
        $message->setDesc($desc);
        $message->setMeaning($meaning);
        $message->addSource(new FileSource((string) $this->file, $node->getLine()));

        $this->catalogue->add($message);
    }
}
