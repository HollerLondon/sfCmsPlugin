<?php

/**
 * PluginIframe
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    site_cms
 * @subpackage model
 * @author     Jo Carter
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginIframe extends BaseIframe
{
  public static function createFromSitetree($sitetree)
  {
    $iframe              = new Iframe();
    $iframe->sitetree_id = $sitetree->id;
    
    $config              = sfConfig::get('app_site_iframe', array());
    $layouts             = (isset($config['layouts']) ? $config['layouts'] : array());
    
    if (!empty($layouts))
    {
      $layouts        = array_keys($layouts);
      $iframe->layout = $layouts[0];
    }

    return $iframe;
  }
  
  /**
   * Create a copy of this iframe, exactly the same but linked to the given sitetree.
   *
   * @param Sitetree $copyToSitetree
   * @return Iframe
   */
  public function createCopy($copyToSitetree) 
  {
    $copy             = $this->copy(true);
    
    $copy->Sitetree   = $copyToSitetree;
    $copy->created_at = $copy->updated_at = null;
    
    $copy->save();
    
    return $copy;
  }
  
  /**
   * Handle iframe events
   * 
   * @static
   * @param siteEvent $event
   */
  public static function siteEventHandler(siteEvent $event)
  {
    if ($event->getName() == siteEvent::SITETREE_ROUTING)
    {
      // handle the routing... i.e: register our routes.
      $sitetree     = $event->getSubject();
      $params       = $event->getParameters();
      $routingProxy = $params['routingProxy'];
      $urlStack     = $params['urlStack'];

      $nodeUrl      = Sitetree::makeUrl($sitetree, $urlStack);

      // add in index route
      $routingProxy->addRoute(
            $sitetree,
            '',
            $nodeUrl,
            array('module' => 'iframeDisplay', 'action' => 'index')
      );

      // add in iframe render route
      $routingProxy->addRoute(
            $sitetree,
            'render',
            '/feature/' . $sitetree->getRouteName(),
            array('module' => 'iframeDisplay', 'action' => 'render')
      );
    }
    else if ($event->getName() == siteEvent::SITETREE_DELETE) 
    {
      // node has been deleted
      $sitetree = $event->getSubject();
   
      // handle delete - delete iframe link if sitetree node deleted (if not cascaded)
      $iframe = IframeTable::getInstance()->findOneBySitetreeId($sitetree->id);
      if ($iframe) $iframe->delete();
    } 
    else if ($event->getName() == siteEvent::SITETREE_COPY) 
    {
      $copyFromSitetree = $event->getSubject();
      $params           = $event->getParameters();
      
      if (!$copyToSitetree = @$params['copyTo']) 
      {
        throw new sfException('No sitetree to copy to');
      }
      
      // get the iframe from the old Sitetree
      $fromFrame = siteManager::getInstance()->loadItemFromSitetree('Iframe', $copyFromSitetree);
      
      if (!$fromFrame) 
      {
        // there was no iframe at the old sitetree, maybe it hadn't been created yet.
        return;
      }
      
      $fromFrame->createCopy($copyToSitetree);
    }
  }
}
