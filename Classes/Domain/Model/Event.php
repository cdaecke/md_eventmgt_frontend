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

use Mediadreams\MdEventmgtFrontend\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Event
 */
class Event extends \DERHANSEN\SfEventMgt\Domain\Model\Event
{
    /**
     * Frontend user, who has created the event
     */
    protected ?FrontendUser $mdEventmgtFeuser = null;

    /**
     * Slug of the event
     */
    protected string $slug;

    public function getMdEventmgtFeuser(): ?FrontendUser
    {
        return $this->mdEventmgtFeuser;
    }

    public function setMdEventmgtFeuser(?FrontendUser $mdEventmgtFeuser): void
    {
        $this->mdEventmgtFeuser = $mdEventmgtFeuser;
    }

    public function setMdEventmgtFeuserByUid(int $uid): void
    {
        $frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
        $feUser = $frontendUserRepository->findOneBy(['uid' => $uid]);

        if ($feUser !== null) {
            $this->mdEventmgtFeuser = $feUser;
        }
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
