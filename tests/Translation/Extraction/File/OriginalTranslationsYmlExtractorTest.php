<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.04.15.
 * Time: 7:27
 */

namespace Webtown\KunstmaanExtensionBundle\Tests\Translation\Extraction\File;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use Webtown\KunstmaanExtensionBundle\Test\Traits\PhpUnitTrait;
use Webtown\KunstmaanExtensionBundle\Translation\Extraction\File\OriginalTranslationsYmlExtractor;

class OriginalTranslationsYmlExtractorTest extends \PHPUnit_Framework_TestCase
{
    use PhpUnitTrait;

    /**
     * @param $array
     * @param $results
     *
     * @dataProvider getArrays
     */
    public function testChildrenKeys($array, $results)
    {
        $extractor = new OriginalTranslationsYmlExtractor();

        $this->assertEquals($results, $this->callObjectProtectedMethod($extractor, 'getChildrenKeys', [$array]));
    }

    public function getArrays()
    {
        return [
            [
                [],
                [],
            ],
            [
                ['test' => 'test', 'text' => 'text'],
                ['test' => 'test', 'text' => 'text'],
            ],
            [
                ['test' => ['subtest' => 'subtest'], 'text' => 'text'],
                ['test.subtest' => 'subtest', 'text' => 'text'],
            ],
            [
                ['test' => ['subtest' => 'subtest', 'subtest1' => 'subtest1'], 'text' => 'text'],
                ['test.subtest' => 'subtest', 'test.subtest1' => 'subtest1', 'text' => 'text'],
            ],
            [
                ['test' => ['subtest' => 'subtest', 'subtest1' => ['subsubtest' => 'subtest1']], 'text' => 'text'],
                ['test.subtest' => 'subtest', 'test.subtest1.subsubtest' => 'subtest1', 'text' => 'text'],
            ],
        ];
    }

    /**
     * @param $filePath
     * @param $locale
     * @param $result
     *
     * @dataProvider getFilesForSkip
     */
    public function testSkipThisLocale($filePath, $locale, $result)
    {
        $extractor = new OriginalTranslationsYmlExtractor();
        $this->assertTrue(file_exists($filePath));
        $file = new \SplFileInfo($filePath);
        $this->assertEquals($result, $this->callObjectProtectedMethod($extractor, 'skipThisLocale', [$file, $locale]));
    }

    public function getFilesForSkip()
    {
        return [
            [$this->getResourceFilePath('invalid_translation.yml'), null, true],
            // catalogue locale: en
            [$this->getResourceFilePath('translations/messages.en.yml'),    'en', false],
            [$this->getResourceFilePath('translations/messages.es.yml'),    'en', true],
            [$this->getResourceFilePath('translations/messages.hu.yml'),    'en', true],
            [$this->getResourceFilePath('translations/other.es.yml'),       'en', true],
            [$this->getResourceFilePath('translations/validation.en.yml'),  'en', false],
            // catalogue locale: hu
            [$this->getResourceFilePath('translations/messages.en.yml'),    'hu', true],
            [$this->getResourceFilePath('translations/messages.es.yml'),    'hu', true],
            [$this->getResourceFilePath('translations/messages.hu.yml'),    'hu', false],
            [$this->getResourceFilePath('translations/other.es.yml'),       'hu', true],
            [$this->getResourceFilePath('translations/validation.en.yml'),  'hu', false],
            // catalogue locale: es
            [$this->getResourceFilePath('translations/messages.en.yml'),    'es', true],
            [$this->getResourceFilePath('translations/messages.es.yml'),    'es', false],
            [$this->getResourceFilePath('translations/messages.hu.yml'),    'es', true],
            [$this->getResourceFilePath('translations/other.es.yml'),       'es', false],
            [$this->getResourceFilePath('translations/validation.en.yml'),  'es', false],
            // catalogue locale: de
            [$this->getResourceFilePath('translations/messages.en.yml'),    'de', false],
            [$this->getResourceFilePath('translations/messages.es.yml'),    'de', true],
            [$this->getResourceFilePath('translations/messages.hu.yml'),    'de', true],
            [$this->getResourceFilePath('translations/other.es.yml'),       'de', true],
            [$this->getResourceFilePath('translations/validation.en.yml'),  'de', false],
        ];
    }

