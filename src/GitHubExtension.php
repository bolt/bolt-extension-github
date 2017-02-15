<?php

namespace Bolt\Extension\Bolt\GitHub;

use Bolt\Extension\SimpleExtension;
use Doctrine\Common\Cache\FilesystemCache;
use Github\Client as GithubClient;
use Github\HttpClient\HttpClient as GithubHttpClient;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Plugin\Cache\CachePlugin;
use Guzzle\Plugin\Cache\DefaultCacheStorage;
use Silex\Application;

/**
 * Interface to query Bolt's GitHub account via the GitHub API
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
class GitHubExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    protected function registerServices(Application $app)
    {
        $app['github.api.client.cache'] = $app->share(
            function ($app) {
                $fsCache = new FilesystemCache($app['resources']->getPath('cache/github'));
                $cacheAdapter = new DoctrineCacheAdapter($fsCache);
                $storage = ['storage' => new DefaultCacheStorage($cacheAdapter)];
                $cachePlugin = new CachePlugin($storage);

                return $cachePlugin;
            }
        );

        $app['github.api.client'] = $app->share(
            function ($app) {
                $config = $this->getConfig();
                $options = [
                    'base_url'    => 'https://api.github.com/',
                    'user_agent'  => 'Bolt GitHub API (http://github.com/GawainLynch/bolt-extension-github)',
                    'timeout'     => 10,
                    'api_limit'   => 5000,
                    'api_version' => 'v3',
                    'cache_dir'   => $app['path_resolver']->resolve('%cache%/github'),
                ];
                $client = new GithubClient(new GithubHttpClient($options));

                /*
                 * The API comes with a cache extension, but it fails in Guzzle.
                 * @see https://github.com/KnpLabs/php-github-api/issues/116
                 *
                 * We are using the preferable Guzzle cache plugin that seems more stable
                 */
                if ($config['cache']) {
                    $client->getHttpClient()->addSubscriber($app['github.api.client.cache']);
                }

                if ($config['github']['token'] !== null) {
                    $client->authenticate($config['github']['token'], GithubClient::AUTH_HTTP_TOKEN);
                }

                return $client;
            }
        );

        $app['twig.runtime.github'] = function ($app) {
            return new Twig\GitHubRuntime($this->getConfig(), $app['github.api.client']);
        };

        $app['twig.runtimes'] = $app->extend(
            'twig.runtimes',
            function (array $runtimes) {
                return $runtimes + [
                        Twig\GitHubRuntime::class => 'twig.runtime.github',
                    ];
            }
        );

        $app['twig'] = $app->extend(
            'twig',
            function (\Twig_Environment $twig) use ($app) {
                $twig->addExtension(new Twig\GitHubExtension());

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
                'org'   => 'bolt',
                'repo'  => 'bolt',
                'token' => null,
            ],
            'cache'     => true,
            'templates' => [
                'collaborators'  => 'members.twig',
                'contributors'   => 'members.twig',
            ],
        ];
    }
}
