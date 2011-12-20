<?php

/**
 * Page
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    site_cms
 * @subpackage model
 * @author     Jo Carter
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginPage extends BasePage
{
  /**
   * Creates an unsaved Page object from the Sitetree
   *
   * @param Sitetree $sitetree
   * @return Page
   */
  public static function createFromSitetree($sitetree)
  {
    $page = new Page();
    $page->sitetree_id = $sitetree->id;

    return $page;
  }

  /**
   * Update a new Page with a content group
   */
  public function updateNew()
  {
    $group = ContentGroup::createNew('Page');
    $this->ContentGroup = $group;
    $this->save();
  }

  
  /**
   * Render the template for this page
   * 
   * @param boolean $tryUseCache
   * @param Sitetree $sitetreeNode
   * @return string (Partial)
   */
  public function render($tryUseCache = false, $sitetreeNode = null)
  {
    $contentManager = pageManager::getInstance();
    $templateSlug = $this->template;
    
    if (is_null($sitetreeNode)) $sitetreeNode = $this->Sitetree;
    $partialVariables = array('sitetree' => $sitetreeNode);

    // Initialise content group
    $contentGroup = $this->initContentGroup();

    // cache
    $useCache = false;

    if ($tryUseCache)
    {
      // See if we should be using the cache for this template
      if ($contentManager->getTemplateDefinitionAttribute($templateSlug, 'cacheable', false))
      {
        $useCache = true;
        $culture = sfContext::getInstance()->getUser()->getCulture();
        $partialVariables['cacheName'] = "contentGroup.{$contentGroup->id}.{$culture}";
      }
    }
    
    $partialVariables['useCache'] = $useCache;

    // template location
    $partialVariables['templateFileLocation'] = $contentManager->getTemplateFileLocation($templateSlug);

    // Add stylesheets etc to response
    $response = sfContext::getInstance()->getResponse();
    
    if ($stylesheets = $contentManager->getTemplateDefinitionAttribute($templateSlug, 'stylesheets'))
    {
      foreach ($stylesheets as $stylesheet) $response->addStylesheet($stylesheet, '', array('media' => $media));
    }
    
    if ($javascripts = $contentManager->getTemplateDefinitionAttribute($templateSlug, 'javascripts'))
    {
      foreach ($javascripts as $javascript) $response->addJavascript($javascript, 'last');
    }

    $partialVariables['page']         = $this;
    $partialVariables['contentGroup'] = $contentGroup;
    
    if (!function_exists('get_partial'))
    {
      sfApplicationConfiguration::getActive()->loadHelpers('Partial');
    }

    // This is ugly - but we don't want the partial to be escaped as it contains HTML
    $strategy = sfConfig::get('sf_escaping_strategy');
    sfConfig::set('sf_escaping_strategy', false);
    $content = get_partial('pageDisplay/render', $partialVariables);
    sfConfig::set('sf_escaping_strategy', $strategy);
    
    return $content;
  }
  
  
  /**
   * Initialise the contentGroup
   * 
   * @return contentGroup
   */
  protected function initContentGroup()
  {
    $contentGroup = $this->ContentGroup;
    $contentGroup->initialiseForRender(sfContext::getInstance()->getUser()->getCulture());
    return $contentGroup;
  }
  
  /**
   * Render one of the content blocks for this Page
   *
   * The ContentGroup must be initialised first.
   *
   * @param string $identifier
   * @param array $extraParams
   */
  public function renderContent($identifier, $extraParams = array())
  {
    return $this->ContentGroup->renderContent($identifier, $extraParams);
  }

  /**
   * Handle the node events for the site module
   *
   * @param siteEvent $event
   */
  public static function siteEventHandler($event)
  {
    if ($event->getName() == siteEvent::SITETREE_DELETE)
    {
      // node has been deleted - delete the contentGroup and content too
      $sitetree = $event->getSubject();
      $page = PageTable::getInstance()->findOneBySitetreeId($sitetree->id);

      if (!$page)
      {
        // there is no Page at this node to delete
        return;
      }

      $page->delete();
    }
  }

  /**
   * What to do when the content is updated
   */
  public function handleContentChanged()
  {
    // This removes the cached Pages
    siteManager::getInstance()->getCache()->removePattern("Page.{$this->id}.*");
  }

  /**
   * Delete
   *
   * @param Doctrine_Connection $conn
   */
  public function delete(Doctrine_Connection $conn = null)
  {
    parent::delete($conn);
    
    // delete associated contentGroup
    $this->ContentGroup->delete();
  }
}
