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

## Why not subsites?

Subsites creates a parallel CMS instance for a given domain name. This module allows you to map domains to a specific section of the hierarchy, in the context of all your other pages.

## Why not "homepage for domain"?

That works to create a vanity URL for one page, but as soon as you go deeper into the hierarchy, you return to the native URL. A more robust solution is required for persisting the vanity URLs.

Further, this module is more extensible, allowing for collaboration with other URL-hungry modules, such as Translatable or Fluent.

