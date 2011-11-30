<?php
$treeNodes = $sf_data->getRaw('treeNodes');

// use this to store a global var for our tree rendering functions
sfConfig::set('site_hack_entireSitetree', $treeNodes);
?>

<div id="sf_admin_container">

  <h1>Sitetree</h1>

  <?php if ($sf_user->hasFlash('error')) : ?>
    <div class="error"><?php echo $sf_user->getFlash('error'); ?></div>
  <?php endif; ?>
  
  <?php if ($sf_user->hasFlash('notice')) : ?>
    <div class="notice"><?php echo $sf_user->getFlash('notice'); ?></div>
  <?php endif; ?>
  
  <div id="sf_admin_content">
    <div class="sf_admin_list">
      <?php if (count($treeNodes) > 0) 
      {
        function sitetree_manager_node($sitetree) 
        {
          $user = sfContext::getInstance()->getUser();
          $canAdmin = $user->isSuperAdmin();
          
          $name = esc_entities($sitetree->title);
          $out = "<span class=\"lnk\">" . $name . "</span>";
          $class = '';
          $content = '';
      
          if ($sitetree->isManagedModule()) 
          {
            $moduleDefinition = $sitetree->getModuleDefinition();
            
            if ('sitetree/index' != $moduleDefinition['admin_url'])
            {
              $out = '<span class="lnk left">' . link_to(
                $name,
                $moduleDefinition['admin_url'] . "?routeName=$sitetree->route_name"
              ) . "</span>";
            }
          }
      
          if ($sitetree->is_hidden && !$sitetree->is_deleted) 
          {
            $class .= ' hidden';
            $content .= ' HIDDEN';
          }
      
          if ($sitetree->is_locked && !$sitetree->is_deleted) 
          {
            $class .= ' locked';
            $content .= ' LOCKED';
          }
          
          if (!$sitetree->is_active && !$sitetree->is_deleted) 
          {
            $class .= ' notpublished';
            $content .= ' NOT LIVE';
          }
          
          if ($sitetree->is_deleted) 
          {
            $class .= ' deleted';
            $content .= ' DELETED';
          }
          
          if (empty($class)) $class = 'blank';
          
          $out .= '<span class="right nodeinfo '.trim($class).'">'.trim($content).'&nbsp;</span>';
          
          $out .= '<span class="right">';
          if (!$sitetree->getNode()->isRoot() && !$sitetree->is_locked) $out .= '<a href="' . url_for('sitetree/delete?id='.$sitetree->id) . '" class="ui-icon ui-icon-close delete_sitetree" title="delete"><img src="/sfDoctrinePlugin/images/delete.png" /></a>';
          if ($sitetree->is_deleted && $canAdmin) $out .= '<a href="' . url_for('sitetree/restore?id='.$sitetree->id) . '" class="ui-icon ui-icon-check" title="restore"><img src="/sfDoctrinePlugin/images/tick.png" /></a>';
          if (!$sitetree->is_deleted) $out .= '<a href="' . url_for('sitetree/edit?id='.$sitetree->id) . '" class="ui-icon ui-icon-pencil" title="edit"><img src="/sfDoctrinePlugin/images/edit.png" /></a>';
          if (!$sitetree->is_deleted) $out .= '<a href="' . url_for('sitetree/create?parent='.$sitetree->id) . '" class="ui-icon ui-icon-plus" title="add"><img src="/sfDoctrinePlugin/images/new.png" /></a>';
          if (!$sitetree->is_active && !$sitetree->is_deleted) $out .= '<a href="' . url_for('sitetree/publish?id='.$sitetree->id) . '" class="ui-icon ui-icon-star" title="publish"><img src="/sfDoctrinePlugin/images/list.png" /></a>';
          $out .= '</span>';
      
          return $out;
        }
      
        function sitetree_manager_li($node) 
        {
          $manager = siteManager::getInstance();
          $user = sfContext::getInstance()->getUser();
          
          $canAdmin = $user->isSuperAdmin();
          $entireSitetree = sfConfig::get('site_hack_entireSitetree');
      
          $class = '';
          $class .= ($canAdmin ? ' canAdmin' : '');
          $class .= ($node->is_locked ? ' locked' : '');
          $class .= (!$node->is_active ? ' notlive' : '');
          $class .= ($node->getNode()->isRoot() ? ' root' : '');
          $class .= ($node->is_deleted ? ' deleted' : '');
          
          return "<li class='$class' id='sitetree_{$node->id}'>";
        }
      
        // render the tree
        include_partial(
          'sitetree/tree',
          array(
            'id' => 'sitetree_manager_sitetree',
            'class' => 'folderTree',
            'records' => $treeNodes,
            'nodeRenderFunction' => 'sitetree_manager_node',
            'liRenderFunction' => 'sitetree_manager_li',
            'sf_cache_key' => 'sitetree',
            'canBeDeleted' => (!$sf_user->isSuperAdmin())
          )
        );
      } ?>
    </div>
  </div>
</div>