    protected function getResourceFilePath($file)
    {
        $base = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            '..',
            '..',
            '..',
            'Resources',
        ]);

        return $base . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * @param $filePath
     * @param $locale
     * @param $results
     *
     * @dataProvider getVisitedFiles
     */
    public function testVisitFile($filePath, $locale, $results)
    {
        $extractor = new OriginalTranslationsYmlExtractor();
        $this->assertTrue(file_exists($filePath));
        $file = new \SplFileInfo($filePath);
        $catalogue = new MessageCatalogue();
        $catalogue->setLocale($locale);
        $extractor->visitFile($file, $catalogue);

        $this->assertEquals($results, $this->convertCatalogueToArray($catalogue));
    }

    public function getVisitedFiles()
    {
        return [
            [$this->getResourceFilePath('invalid.xml'), null, []],
            [$this->getResourceFilePath('invalid_translation.yml'), null, []],
            // catalogue locale: en
            [$this->getResourceFilePath('translations/messages.en.yml'),    'en', [
                'messages' => [
                    'button.cancel' => [
                        'target' => 'Cancel',
                        'note'   => null,
                    ]
                ]
            ]],
            [$this->getResourceFilePath('translations/messages.es.yml'),    'en', []],
            [$this->getResourceFilePath('translations/messages.hu.yml'),    'en', []],
            [$this->getResourceFilePath('translations/other.es.yml'),       'en', []],
            [$this->getResourceFilePath('translations/validation.en.yml'),  'en', [
                'validation' => [
                    'validation.error' => [
                        'target' => 'Error',
                        'note'   => null,
                    ]
                ]
            ]],
            // catalogue locale: hu
            [$this->getResourceFilePath('translations/messages.en.yml'),    'hu', []],
            [$this->getResourceFilePath('translations/messages.es.yml'),    'hu', []],
            [$this->getResourceFilePath('translations/messages.hu.yml'),    'hu', [
                'messages' => [
                    'button.cancel' => [
                        'target' => 'Mégse',
                        'note'   => null,
                    ]
                ]
            ]],
            [$this->getResourceFilePath('translations/other.es.yml'),       'hu', []],
            [$this->getResourceFilePath('translations/validation.en.yml'),  'hu', [
                'validation' => [
                    'validation.error' => [
                        'target' => 'validation.error',
                        'note'   => 'En: `Error`',
                    ]
                ]
            ]],
            // catalogue locale: es
            [$this->getResourceFilePath('translations/messages.en.yml'),    'es', []],
            [$this->getResourceFilePath('translations/messages.es.yml'),    'es', [
                'messages' => [
                    'button.cancel' => [
                        'target' => 'Cancelar',
                        'note'   => null,
                    ]
                ]
            ]],
            [$this->getResourceFilePath('translations/messages.hu.yml'),    'es', []],
            [$this->getResourceFilePath('translations/other.es.yml'),       'es', [
                'other' => [
                    'other.cancel' => [
                        'target' => 'Cancelar',
                        'note'   => null,
                    ]
                ]
            ]],
            [$this->getResourceFilePath('translations/validation.en.yml'),  'es', [
                'validation' => [
                    'validation.error' => [
                        'target' => 'validation.error',
                        'note'   => 'En: `Error`',
                    ]
                ]
            ]],
            // catalogue locale: de
            [$this->getResourceFilePath('translations/messages.en.yml'),    'de', [
                'messages' => [
                    'button.cancel' => [
                        'target' => 'button.cancel',
                        'note'   => 'En: `Cancel`',
                    ]
                ]
            ]],
            [$this->getResourceFilePath('translations/messages.es.yml'),    'de', []],
            [$this->getResourceFilePath('translations/messages.hu.yml'),    'de', []],
            [$this->getResourceFilePath('translations/other.es.yml'),       'de', []],
            [$this->getResourceFilePath('translations/validation.en.yml'),  'de', [
                'validation' => [
                    'validation.error' => [
                        'target' => 'validation.error',
                        'note'   => 'En: `Error`',
                    ]
                ]
            ]],
        ];
    }

    protected function convertCatalogueToArray(MessageCatalogue $catalogue)
    {
        $results = [];
        foreach ($catalogue->getDomains() as $domain => $messageCollection) {
            /** @var Message $message */
            foreach ($messageCollection->all() as $message) {
                $results[$domain][$message->getId()] = [
                    'target' => $message->getLocaleString(),
                    'note' => $message->getMeaning(),
                ];
            }
        }

        return $results;
    }
}
