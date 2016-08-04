<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.05.03.
 * Time: 17:29
 */

namespace Webtown\KunstmaanExtensionBundle\Entity;

interface SearchableEntityInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param string|null $locale
     * @return string
     */
    public function getSearchTitle($locale = null);

    /**
     * @param string|null $locale
     * @return string
     */
    public function getSearchContent($locale = null);

    /**
     * @return string Translation key!
     */
    public function getSearchType();

    /**
     * @return string
     */
    public function getSearchRouteName();

    /**
     * @return array
     */
    public function getSearchRouteParameters();

    /**
     * Use in create doc UID.
     *
     * @return string
     */
    public function getSearchUniqueEntityName();
}
