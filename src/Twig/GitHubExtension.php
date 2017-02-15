<?php

namespace Bolt\Extension\Bolt\GitHub\Twig;

use Twig_Extension as TwigExtension;
use Twig_SimpleFunction as TwigSimpleFunction;

/**
 * Twig functions for Bolt GitHub Repo
 *
 * Copyright (C) 2014-2017 Gawain Lynch
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
class GitHubExtension extends TwigExtension
{
    /**
     * The functions we add
     */
    public function getFunctions()
    {
        $env  = ['needs_environment' => true];

        return [
            new TwigSimpleFunction('github_collaborators', [GitHubRuntime::class, 'gitHubRepoCollaborators'], $env),
            new TwigSimpleFunction('github_contributors',  [GitHubRuntime::class, 'gitHubRepoContributors'], $env),
            new TwigSimpleFunction('github_user',          [GitHubRuntime::class, 'gitHubUser']),
            new TwigSimpleFunction('github_user_events',   [GitHubRuntime::class, 'gitHubUserEvents']),
        ];
    }
}
