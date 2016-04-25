<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.04.11.
 * Time: 10:38
 */

namespace Webtown\KunstmaanExtensionBundle\Exception;

/**
 * Class InsertedMaxDepthException
 *
 * There is a limit of page insertion to prepend the infinity insert cycle. You can change the limit with the
 * `webtown_kunstmaan_extension.max_page_insertion_depth` parameter.
 *
 * @package Webtown\KunstmaanExtensionBundle\Exception
 */
class InsertedMaxDepthException extends \Exception
{
}
