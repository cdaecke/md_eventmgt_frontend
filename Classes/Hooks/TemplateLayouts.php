<?php
declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Hooks;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Christoph Daecke <typo3@mediadreams.org>
 * This class was initially taken from ext:sf_event_mgt. Thanks to Torben Hansen
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Class TemplateLayouts
 * @package Mediadreams\MdEventmgtFrontend\Hooks
 */
class TemplateLayouts
{
    /**
     * Itemsproc function to extend the selection of templateLayouts in the plugin
     *
     * @param array $config Configuration array
     */
    public function getTemplateLayouts(array &$config)
    {
        $templateLayouts = $this->getTemplateLayoutsFromTsConfig($config['flexParentDatabaseRow']['pid']);
        foreach ($templateLayouts as $index => $layout) {
            $additionalLayout = [
                $GLOBALS['LANG']->sL($layout),
                $index
            ];
            array_push($config['items'], $additionalLayout);
        }
    }

    /**
     * Get template layouts defined in TsConfig
     *
     * @param int $pageUid PageUID
     *
     * @return array
     */
    protected function getTemplateLayoutsFromTsConfig(int $pageUid)
    {
        $templateLayouts = [];
        $pagesTsConfig = BackendUtility::getPagesTSconfig($pageUid);
        if (isset($pagesTsConfig['tx_mdeventmgt_frontend.']['templateLayouts.']) &&
            is_array($pagesTsConfig['tx_mdeventmgt_frontend.']['templateLayouts.'])
        ) {
            $templateLayouts = $pagesTsConfig['tx_mdeventmgt_frontend.']['templateLayouts.'];
        }

        return $templateLayouts;
    }
}
