<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Service;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Christoph Daecke <typo3@mediadreams.org>
 */

use Mediadreams\MdEventmgtFrontend\Domain\Model\Event;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SlugService
 * @package Mediadreams\MdEventmgtFrontend\Service
 */
class SlugService
{
    /**
     * @var string $tableName
     */
    protected $tableName = 'tx_sfeventmgt_domain_model_event';

    /**
     * @var string $fieldName
     */
    protected $fieldName = 'slug';

    /**
     * @var SlugHelper $slugHelper
     */
    protected $slugHelper;

    /**
     * SlugService constructor.
     */
    public function __construct()
    {
        $fieldConfig = $GLOBALS['TCA'][$this->tableName]['columns'][$this->fieldName]['config'];
        $this->slugHelper = GeneralUtility::makeInstance(
            SlugHelper::class,
            $this->tableName,
            $this->fieldName,
            $fieldConfig
        );
    }

    /**
     * Get unique slug for entry
     *
     * @param Event $obj
     * @return string
     * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
     */
    public function getSlug(Event $obj): string
    {
        $recordData = [
            'title' => $obj->getTitle(),
        ];

        $slug = $this->slugHelper->generate($recordData, $obj->getPid());

        $state = RecordStateFactory::forName($this->tableName)
            ->fromArray($recordData, $obj->getPid(), $obj->getUid());

        $slug = $this->slugHelper->buildSlugForUniqueInSite($slug, $state);

        return $slug;
    }
}
