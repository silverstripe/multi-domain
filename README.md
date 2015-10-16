# Multi Domains for SilverStripe
Allows multiple domains to access one CMS instance, mapping them to different sections of the hierarchy, which allows for vanity URLs. Examples:

* `example.com` -> resolves to home page
* `example.com/shop/store` -> Resolves to a Store page
* `example-store.com` -> Shows content for `example.com/shop/store`.
* `example-store.com/checkout` -> Shows content for `example.com/shop/store/checkout`

## Requirements

silverstripe/framework:3.1.*


## Configuration

Each domain is identified by a key. You must define one domain using the `primary` key to mark it as the primary domain. 

```yml
---
Name: mymultidomain
After: '#multidomain'
---
MultiDomain:
  domains:
    primary:
      hostname: 'example.com'
    store:
      hostname: 'example-store.com'
      resolves_to: 'shop/store'
```

### Options

`allow_subdomains`: If true, domain matching is subdomain agnostic, so that *anything.example.com* still maps to *example.com*, the primary domain in the above configuration.

### Whitelisting

Sometimes you may have routes that should resolve normally, and bypass the multidomain filter. In this case, for any given domain, you can specify an `allow` list.

```yml
---
Name: mymultidomain
After: '#multidomain'
---
MultiDomain:
  domains:
    primary:
      hostname: 'example.com'
    store:
      hostname: 'example-store.com'
      resolves_to: 'shop/store'
      allow:
        - 'admin/*'
        - 'Security/*'
        - 'my-custom-webhook/'
```
In the above example, any URL beginning with `admin/`, `Security/` or matching `my-custom-webhook/` will resolve on any domain.

### Forcing URLs to specific domains

Sometimes, you may have a page that sits outside the node representing a domain, but you still want it to be considered part of that domain. For this, you can use the `force` option.

```yml
---
Name: mymultidomain
After: '#multidomain'
---
MultiDomain:
  domains:
    primary:
      hostname: 'example.com'
    store:
      hostname: 'example-store.com'
      resolves_to: 'shop/store'
      force:
        'buy-now/*'
```
In the above configuration, the page `buy-now` can live in the site root, but the URL `example-store.com/buy-now`
will nonetheless resolve the page, even though the page isn't under `shop/store`.

## Why not subsites?

Subsites creates a parallel CMS instance for a given domain name. This module allows you to map domains to a specific section of the hierarchy, in the context of all your other pages.

## Why not "homepage for domain"?

That works to create a vanity URL for one page, but as soon as you go deeper into the hierarchy, you return to the native URL. A more robust solution is required for persisting the vanity URLs.

Further, this module is more extensible, allowing for collaboration with other URL-hungry modules, such as Translatable or Fluent.

