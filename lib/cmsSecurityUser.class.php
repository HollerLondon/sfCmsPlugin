<?php 
/**
 * Security user (required sfDoctrineGuardPlugin) to allow part credentials
 * and to manage user sites
 * 
 * @author Jo Carter
 *
 */
class cmsSecurityUser extends sfGuardSecurityUser
{
  /**
   * Overriding hasCredential to allow part permissions
   * 
   * e.g:
   *  site.admin
   *  site.edit
   *  site.publish
   *  
   * All return true when credential checked is 'site'
   * 
   * @param mixed $credential
   * @param boolean $useAnd
   * @see user/sfGuardSecurityUser::hasCredential()
   */
  public function hasCredential($credential, $useAnd = true)
  {
    $hasCredential = parent::hasCredential($credential, $useAnd);
    
    if (!$hasCredential)
    {
      if (!is_array($credential)) $credential = array($credential);
      
      // Check permission part
      // So if just require 'site' permission returns true with
      // site.admin, site.edit, site.publish
      $credentials = $this->getCredentials();
      
      foreach ($credential as $part)
      {
        foreach ($credentials as $name)
        {
          if ($part === substr($name, 0, strlen($part))) $hasCredential = true;
        }
      }
      
      if (sfConfig::get('sf_logging_enabled')) 
      {
        sfContext::getInstance()->getLogger()->info(
                    sprintf('Checking part user credentials for "%s", result is "%s"', 
                    implode(',', $credential), 
                    ($hasCredential ? 'true' : 'false'))
        );
      }
    }
    
    return $hasCredential;
  }
  
  /**
   * Get sites for a user (if no sites assigned through a group, assume access to all).
   * Stores in session for when needed
   * 
   * @return array of site identifiers
   */
  public function getSites()
  {
    $sites  = $this->getAttribute('sites', array(), 'sfGuardSecurityUser');
    
    if (empty($sites))
    {
      foreach ($this->getGuardUser()->getGroups() as $group)
      {
        foreach ($group->getSites() as $site)
        {
          $sites[] = $site->site;
        }
      }
      
      if (empty($sites))
      {
        $sites = SiteTable::getInstance()->getSiteIdentifiers();
        
        if (!is_array($sites)) $sites = array($sites);
      }
    }
    
    $this->setAttribute('sites', $sites, 'sfGuardSecurityUser');
    
    return $sites;
  }
}
