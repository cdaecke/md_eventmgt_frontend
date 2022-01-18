<?php
declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\TypeConverter;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Christoph Daecke <typo3@mediadreams.org>
 * This class is taken from: http://lbrmedia.net/codebase/Eintrag/extbase-propertymapper-float-komma-notierung/
 * Thanks to Marcel!
 */

use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;

/**
 * Class FloatConverter
 * @package Mediadreams\MdEventmgtFrontend\TypeConverter
 */
class FloatConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\FloatConverter
{
    /**
     * Actually convert from $source to $targetType, by doing a typecast.
     *
     * @param mixed $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface $configuration
     * @return float|Error
     * @api
     */
    public function convertFrom(
        $source,
        $targetType,
        array $convertedChildProperties = array(),
        PropertyMappingConfigurationInterface $configuration = null
    ) {
        if ($source === null || strlen($source) === 0) {
            return null;
        }

        $posComma = strpos($source, ",");
        $posPoint = strpos($source, ".");

        if ($posComma === false && $posPoint === false) { // should be an integer
            //$source = $source;
        } else {
            if ($posComma !== false && $posPoint === false) { // there is a comma. Let us define this is a german value with decimals
                $source = str_replace(",", ".", $source); // transform to english notation
            } else {
                if ($posComma === false && $posPoint !== false) { // there is a point. Let us define this is an english value with decimals
                    //$source = $source;
                } else {
                    // at this point we have a comma and a point.
                    // Let us try to find out if it is 0.000,00 or 0,000.00
                    if ($posComma < $posPoint) { // 0,000.00
                        // we need no comma
                        $source = str_replace(",", "", $source);
                    } else { // 0.000,00
                        // remove the . and replace , with .
                        $source = str_replace(".", "", $source);
                        $source = str_replace(",", ".", $source);
                    }
                }
            }
        }

        if (!is_numeric($source)) {
            return new Error('"%s" cannot be converted to a float value.', 1642496631, [$source]);
        }

        return (float)$source;
    }
}
