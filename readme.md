GitHub API for Bolt
-------------------

Implementation of the GitHub API as a Bolt extension.

Set up
======

Set the following parameters (at a minimum) in the `app/config/extensions/github.bolt.yml` file:

```yaml
github:
  org: bolt
  repo: bolt
```

Template Use
============

The following functions are available for inclusion in templates:

```
{{ github_collaborators() }}
{{ github_contributors() }}
{{ github_user(user) }}
```

Both `github_collaborators` and `github_contributors` take an optional Boolean where true will lookup
additional information about the members.  

**Note** these lookups affect your GitHub API rate limit quite severly, especially if caching is not enabled!