# Hierarchy Extension for Bolt 5
Hierarchical content using `menu.yml` for Bolt 5.


## Configuration

* `menu`: List of menus to check for.


## Twig Functions and Filters

Generating nice URLs like `/foo/bar` requires you to use the `hMenu` function and the `hLink` filter.

* `hMenu('main')`: Use this function to get nice URLs in your menus.
* `getParent(record)`
* `getParents(record)`
* `getSiblings(record)`
* `getChildren(record)`
* `record|hLink`: Use this filter on records to get nice URLs.

## Routes

Add the following to your `routes.yaml` to allow nice URLs.

```
hierarchicalRoute:
    resource: 'TwoKings\Hierarchy\Controller\Controller'
    type: annotation
```
