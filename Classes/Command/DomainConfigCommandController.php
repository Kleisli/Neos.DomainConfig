<?php
namespace Kleisli\Neos\DomainConfig\Command;

/*
 * This file is part of the Kleisli.Neos.DomainConfig package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Neos\Command\DomainCommandController;
use Neos\Neos\Domain\Model\Domain;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;

/**
 * @Flow\Scope("singleton")
 */
class DomainConfigCommandController extends CommandController
{

    /**
     * @Flow\InjectConfiguration(path = "sites")
     * @var array
     */
    protected $sitesConfig;

    /**
     * @var DomainRepository
     * @Flow\Inject
     */
    protected $domainRepository;

    /**
     * @var SiteRepository
     * @Flow\Inject
     */
    protected $siteRepository;

    /**
     * @var DomainCommandController
     * Flow\Inject
     */
    protected $domainCommandController;

    /**
     * An example command
     *
     * The comment of this command method is also used for Flow's help screens. The first line should give a very short
     * summary about what the command does. Then, after an empty line, you should explain in more detail what the command
     * does. You might also give some usage example.
     *
     * It is important to document the parameters with param tags, because that information will also appear in the help
     * screen.
     *
     * @return void
     */
    public function listCommand()
    {
        foreach ($this->sitesConfig as $site => $config){
            $this->outputLine('Configured domains for site "%s"', array($site));
            $this->outputLine('Primary domain: %s', array($config['primary']));
            $this->outputLine('Aliases %s', array(implode(", ", $config['aliases'])));
            $this->outputLine();
        }

    }


    /**
     * An example command
     *
     * The comment of this command method is also used for Flow's help screens. The first line should give a very short
     * summary about what the command does. Then, after an empty line, you should explain in more detail what the command
     * does. You might also give some usage example.
     *
     * It is important to document the parameters with param tags, because that information will also appear in the help
     * screen.
     *
     * @return void
     */
    public function applyCommand()
    {
        $sites = $this->siteRepository->findAll();
        /** @var Site $site */
        foreach ($sites as $site){
            $this->outputLine('Apply config for site <b>%s</b>', array($site->getNodeName()));
            $this->outputLine('Reset domains...');

            // reset primary domain
            $site->setPrimaryDomain(null);
            $this->siteRepository->update($site);

            // reset active domains
            foreach ($site->getDomains() as $domain){
                $domain->setActive(false);
                $this->domainRepository->update($domain);
            }
            $siteConfig = $this->sitesConfig[$site->getNodeName()];
            $allUrls = array_merge($siteConfig['aliases'], [$siteConfig['primary']]);
            foreach ($allUrls as $url){
                $host = parse_url($url, PHP_URL_HOST);
                // find domain
                /** @var Domain $domain */
                $domain = $this->domainRepository->findOneByHostname($host);
                if($domain == null){
                    $domain = new Domain();
                    if (parse_url($url, PHP_URL_SCHEME) !== null) {
                        $domain->setScheme(parse_url($url, PHP_URL_SCHEME));
                    }
                    if (parse_url($url, PHP_URL_PORT) !== null) {
                        $domain->setPort(parse_url($url, PHP_URL_PORT));
                    }
                    $domain->setSite($site);
                    $domain->setHostname($host);

                    $this->domainRepository->add($domain);
                    $this->outputLine('<success>Added %s</success>', array($url));
                }else{
                    $domain->setActive(true);
                    $this->domainRepository->update($domain);
                    $this->outputLine('<success>Activated %s</success>', array($url));
                }
            }

            $primaryHost = parse_url($siteConfig['primary'], PHP_URL_HOST);
            // find domain
            /** @var Domain $primaryDomain */
            $primaryDomain = $this->domainRepository->findOneByHostname($primaryHost);
            if ($site->getPrimaryDomain() !== $primaryDomain) {
                $site->setPrimaryDomain($primaryDomain);
                $this->siteRepository->update($site);
            }
            $this->outputLine('<success>Set %s</success> as primary domain', array($siteConfig['primary']));
            $this->outputLine();

        }

    }
}
