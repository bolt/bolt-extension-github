<?php

namespace Bolt\Extension\Bolt\GitHub\Twig;

use Github\Api as GithubApi;
use Github\Client as GithubClient;
use Pimple as Container;
use Silex\Application;
use Twig_Environment as TwigEnvironment;
use Twig_Extension as TwigExtension;
use Twig_Markup as TwigMarkup;
use Twig_SimpleFunction as TwigSimpleFunction;

/**
 * Twig functions for Bolt GitHub Repo
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
class GitHubExtension extends TwigExtension
{
    /** @var array */
    private $config;
    /** @var Container */
    private $api;

    /**
     * Constructor.
     *
     * @param array     $config
     * @param Container $api
     *
     * @internal param Application $app
     */
    public function __construct(array $config, Container $api)
    {
        $this->config = $config;
        $this->api = $api;
    }

    /**
     * Return the name of the extension
     */
    public function getName()
    {
        return 'GitHubAPI';
    }

    /**
     * The functions we add
     */
    public function getFunctions()
    {
        $env  = ['needs_environment' => true];

        return [
            new TwigSimpleFunction('github_collaborators', [$this, 'gitHubRepoCollaborators'], $env),
            new TwigSimpleFunction('github_contributors',  [$this, 'gitHubRepoContributors'], $env),
            new TwigSimpleFunction('github_user',          [$this, 'gitHubUser']),
            new TwigSimpleFunction('github_user_events',   [$this, 'gitHubUserEvents']),
        ];
    }

    /**
     * @param TwigEnvironment $twig
     * @param bool            $includeUserInfo
     *
     * @return TwigMarkup
     */
    public function gitHubRepoCollaborators(TwigEnvironment $twig, $includeUserInfo = false)
    {
        $members = [];
        /** @var GithubApi\Repo $apiRepo */
        $apiRepo = $this->getGitHubAPI()->api('repo');
        // Call API for org/repo collaborators
        $collaborators = $apiRepo->collaborators()->all($this->config['github']['org'], $this->config['github']['repo']);

        if ($includeUserInfo) {
            foreach ($collaborators as $collaborator) {
                /** @var GithubApi\User $apiUser */
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
        $members = [];
        /** @var GithubApi\Repo $apiRepo */
        $apiRepo = $this->getGitHubAPI()->api('repo');
        // Call API for org/repo contributors
        $contributors = $apiRepo->contributors($this->config['github']['org'], $this->config['github']['repo']);

        // If the caller wants user info added, give it to them
        if ($includeUserInfo) {
            foreach ($contributors as $contributor) {
                /** @var GithubApi\User $apiUser */
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
        /** @var GithubApi\User $apiUser */
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
        /** @var GithubApi\User $apiUser */
        $apiUser = $this->getGitHubAPI()->api('user');

        return $apiUser->publicEvents($userName);
    }

    /**
     * Get a valid GitHub API object.
     *
     * @return GithubClient
     */
    private function getGitHubAPI()
    {
        return $this->api['client'];
    }
}
