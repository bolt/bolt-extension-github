<?php

namespace Bolt\Extension\Bolt\GitHub\Twig;

use Bolt\Extension\Bolt\GitHub\Extension;
use Github;
use Github\HttpClient\CachedHttpClient;
use Silex\Application;

/**
 * Twig functions for Bolt GitHub Repo
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
class GitHubExtension extends \Twig_Extension
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Github\HttpClient\CachedHttpClient
     */
    private $client = null;

    /**
     * @var \Twig_Environment
     */
    private $twig = null;

    public function __construct(Application $app)
    {
        $this->app      = $app;
        $this->config   = $this->app[Extension::CONTAINER]->config;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->twig = $environment;
    }

    /**
     * Return the name of the extension
     */
    public function getName()
    {
        return 'boltforms.extension';
    }

    /**
     * The functions we add
     */
    public function getFunctions()
    {
        return array(
            'github_collaborators' => new \Twig_Function_Method($this, 'twigCollaborators'),
            'github_contributors'  => new \Twig_Function_Method($this, 'twigContributors')
        );
    }

    /**
     *
     *
     * @return Twig_Markup
     */
    public function twigCollaborators()
    {
        $this->addTwigPath($this->app);

        $collaborators = $this->getGitHubAPI()->api('repo')->collaborators()->all($this->config['github']['org'], $this->config['github']['repo']);

        foreach ($collaborators as $collaborator) {
            try {
                $members[] = $this->getGitHubAPI()->api('user')->show($collaborator['login']);
            } catch (Exception $e) {
            }
        }
        // Get our values to be passed to Twig
        $twigvalues = array(
            'members' => $members
        );

        $html = $this->app['render']->render($this->config['templates']['collaborators'], $twigvalues);

        // Render the Twig_Markup
        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     *
     *
     * @return Twig_Markup
     */
    public function twigContributors()
    {
        $this->addTwigPath($this->app);

        $contributors = $this->getGitHubAPI()->api('repo')->contributors($this->config['github']['org'], $this->config['github']['repo']);

        foreach ($contributors as $contributor) {
            $members[] = $this->getGitHubAPI()->api('user')->show($contributor['login']);
        }

        // Get our values to be passed to Twig
        $twigvalues = array(
            'members' => $members
        );

        $html = $this->app['render']->render($this->config['templates']['contributors'], $twigvalues);

        // Render the Twig_Markup
        return new \Twig_Markup($html, 'UTF-8');
    }

    private function getGitHubAPI()
    {
        if ($this->client) {
            return $this->client;
        }

        if ($this->config['cache']) {
            $this->client = new \Github\Client();
        } else {
            // GitHub API client with cache
            $this->client = new \Github\Client(
                new Github\HttpClient\CachedHttpClient(array('cache_dir' => $this->app['paths']['cache'] . '/github'))
            );
        }

        if (isset($this->config['github']['token'])) {
            $this->client->authenticate($this->config['github']['token'], Github\Client::AUTH_HTTP_TOKEN);
        }

        return $this->client;
    }

    /**
     *
     * @param Silex\Application $app
     */
    private function addTwigPath(Application $app)
    {
        $app['twig.loader.filesystem']->addPath(dirname(dirname(__DIR__)) . '/assets');
    }
}
