<?php

/**
 * ContentBlock
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    site_cms
 * @subpackage model
 * @author     Jo Carter
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginContentBlock extends BaseContentBlock 
{
	/**
     * The definition array for this Content block
     *
     * @var array
     */
    protected $definition = null;
    
    /**
     * @var mixed
     */
    protected $currentVersionsCache = array();
    
    /**
     * Create a new Content block for the given identifier + Content group.
     * 
     * @todo Does not check that a Content block with that identifier already exists
     * in the group.
     *
     * @param string $identifier
     * @param ContentGroup $contentGroup
     * @return ContentBlock
     */
    public static function createFromIdentifier($identifier, $contentGroup) 
	{        
        $definition = $contentGroup->getBlockDefinition($identifier);
        $contentBlock = new ContentBlock();
        $contentBlock->setDefinition($definition);
        $contentBlock->ContentGroup = $contentGroup;
        $contentBlock->identifier = $identifier;
        $contentBlock->type = $definition['type'];
        $contentBlock->save();
        
        return $contentBlock;  
    }
    
	/**
     * Make the given version the current one
     *
     * @param ContentBlockVersion $contentBlockVersion
     * @return ContentBlockCurrentVersion
     */
    public function makeVersionCurrent($contentBlockVersion) 
	{
        $lang = $contentBlockVersion->lang;
        
        // load up the ContentBlockVersion for this ContentBlock/lang
        $contentBlockCurrentVersion = ContentBlockCurrentVersionTable::getInstance()->findCurrentVersion($this->id, $lang);
        
        if (!$contentBlockCurrentVersion) 
		{
            $contentBlockCurrentVersion = ContentBlockCurrentVersion::createNew($contentBlock, $lang);
        }
        
        if ($contentBlockCurrentVersion->Content_block_version_id == $contentBlockVersion->id) 
		{
            // this version is already the current one
            return $contentBlockCurrentVersion;
        }
        
        $contentBlockCurrentVersion->Version = $contentBlockVersion;
        $contentBlockCurrentVersion->content_block_version_id = $contentBlockVersion->id;
        $contentBlockCurrentVersion->save();
        
        // clear the "cache" of the current version data
        $this->currentVersionsCache = array();
        
        return $contentBlockCurrentVersion;
    }
    
    /**
     * Set the definition for this Content block.  This is used for improving 
     * efficiency, the Content block can get its own definition via getDefinition()
     * if this is not called.
     *
     * @param array $v
     */
    public function setDefinition($v) 
	{
        $this->definition = $v;
    }
    
    /**
     * Get the definition array for this Content block
     *
     * @return array
     */
    public function getDefinition() 
	{
        if ($this->definition === null) 
		{
            $this->definition = $this->ContentGroup->getBlockDefinition($this->identifier);
        }
        
        return $this->definition;
    }  
    
    /**
     * Get an entry from the definition array for this Content block
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getDefinitionParam($name, $default = null) 
	{
        $definition = $this->getDefinition();
        
        return (isset($definition[$name]) ? $definition[$name] : $default);
    }
    
    
    /**
     * Is this Content block using a language, or is it the same across all languages?
     *
     * @return boolean
     */
    public function useLang() 
	{
        return $this->getDefinitionParam('use_lang', true);
    }
    
    /**
     * Get the current lang.  Will return null if we're not using 
     * one for this Content block
     *
     * @return string
     */
    public function getCurrentLang() 
	{
        return ($this->useLang() ? $this->ContentGroup->getCurrentLang() : null);
    }

    /**
     * Returns the current ContentBlockVersion for the current lang.
     *
     * @return ContentBlockVersion
     */
    public function getCurrentVersion() 
	{
        $lang = $this->getCurrentLang();
    
        return $this->getVersionForLang($lang);
    }   
     
    /**
     * Returns the newest ContentBlockVersion for the current lang.
     *
     * @return ContentBlockVersion
     */
    public function getNewestVersion() 
	{
        $lang = $this->getCurrentLang();
    
        return $this->getNewestVersionForLang($lang);
    }
    
    /**
     * Returns a specified ContentBlockVersion for the current lang
     * 
     * @return ContentBlockVersion
     */
    public function getSpecifiedVersion($versionId) 
	{
    	$lang = $this->getCurrentLang();
    	
    	return $this->getSpecifiedVersionForLang($lang, $versionId);
    }
    
    /**
     * Loads the currently live version for the given language
     * 
     * @param string $lang
     * @return ContentBlockVersion
     */
    public function getVersionForLang($lang) 
	{  
        $isLoaded = ($lang === null ? (!is_array($this->currentVersionsCache)) : isset($this->currentVersionsCache[$lang]));
        
        if (!$isLoaded) 
		{
            $id = $this->id;
            $contentBlockVersion = ContentBlockVersionTable::getInstance()->getCurrentVersion($id, $lang);
            
            if ($contentBlockVersion) 
			{
                $contentBlockVersion->ContentBlock = $this;
            } 
            else 
			{
                $contentBlockVersion = ContentBlockVersion::createInitialVersion($this, $lang);
            }
            
            $this->setCurrentVersionsCache($contentBlockVersion, $lang);
        } 

        if ($lang === null) 
		{
            return $this->currentVersionsCache;
        } 
        else 
		{
            return $this->currentVersionsCache[$lang];
        }
    } 
    
    /**
     * Loads the newest version for the given lang
     * 
     * @param string $lang
     * @return ContentBlockVersion
     */
    public function getNewestVersionForLang($lang) 
	{
        $id = $this->id;
        $contentBlockVersion = ContentBlockVersionTable::getInstance()->getNewestVersion($id, $lang);
        
        if ($contentBlockVersion) 
		{
            $contentBlockVersion->ContentBlock = $this;
            return $contentBlockVersion;
        } 
        else 
		{
            return null;
        }
    }
    
    /**
     * Load specified version for given lang
     * 
     * @param string $lang
     * @param int $versionId
     * @return ContentBlockVersion
     */
    public function getSpecifiedVersionForLang($lang, $versionId) 
	{
    	$id = $this->id;
        $contentBlockVersion = ContentBlockVersionTable::getInstance()->getSpecifiedVersion($id, $lang, $versionId);
        
        if ($contentBlockVersion) 
		{
            $contentBlockVersion->ContentBlock = $this;
            return $contentBlockVersion;
        } 
        else 
		{
            return null;
        }
    }
    
    /**
     * Set a ContentBlockVersion in the currentVersion cache
     *
     * @param ContentBlockVersion $contentBlockVersion
     * @param string $lang
     */
    public function setCurrentVersionsCache($contentBlockVersion, $lang = null) 
	{
        if ($lang === null) 
		{
            $this->currentVersionsCache = $contentBlockVersion;
        } 
        else 
		{
            $this->currentVersionsCache[$lang] = $contentBlockVersion;
        }
    }
    
	/**
     * Get an efficient version history as an array
     *
     * @param string $lang
     * @param integer $limit
     * @return array
     */
    public function getEfficientVersionHistoryWithUsers($lang, $limit = 10) 
	{
        return ContentBlockVersionTable::getInstance()->getEfficientVersionHistoryWithUsers($this->id, $lang, $limit);
    }
    
	/**
     * @param boolean $deep
     * @see Doctrine_Record::free()
     */
    public function free($deep = false) 
	{
        unset($this->currentVersionsCache);
        parent::free($deep);
    }
}