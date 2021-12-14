<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Domain\Model;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Christoph Daecke <typo3@mediadreams.org>
 */

/**
 * Event
 */
class Event extends \DERHANSEN\SfEventMgt\Domain\Model\Event
{
    /**
     * Frontend user, who has created the event
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    protected $mdEventmgtFeuser = null;

    /**
     * Slug of the event
     *
     * @var string
     */
    protected $slug = null;

    /**
     * Returns the mdEventmgtFeuser
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $mdEventmgtFeuser
     */
    public function getMdEventmgtFeuser()
    {
        return $this->mdEventmgtFeuser;
    }

    /**
     * Sets the mdEventmgtFeuser
     *
     * @param int $mdEventmgtFeuser
     * @return void
     */
    public function setMdEventmgtFeuser($mdEventmgtFeuser)
    {
        $this->mdEventmgtFeuser = $mdEventmgtFeuser;
    }

    /**
     * Returns the slug
     *
     * @return string $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Sets the slug
     *
     * @param string $slug
     * @return void
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }
}
