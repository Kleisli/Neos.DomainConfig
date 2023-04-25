# Kleisli.Neos.DomainConfig
Adjust active domains in Neos CMS according to the Flow context

## Configuration

Settings.DomainConfig.yaml:

```yaml
Kleisli:
  Neos:
    DomainConfig:
      sites:
         # a site id
         mysite:
           # the domain to be used as primary domain for this site
           primary: 'https://mysite.ddev.site'
           # optional aliases for this site
           aliases: []
```

## Commands
### `./flow domainconfig:list`
List the domains that would be applied in the current Flow context.

### `./flow domainconfig:apply`
Apply the domains for the current flow context. If the domain doesn't exist, it will be added, otherwise it will just be activated.

## Kudos
The development of this package has significantly been funded by [Profolio](https://www.profolio.ch/) - a digital platform for career choice & career counseling
