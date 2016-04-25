<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.04.15.
 * Time: 7:27
 */

namespace Webtown\KunstmaanExtensionBundle\Translation\Extraction\File;


use Webtown\KunstmaanExtensionBundle\Translation\Extraction\File\OriginalTranslationsYmlExtractor;

class OriginalTranslationsYmlExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $array
     * @param $results
     *
     * @dataProvider getArrays
     */
    public function testChildrenKeys($array, $results)
    {
        $extractor = new OriginalTranslationsYmlExtractor();

        $this->assertEquals($results, $extractor->getChildrenKeys($array));
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
}
