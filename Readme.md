# TYPO3 Extension ``md_eventmgt_frontend``

This extension enables a frontend user to add new events of  ``ext:sf_event_mgt``.

Templates are ready to use with the [bootstrap framework](https://getbootstrap.com/) and icons will be shown, if you have [Font Awesome](https://fontawesome.com/) icon set included in your project.

## Requirements

- TYPO3 >= 10.4
- ext:sf_event_mgt >= 5.6

## Installation

- Install the extension by using the extension manager or use composer
- Include the static TypoScript of the extension
- Configure the extension by setting your own Typoscript variables

## Usage

- Add the pluign ``Event management frontend`` on a page, which is restricted by the frontend user login
- Select a storage page in the plugin-tab in the field ``Record Storage Page``
    - Tab `Settings`
        - `Event type `: Select whether the events are whole day events (`All day events`), or have a start and end date and time (`Start- and end date and time`).
        - `Parent category`: If selected, all subcategories of this will be shown in the template.
        - `Location storage page`: Event locations on this page will be shown in the template.
        - `Organisator storage page`: Event organisers on this page will be shown in the template.
        - `Speaker storage page`: Event speakers on this page will be shown in the template.
        - `Related events storage page`: Events on this page will be shown in the template.
    - Tab `Email`
        - `Enable email notification`: If checked, you can add email notifications for chosen actions.
        - `Email address of sender`: Email from address.
        - `Name of sender`: Email from name.
        - `Email receivers`: Add as many email receivers as you want.
            - `Email for action`: Select the action, where you want to send the email.
            - `Email address of receiver`: Enter the email address of receiver (Email to address).
            - `Name of receiver`: Enter the name of the receiver (Email to name).
            - `Subject of email`: The subject of the email (Email subject).
            - `Text in email`: The text for the email (Email body).
    - Tab `Template`:
        - `Template layout`: Select a defined template (see chapter `Template layouts` how to define template layouts).
- Now a frontend user is able to add, edit and delete own records

### Template layouts

You are able to configure template layouts in TsConfig:

```
tx_mdeventmgt_frontend {
  templateLayouts {
    1 = First layout
    2 = Second layout
  }
}
```

## PSR-14 Events

Following PSR-14 events are available:

- `Mediadreams\MdEventmgtFrontend\Event\CreateActionBeforeSave`: Called just before saving a new record
- `Mediadreams\MdEventmgtFrontend\Event\CreateActionAfterPersistEvent`: Called after a new record was saved (new record Id is available)
- `Mediadreams\MdEventmgtFrontend\Event\UpdateActionBeforeSave`: Called just before an existig record will be updated
- `Mediadreams\MdEventmgtFrontend\Event\DeleteActionBeforeDelete`: Called just before a record will be deleted

### Register an example event

Add following lines in `Configuration/Services.yaml` of your own extension:

```yaml
services:
  Vendor\Extension\EventListener\MyListener:
    tags:
      - name: event.listener
        identifier: 'ext-mdnewsfrontend/enrichEvent'
        method: 'enrichEvent'
        event: Mediadreams\MdNewsfrontend\Event\CreateActionBeforeSaveEvent
```

Add the class `Vendor\Extension\EventListener\MyListener` with the method `enrichEvent` in your extension:

```php
namespace Vendor\Extension\EventListener;

use Mediadreams\MdNewsfrontend\Event\CreateActionBeforeSaveEvent;

final class MyListener
{
    public function enrichEvent(CreateActionBeforeSaveEvent $obj)
    {
        // Get event object
        /** @var \Mediadreams\MdEventmgtFrontend\Domain\Model\Event $event */
        $event = $obj->getEvent();
        $event->setTitle('Set some teaser...');

        // Get EventController
        /** @var \Mediadreams\MdEventmgtFrontend\Controller\EventController $controller */
        $controller = $obj->getNewsController();
    }
}

```

## Bugs and Known Issues
If you find a bug, it would be nice if you add an issue on [Github](https://github.com/cdaecke/md_eventmgt_frontend/issues).

# THANKS

Thanks a lot to all who make this outstanding TYPO3 project possible!

## Credits

- Extension icon was copied from [ext:sf_event_mgt](https://github.com/derhansen/sf_event_mgt) and enriched with a pen from [Font Awesome](https://fontawesome.com/icons/pencil-alt?style=solid).
