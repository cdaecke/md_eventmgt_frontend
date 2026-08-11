<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Domain\Repository;

use Mediadreams\MdEventmgtFrontend\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Christoph Daecke <typo3@mediadreams.org>
 */

/**
 * The repository for FrontendUsers
 *
 * @extends \TYPO3\CMS\Extbase\Persistence\Repository<FrontendUser>
 */
class FrontendUserRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * Disable storage page for all repository calls
     */
    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }
}
