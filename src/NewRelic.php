<?php

/**
 * New Relic
 *
 * @author     Michael Rog <michael@michaelrog.com>
 * @link       https://topshelfcraft.com
 * @copyright  Copyright 2018, Top Shelf Craft (Michael Rog)
 * @see        https://github.com/TopShelfCraft/New-Relic
 */

namespace ibrahimlawal\newrelic;

use Craft;
use craft\base\Plugin;
use ibrahimlawal\newrelic\models\Settings;

/**
 * @author    Top Shelf Craft (Michael Rog)
 * @package   NewRelic
 * @since     3.0.0
 *
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class NewRelic extends Plugin
{

    /*
     * Static Properties
     * =========================================================================
     */

    /**
     * @var NewRelic
     */
    public static $plugin;

    /*
     * Public Methods
     * =========================================================================
     */

    public function init()
    {

        parent::init();
        self::$plugin = $this;

        if (extension_loaded('newrelic')) {

            if (!empty($this->getSettings()->appName)) {
                newrelic_set_appname($this->getSettings()->appName);
            }

			$request = Craft::$app->getRequest();

			if ($request->getIsConsoleRequest()) {

				/*
				 * Console requests have no concept of a URI or segments,
				 * so we'll name the transaction based on the resolved route.
				 */

				$route = ($request->resolve())[0];
				$name = "Console/{$route}";

			}
			else
			{

				/*
				 * We're in a web request, so we can name the transaction based on segments/context.
				 */

                $name = Craft::$app->getRequest()->getSegment(1);

                if (Craft::$app->getRequest()->getSegment(2)) {
                    if ($this->getSettings()->includeSegment2 === '1') {
                        $name .= "/" . Craft::$app->getRequest()->getSegment(2);
                    } else {
                        $name .= "/*";
                    }
                }
    
				if ($request->getIsLivePreview())
				{
					$name = "LivePreview/{$name}";
				}
				elseif ($request->getIsCpRequest())
				{
					$name = Craft::$app->getConfig()->getGeneral()->cpTrigger . "/{$name}";
				}

			}

            newrelic_name_transaction($name);

        }

    }

    /*
     * Protected Methods
     * =========================================================================
     */

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'new-relic-craft-3/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }

}
