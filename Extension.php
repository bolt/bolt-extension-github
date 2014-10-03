<?php

namespace Bolt\Extension\Bolt\GitHub;

use Bolt;

/**
 * Interface to query Bolt's GitHub account via the GitHub API
 *
 * Copyright (C) 2014 Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class Extension extends \Bolt\BaseExtension
{
    /**
     * Extension name
     *
     * @var string
     */
    const NAME = "GitHub";

    /**
     * Extension's service container
     *
     * @var string
     */
    const CONTAINER = 'extensions.GitHub';

    public function getName()
    {
        return Extension::NAME;
    }

    public function initialize()
    {

        /*
         * Config
         */
        $this->setConfig();

        /*
         * Backend
         */
        if ($this->app['config']->getWhichEnd() == 'backend') {
            //
        }

        /*
         * Frontend
         */
        if ($this->app['config']->getWhichEnd() == 'frontend') {
            // Twig functions
            $this->app['twig']->addExtension(new Twig\GitHubExtension($this->app));
        }
    }

    /**
     * Post config file loading configuration
     *
     * @return void
     */
    private function setConfig()
    {
        //
    }

    /**
     * Set the defaults for configuration parameters
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return array(
        );
    }

}
