<?php

namespace Bolt\Extension\Bolt\GitHub\Twig;

use Github\Api as GitHubApi;
use Github\Client as GitHubClient;
use Twig_Environment as TwigEnvironment;
use Twig_Markup as TwigMarkup;

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
class GitHubRuntime
{
    /** @var array */
    private $config;
    /** @var GitHubClient */
    private $api;

    /**
     * Constructor.
     *
     * @param array        $config
     * @param GitHubClient $api
     */
    public function __construct(array $config, GithubClient $api)
    {
        $this->config = $config;
        $this->api = $api;
    }

    /**
     * @param TwigEnvironment $twig
     * @param bool            $includeUserInfo
     *
     * @return TwigMarkup
     */
    public function gitHubRepoCollaborators(TwigEnvironment $twig, $includeUserInfo = false)
    {
        $gitHubParameters = $this->config['github'];
        $members = [];
        /** @var GitHubApi\Repo $apiRepo */
        $apiRepo = $this->getGitHubAPI()->api('repo');
        // Call API for org/repo collaborators
        $collaborators = $apiRepo->collaborators()->all($gitHubParameters['org'], $gitHubParameters['repo']);

        if ($includeUserInfo) {
            foreach ($collaborators as $collaborator) {
                /** @var GitHubApi\User $apiUser */
                $apiUser = $this->getGitHubAPI()->api('user');
                $members[] = $apiUser->show($collaborator['login']);
            }
        } else {
            $members = $collaborators;
        }

        $context = [
            'members' => $members,
        ];
        $html = $twig->render($this->config['templates']['collaborators'], $context);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * @param TwigEnvironment $twig
     * @param bool            $includeUserInfo
     *
     * @return TwigMarkup
     */
    public function gitHubRepoContributors(TwigEnvironment $twig, $includeUserInfo = false)
    {
        $gitHubParameters = $this->config['github'];
        $members = [];
        /** @var GitHubApi\Repo $apiRepo */
        $apiRepo = $this->getGitHubAPI()->api('repo');
        // Call API for org/repo contributors
        $contributors = $apiRepo->contributors($gitHubParameters['org'], $gitHubParameters['repo']);

        // If the caller wants user info added, give it to them
        if ($includeUserInfo) {
            foreach ($contributors as $contributor) {
                /** @var GitHubApi\User $apiUser */
                $apiUser = $this->getGitHubAPI()->api('user');
                $members[] = $apiUser->show($contributor['login']);
            }
        } else {
            $members = $contributors;
        }

        $context = [
            'members' => $members,
        ];
        $html = $twig->render($this->config['templates']['contributors'], $context);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Get the user info for a GitHub user.
     *
     * @param string $userName
     *
     * @return array
     */
    public function gitHubUser($userName)
    {
        /** @var GitHubApi\User $apiUser */
        $apiUser = $this->getGitHubAPI()->api('user');

        return $apiUser->show($userName);
    }

    /**
     * Get the public events for a GitHub user.
     *
     * @param string $userName
     *
     * @return array
     */
    public function gitHubUserEvents($userName)
    {
        /** @var GitHubApi\User $apiUser */
        $apiUser = $this->getGitHubAPI()->api('user');

        return $apiUser->publicEvents($userName);
    }

    /**
     * Get a valid GitHub API object.
     *
     * @return GitHubClient
     */
    private function getGitHubAPI()
    {
        return $this->api;
    }
}
