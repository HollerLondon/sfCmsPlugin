<?php

/**
 * ListingItem
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    site_cms
 * @subpackage model
 * @author     Jo Carter
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginListingItem extends BaseListingItem 
{
	/**
     * Creates an unsaved Listing item object from the listing
     *
     * @param Listing $listing
     * @return Listing
     */
    public static function createFromListing(Listing $listing) {
      $listingItem = new ListingItem();
      $listingItem->listing_id = $listing->id;
      
      return $listingItem;
    }
    
	/**
     * Update a new Listing item with a content group
     */
    public function updateNew() 
	{
      $group = contentGroup::createNew('ListingItem', get_class($this));
      $this->ContentGroup = $group;
      $this->save();
    }
    
	/**
	 * Render one of the fragments for this Listing.
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
	 * Handle when the content for the item has changed.
	 */
	public function handleContentChanged() 
	{
	    $listing = $this->Listing;
	    
      	// This removes the cached pages from both the Listing and the items
      	siteManager::getInstance()->getCache()->removePattern("Listing.{$listing->id}.*");
	}
	
	public function publish() 
	{
		$this->is_active = true;
		$this->save();
	}
	
	public function unPublish() 
	{
		$this->is_active = false;
		$this->save();
	}
	
	public function delete(Doctrine_Connection $conn = null) 
	{
	    // delete associated item content group
	    $this->ContentGroup->delete();
	    
	    // delete translations
	    $translations = $this->Translation;
	    foreach ($translations as $lang => $translation) 
		{
	    	$translation->delete();
	    	$translation->free();	
	    }
	    
	    // delete the item:
	    parent::delete($conn);
	}
}