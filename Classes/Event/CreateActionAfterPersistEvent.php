<?php
declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Event;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Christoph Daecke <typo3@mediadreams.org>
 */


/**
 * Class CreateActionAfterPersistEvent
 * This event is triggered in CreateAction() after the event was persisted, so it includes the event Uid.
 * All changes on the event will be saved afterwards.
 *
 * @package Mediadreams\MdEventmgtFrontend\Event
 */
final class CreateActionAfterPersistEvent extends BaseEvent
{

}
