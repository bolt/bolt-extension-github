<?php

namespace Bolt\Extension\Bolt\GitHub;

use Bolt\Extension\SimpleExtension;
use Silex\Application;

/**
 * Interface to query Bolt's GitHub account via the GitHub API
 *
 * Copyright (C) 2014-2016 Gawain Lynch
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
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class GitHubExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    protected function registerServices(Application $app)
    {
        $app['twig'] = $app->extend(
            'twig',
            function (\Twig_Environment $twig) use ($app) {
                $twig->addExtension(new Twig\GitHubExtension($app));

                return $twig;
            }
        );
    }

    /**
     * Set the defaults for configuration parameters
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return [
            'github' => [
                'org'  => 'bolt',
                'repo' => 'bolt',
            ],
            'cache'     => true,
            'templates' => [
                'collaborators'  => 'members.twig',
                'contributors'   => 'members.twig',
            ],
        ];
    }
}
